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
        $user = Auth::user()->load('businessStaff');
        $query = Receipt::with(['seller', 'customer'])->latest();

        if ($user->hasRole('Admin') || $user->hasRole('Police')) {
            // See all receipts — no restriction
        } elseif ($user->hasRole('Business Owner')) {
            // Get all user-owned businesses
            $businessIds = Business::where('owner_id', $user->id)->pluck('id');

            // Get all receipts where seller is from user's businesses OR customer is the user
            $query->where(function ($q) use ($businessIds, $user) {
                $q->whereHas('products.product', function ($q2) use ($businessIds) {
                    $q2->whereIn('business_id', $businessIds);
                })->orWhere('customer_id', $user->id);
            });
        } elseif ($user->hasRole('Business Staff')) {
            $branchId = $user->businessStaff->branch_id ?? null;

            $query->where(function ($q) use ($user, $branchId) {
                $q->where(function ($sub) use ($user, $branchId) {
                    $sub->where('seller_id', $user->id)
                        ->whereHas('products.product', fn($p) => $p->where('branch_id', $branchId));
                })->orWhere('customer_id', $user->id);
            });
        } else {
            // Every other role: only show receipts where user is the customer
            $query->where('customer_id', $user->id);
        }

        return DataTables::eloquent($query)
            ->addColumn('reference', function ($r) {
                return '<a href="' . route('receipts.show', $r->uuid) . '" class="text-blue-600 hover:underline">'
                    . $r->reference_number . '</a>';
            })
            ->addColumn('seller',    fn($r) => optional($r->seller)->name)
            ->addColumn('customer',  fn($r) => optional($r->customer)->name)
            ->addColumn('date',      fn($r) => $r->created_at->format('jS F, Y g:ia'))
            ->filterColumn('reference', fn($q, $k) => $q->where('reference_number', 'like', "%{$k}%"))
            ->filterColumn('seller',    fn($q, $k) => $q->whereHas('seller', fn($q2) => $q2->where('name', 'like', "%{$k}%")))
            ->filterColumn('customer',  fn($q, $k) => $q->whereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$k}%")))
            ->filterColumn('date',      fn($q, $k) => $q->whereDate('created_at', $k))
            ->rawColumns(['reference'])
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
        $user = Auth::user()->load('businessStaff');

        // Scope businesses by role:
        if ($user->hasRole('Business Owner')) {
            $businesses = Business::where('owner_id', $user->id)->get();
        } elseif ($user->hasRole('Business Staff')) {
            // only their assigned business
            $businesses = Business::where('id', $user->businessStaff->business_id)->get();
        } else {
            // Admin, Police, etc
            $businesses = Business::all();
        }

        // Eager-load reports/sold flags onto products
        $products = Product::select(['id', 'name', 'unique_identifier', 'business_id', 'branch_id'])
            ->withCount([
                'reportedProduct as reported_status' => fn($q) => $q->where('status', true),
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

        $fee = Pricing::where('slug', 'receipt-generation')
            ->where('is_active', true)
            ->firstOrFail()
            ->amount;

        return view('receipts.create', compact(
            'businesses',
            'products',
            'user',
            'allBranches',
            'fee'
        ));
    }


    public function store(Request $request)
    {
        Log::debug('ReceiptController@store payload', $request->all());

        try {
            // 1) Lookup or create customer
            $existing = User::where('nin', $request->customer_nin)->first();

            if ($existing) {
                $request->validate([
                    'customer_nin'      => ['required', 'digits:11'],
                    'customer_name'     => 'required|string',
                    'customer_email'    => ['required', 'email'],
                    'customer_phone'    => ['required', 'digits:11'],
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

                // create the new customer
                $plain = Str::random(12);
                $roleId = Role::findByName('Customer')->id;

                $customer = User::create([
                    'nin'          => $request->customer_nin,
                    'name'         => $request->customer_name,
                    'email'        => $request->customer_email,
                    'phone_number' => $request->customer_phone,
                    'address'      => $request->customer_address,
                    'state_id'     => $request->customer_state_id,
                    'lga_id'       => $request->customer_lga_id,
                    'password'     => bcrypt($plain),
                    'role_id'      => $roleId,
                ]);
                $customer->assignRole('Customer');
                Mail::to($customer->email)
                    ->send(new NewUserCredentials($customer, $plain));
            }

            // 2) Calculate wallet balance
            $fee    = Pricing::where('slug', 'receipt-generation')
                ->where('is_active', true)
                ->firstOrFail()
                ->amount;
            $seller = Auth::user();

            $totalCredit = $seller->wallets()
                ->where('status', 'success')
                ->where('type', 'credit')
                ->sum('amount');
            $totalDebit  = $seller->wallets()
                ->where('status', 'success')
                ->where('type', 'debit')
                ->sum('amount');

            $balance = $totalCredit - $totalDebit;

            if ($balance < $fee) {
                $error = 'Insufficient balance (need ₦' . number_format($fee, 2) . ')';
                return $request->ajax() || $request->expectsJson()
                    ? response()->json(['error' => $error], 402)
                    : back()->withErrors(['wallet' => $error]);
            }

            // Deduct fee
            Wallet::create([
                'user_id' => $seller->id,
                'amount'  => $fee,
                'type'    => 'debit',
                'status'  => 'success',
                'description' => 'Receipt Generation',
            ]);

            Log::info("Deducted ₦{$fee} from user {$seller->id} for receipt generation");

            // 3) Create receipt and attach products
            $receipt = Receipt::create([
                'customer_id' => $customer->id,
                'seller_id'   => $seller->id,
            ]);

            foreach ($request->batches as $batch) {
                foreach ($batch['products'] as $pid) {
                    ReceiptProduct::create([
                        'receipt_id' => $receipt->id,
                        'product_id' => $pid,
                        'quantity'   => 1,
                    ]);
                    AuditService::log(
                        'product_sold',
                        "{$seller->name} sold product #{$pid} to {$customer->name}"
                    );
                }
            }

            // 4) Return JSON for AJAX or redirect for normal
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success'          => true,
                    'reference_number' => $receipt->reference_number,
                ], 201);
            }

            return redirect()
                ->route('receipts.index')
                ->with('success', 'Receipt generated: ' . $receipt->reference_number);
        } catch (\Throwable $e) {
            Log::error("ReceiptController@store failed: {$e->getMessage()}", [
                'exception' => (string)$e,
            ]);

            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
