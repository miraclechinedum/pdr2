<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Business;
use App\Models\ReportedProduct;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

use App\Services\AuditService;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index()
    {
        // Table is AJAX-driven
        return view('products.index');
    }

    public function data()
    {
        $user  = Auth::user();
        $roles = $user->getRoleNames()->toArray();
        Log::debug('ProductController@data – current user roles: ' . implode(',', $roles));

        // Start with a fresh Eloquent\Builder
        $query = Product::with(['business', 'branch', 'category', 'user', 'reportedProduct'])
            ->withCount('receiptProducts');

        // 1) Admin & Police see everything
        if ($user->hasRole('Admin') || $user->hasRole('Police')) {
            // no additional where()
        }
        // 2) Business Owner sees only their businesses’ products
        elseif ($user->hasRole('Business Owner')) {
            $businessIds = Business::where('owner_id', $user->id)->pluck('id');
            $query->whereIn('business_id', $businessIds);
        }
        // 3) Business Staff sees only their branch
        elseif ($user->hasRole('Business Staff')) {
            $branchId = optional($user->businessStaff)->branch_id;
            $query->where('branch_id', $branchId);
        }
        // 4) Everyone else sees nothing
        else {
            $query->whereRaw('0 = 1');
        }

        return DataTables::eloquent($query)
            ->addColumn('business', fn($p) => $p->business->business_name)
            ->addColumn('branch',   fn($p) => $p->branch?->branch_name ?? '-')
            ->addColumn('category', fn($p) => $p->category->name)
            ->addColumn('owner',    fn($p) => $p->user->name)
            ->addColumn('reported_status', fn($p) => $p->reportedProduct ? true : false)
            ->addColumn('is_sold',          fn($p) => $p->receipt_products_count > 0)
            ->addColumn('actions', function ($p) {
                return '<a href="' . route('products.edit', $p->uuid)
                    . '" class="px-2 py-1 bg-yellow-500 text-white rounded">Edit</a>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }


    public function create()
    {
        $user = auth()->user();
        $categories = ProductCategory::all();

        // Default empty collections
        $businesses = collect();

        if ($user->hasRole('Business Owner')) {
            // Only businesses owned by this user
            $businesses = Business::where('owner_id', $user->id)->with('branches')->get();
        } elseif ($user->hasRole('Business Staff')) {
            // Only the business and branch assigned to this user
            $branch = optional($user->businessStaff)->branch;
            if ($branch) {
                $business = $branch->business;
                $business->setRelation('branches', collect([$branch])); // limit to only their branch
                $businesses = collect([$business]);
            }
        }

        return view('products.create', compact('businesses', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => 'required|exists:product_categories,id',
            'unique_identifier' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'unique_identifier'),
            ],
            'business_id'       => 'required|exists:businesses,id',
            'branch_id'         => 'required|exists:business_branches,id',
        ]);

        $product = Product::create([
            'name'              => $data['name'],
            'category_id'       => $data['category_id'],
            'unique_identifier' => $data['unique_identifier'],
            'business_id'       => $data['business_id'],
            'branch_id'         => $data['branch_id'] ?? null,
            'user_id'           => Auth::id(),
        ]);

        $businessName = $product->business->business_name;
        $branchName = $product->branch->branch_name;

        AuditService::log(
            'product_added',
            Auth::user()->name
                . ' (' . Auth::user()->phone_number . ') added '
                . $product->name
                . ' (' . $product->unique_identifier . ') to '
                . $businessName . ' - ' . $branchName
        );

        return redirect()
            ->route('products.index')
            ->with('success', 'Product added.');
    }

    public function edit(string $uuid)
    {
        $product    = Product::where('uuid', $uuid)->firstOrFail();
        $businesses = Business::all();
        $categories = ProductCategory::all();
        return view('products.edit', compact('product', 'businesses', 'categories'));
    }

    public function update(Request $request, string $uuid)
    {
        $product = Product::where('uuid', $uuid)->firstOrFail();

        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => 'required|exists:product_categories,id',
            'unique_identifier' => 'required|string|max:255',
            'business_id'       => 'required|exists:businesses,id',
            'branch_id'         => 'nullable|exists:business_branches,id',
        ]);

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', 'Product updated.');
    }

    /**
     * Return a single product as JSON for the modals.
     */
    public function show(string $uuid)
    {
        $product = Product::with('branch')
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json([
            'uuid'              => $product->uuid,
            'id'                => $product->id,
            'name'              => $product->name,
            'unique_identifier' => $product->unique_identifier,
            'business_id'       => $product->business_id,
            'branch_id'         => $product->branch_id,
            'branch_name'       => optional($product->branch)->branch_name,
        ]);
    }

    /**
     * Handle product reporting.
     */
    public function report(Request $request, Product $product)
    {
        // 1) Prevent double‐reporting
        $already = ReportedProduct::where('product_id', $product->id)
            ->where('status', true)
            ->exists();

        if ($already) {
            return response()->json([
                'errors' => ['description' => ['That product has already been reported.']]
            ], 422);
        }

        // 2) Validate & create
        $data = $request->validate([
            'description' => 'required|string',
        ]);

        $rp = ReportedProduct::create([
            'product_id'  => $product->id,
            'user_id'     => Auth::id(),
            'description' => $data['description'],
            'status'      => true,
        ]);

        // 3) Audit
        AuditService::log(
            'product_reported',
            "{$product->name} ({$product->unique_identifier}) was reported by "
                . Auth::user()->name . " (" . Auth::user()->phone_number . "): "
                . $data['description']
        );

        return response()->json(['message' => 'Reported successfully'], 201);
    }

    /**
     * Resolve a product report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product       $product
     */
    public function resolve(Request $request, Product $product)
    {
        // 1) Find the active report for this product
        $report = ReportedProduct::where('product_id', $product->id)
            ->where('status', true)
            ->first();

        if (! $report) {
            return response()->json([
                'error' => 'No active report found for that product.'
            ], 404);
        }

        // 2) Mark it resolved
        $report->update(['status' => false]);

        // 3) Audit
        AuditService::log(
            'report_resolved',
            "Report for {$product->name} ({$product->unique_identifier}) resolved by "
                . Auth::user()->name
        );

        return response()->json(['message' => 'Resolved successfully'], 200);
    }

    /**
     * Handle product transfer.
     */
    public function transfer(Request $request, Product $product)
    {
        $data = $request->validate([
            'new_branch_id' => 'required|exists:business_branches,id',
        ]);

        // Manually assign & save:
        $product->branch_id = $data['new_branch_id'];
        $product->save();

        return response()->json(['message' => 'Transferred successfully']);
    }

    public function uploadForm()
    {
        $businesses = Business::where('owner_id', auth()->id())->get();
        $categories = ProductCategory::all(); // You may later scope this if needed

        return view('products.upload', compact('businesses', 'categories'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file'        => 'required|mimes:csv,xlsx|max:20480',
            'business_id' => 'required|exists:businesses,id',
            'branch_id'   => 'required|exists:business_branches,id',
            'category_id' => 'required|exists:product_categories,id',
        ]);

        // confirm the user actually owns that business & branch
        $business = Business::where('id', $request->business_id)
            ->where('owner_id', auth()->id())
            ->firstOrFail();
        $branch = $business->branches()->where('id', $request->branch_id)->firstOrFail();

        $import = new ProductsImport(
            $request->category_id,
            $business->id,
            $branch->id,
            auth()->id()
        );

        // synchronous import
        Excel::import($import, $request->file('file'));

        return redirect()->back()->with('success', 'Products imported successfully.');
    }
}
