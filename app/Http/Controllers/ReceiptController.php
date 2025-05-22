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

class ReceiptController extends Controller
{
    public function index()
    {
        return view('receipts.index');
    }

    public function data(Request $request)
    {
        try {
            Log::debug('ReceiptController@data: start');

            $query = Receipt::with(['seller', 'customer']);

            return DataTables::of($query)
                // S/N column is handled in JS drawCallback
                ->addColumn('uuid', fn($r) => $r->uuid)
                ->addColumn('reference', fn($r) => $r->reference_number)
                ->addColumn('seller', fn($r) => $r->seller->name)
                ->addColumn('customer', fn($r) => $r->customer->name . ' | ' . $r->customer->nin)
                // New date format: e.g. "12th May, 2023 8:56pm"
                ->addColumn('date', fn($r) => $r->created_at->format('jS F, Y g:ia'))
                ->addColumn('actions', function ($r) {
                    return '<a href="' . route('receipts.show', $r->uuid)
                        . '" class="btn-view text-blue-600">View</a>';
                })

                // Enable searching on the reference_number column
                ->filterColumn('reference', function ($query, $keyword) {
                    $query->where('reference_number', 'like', "%{$keyword}%");
                })

                // Enable searching on seller name
                ->filterColumn('seller', function ($query, $keyword) {
                    $query->whereHas(
                        'seller',
                        fn($q) =>
                        $q->where('name', 'like', "%{$keyword}%")
                    );
                })

                // Enable searching on customer name or NIN
                ->filterColumn('customer', function ($query, $keyword) {
                    $query->whereHas(
                        'customer',
                        fn($q) =>
                        $q->where('name', 'like', "%{$keyword}%")
                            ->orWhere('nin', 'like', "%{$keyword}%")
                    );
                })

                ->rawColumns(['actions'])
                ->make(true);
        } catch (\Throwable $e) {
            Log::error('ReceiptController@data ERROR: ' . $e->getMessage());
            return response()->json([
                'error' => 'Server error, please check logs.'
            ], 500);
        }
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
