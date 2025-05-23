<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Business;
use App\Models\BusinessBranch;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\ReceiptProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

use Spatie\Permission\Models\Role;
use App\Mail\NewUserCredentials;
use Illuminate\Support\Facades\Mail;

use App\Services\AuditService;
use App\Models\Pricing;
use App\Models\Wallet;
use Yabacon\Paystack;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ReceiptController extends Controller
{
    public function index()
    {
        return view('receipts.index');
    }

    public function data()
    {
        $user = Auth::user();
        $query = Receipt::with(['seller', 'customer']);

        if ($user->hasRole('Admin')) {
            // do nothing, show all
        } elseif ($user->hasRole('Business')) {
            $query->where('seller_id', $user->id);
        } elseif ($user->hasRole('Business Staff')) {
            $query->where('seller_id', $user->business_id);
        }

        return DataTables::eloquent($query)
            ->addColumn('reference', fn($r) => $r->reference_number)
            ->addColumn('seller', fn($r) => optional($r->seller)->name ?? 'N/A')
            ->addColumn('customer', fn($r) => optional($r->customer)->name ?? 'N/A')
            ->addColumn('date', fn($r) => $r->created_at->format('Y-m-d'))
            ->addColumn('actions', fn($r) => '...') // Your action buttons
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function show(Request $req, Receipt $receipt)
    {
        $receipt->load(['seller', 'customer', 'products.product.business', 'products.product.branch']);

        if ($req->ajax()) {
            // return minimal JSON:
            return response()->json([
                'products' => $receipt->products->map(fn($rp) => [
                    'name'               => $rp->product->name,
                    'unique_identifier'  => $rp->product->unique_identifier,
                    'business_name'      => $rp->product->business->business_name,
                    'branch_name'        => $rp->product->branch?->branch_name,
                ])
            ]);
        }

        return view('receipts.show', compact('receipt'));
    }

    public function downloadPdf(Receipt $receipt)
    {
        $receipt->load(['seller', 'customer', 'products.product.business', 'products.product.branch']);
        $pdf = Pdf::loadView('receipts.pdf', compact('receipt'));
        return $pdf->download('receipt_' . $receipt->reference_number . '.pdf');
    }


    public function create()
    {
        $user = Auth::user()->load('businessStaff'); // now businessStaff is loaded
        $businesses = Business::all();
        // Eager-load whether there’s an active report, and whether it's ever been sold
        $products = Product::select(['id', 'name', 'unique_identifier', 'business_id', 'branch_id'])
            ->withCount([
                // count only active reports
                'reportedProduct as reported_status' => function ($q) {
                    $q->where('status', true);
                },
                // count how many times it appears in any receipt
                'receiptProducts as sold_count'
            ])
            ->get()
            ->map(fn($p) => [
                'id'                => $p->id,
                'name'              => $p->name,
                'unique_identifier' => $p->unique_identifier,
                'business_id'       => $p->business_id,
                'branch_id'         => $p->branch_id,
                'reported'          => $p->reported_status > 0,
                'sold'              => $p->sold_count > 0,
            ]);
        $allBranches = BusinessBranch::select(['id', 'branch_name'])->get();
        return view('receipts.create', compact('businesses', 'products', 'user', 'allBranches'));
    }

    public function store(Request $request)
    {
        Log::debug('ReceiptController@store payload', $request->all());

        // 1) See if this NIN already exists
        $existing = User::where('nin', $request->customer_nin)->first();

        if ($existing) {
            // 2a) If user exists, only validate the non-unique bits:
            $request->validate([
                'customer_nin'      => ['required', 'digits:11'],
                'customer_name'     => 'required|string',
                'customer_email'    => ['required', 'email'],       // no unique rule
                'customer_phone'    => ['required', 'digits:11'],   // no unique rule
                'customer_address'  => 'required|string',
                'customer_state_id' => 'required|exists:states,id',
                'customer_lga_id'   => 'required|exists:lgas,id',
                'batches'           => 'required|array|min:1',
                'batches.*.business_id' => 'required|exists:businesses,id',
                'batches.*.branch_id'   => 'nullable|exists:business_branches,id',
                'batches.*.products'    => 'required|array|min:1',
                'batches.*.products.*'  => 'exists:products,id',
            ]);

            $customer = $existing;
        } else {
            // 2b) Otherwise it's a brand-new user: enforce uniqueness, then create
            $request->validate([
                'customer_nin'      => ['required', 'digits:11', Rule::unique('users', 'nin')],
                'customer_name'     => 'required|string',
                'customer_email'    => ['required', 'email', Rule::unique('users', 'email')],
                'customer_phone'    => ['required', 'digits:11', Rule::unique('users', 'phone_number')],
                'customer_address'  => 'required|string',
                'customer_state_id' => 'required|exists:states,id',
                'customer_lga_id'   => 'required|exists:lgas,id',
                'batches'           => 'required|array|min:1',
                'batches.*.business_id' => 'required|exists:businesses,id',
                'batches.*.branch_id'   => 'nullable|exists:business_branches,id',
                'batches.*.products'    => 'required|array|min:1',
                'batches.*.products.*'  => 'exists:products,id',
            ]);

            // create the user
            $customerRoleId = Role::findByName('Customer')->id;
            // 1) Generate a random password
            $plain = Str::random(12);
            $customer = User::create([
                'nin'          => $request->customer_nin,
                'name'         => $request->customer_name,
                'email'        => $request->customer_email,
                'phone_number' => $request->customer_phone,
                'address'      => $request->customer_address,
                'state_id'     => $request->customer_state_id,
                'lga_id'       => $request->customer_lga_id,
                'password'     => bcrypt($plain),
                'role_id'      => $customerRoleId,
            ]);
            $customer->assignRole('Customer');

            // 4) Send the e-mail (you may want to queue this in production)
            Mail::to($customer->email)
                ->send(new NewUserCredentials($customer, $plain));
        }

        $pricing = Pricing::where('slug', 'receipt-generation')
            ->where('is_active', true)
            ->firstOrFail();

        $fee = $pricing->amount; // 900.00 for example

        // 2) Check seller’s wallet balance
        $seller = Auth::user();
        $totalCredit = $seller->wallets()
            ->where('type', 'credit')
            ->where('status', 'success')
            ->sum('amount');
        $totalDebit = $seller->wallets()
            ->where('type', 'debit')
            ->where('status', 'success')
            ->sum('amount');

        $balance = $totalCredit - $totalDebit;

        if ($balance < $fee) {
            // abort or return JSON error for insufficient funds
            return back()->withErrors([
                'wallet' => 'Insufficient wallet balance (need ₦' . number_format($fee, 2) . ')'
            ]);
        }

        // 3) Deduct the fee immediately
        Wallet::create([
            'user_id'   => $seller->id,
            'amount'    => $fee,
            'type'      => 'debit',
            'status'    => 'success',
        ]);

        Log::info("Deducted ₦{$fee} from user {$seller->id} for receipt generation");

        // 3) Now create the receipt
        $receipt = Receipt::create([
            'customer_id' => $customer->id,
            'seller_id'   => Auth::id(),
        ]);

        // 4) Attach products & audit‐log each sale
        foreach ($request->batches as $batch) {
            foreach ($batch['products'] as $prodId) {
                ReceiptProduct::create([
                    'receipt_id' => $receipt->id,
                    'product_id' => $prodId,
                    'quantity'   => 1,
                ]);
                $p = Product::findOrFail($prodId);
                AuditService::log(
                    'product_sold',
                    Auth::user()->name
                        . ' (' . Auth::user()->phone_number . ') sold '
                        . $p->name . ' (' . $p->unique_identifier . ') to '
                        . $customer->name . ' (' . $customer->phone_number . ')'
                );
            }
        }

        // 5) Return JSON or redirect
        if ($request->wantsJson()) {
            return response()->json([
                'success'          => true,
                'reference_number' => $receipt->reference_number,
            ], 201);
        }

        return redirect()
            ->route('receipts.index')
            ->with('success', 'Receipt generated: ' . $receipt->reference_number);
    }
}
