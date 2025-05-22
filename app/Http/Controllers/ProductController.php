<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Business;
use App\Models\ReportedProduct;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

use App\Services\AuditService;

class ProductController extends Controller
{
    public function index()
    {
        // Table is AJAX-driven
        return view('products.index');
    }

    public function data()
    {
        return DataTables::of(
            Product::with(['business', 'branch', 'category', 'user', 'reportedProduct'])
        )
            ->addColumn('business', fn($p) => $p->business->business_name)
            ->addColumn('branch',   fn($p) => $p->branch?->branch_name ?? '-')
            ->addColumn('category', fn($p) => $p->category->name)
            ->addColumn('owner',    fn($p) => $p->user->name)
            // here’s the new flag:
            ->addColumn('reported_status', fn($p) => $p->reportedProduct ? true : false)
            ->addColumn(
                'actions',
                fn($p) => '<a href="' . route('products.edit', $p->uuid)
                    . '" class="px-2 py-1 bg-yellow-500 text-white rounded">Edit</a>'
            )
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function create()
    {
        $businesses = Business::all();
        $categories = ProductCategory::all();
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
    public function show(Product $product)
    {
        // eager‐load the branch relation
        $product->load('branch');

        return response()->json([
            'id'                => $product->id,
            'name'              => $product->name,
            'unique_identifier' => $product->unique_identifier,
            'business_id'       => $product->business_id,
            'branch_id'         => $product->branch_id,
            // if there's a branch, return its address; otherwise null
            'branch_name'       => $product->branch
                ? $product->branch->branch_name
                : null,
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

        // assume Product has branch_id on it:
        $product->update([
            'branch_id' => $data['new_branch_id'],
        ]);

        return response()->json(['message' => 'Transferred successfully']);
    }
}
