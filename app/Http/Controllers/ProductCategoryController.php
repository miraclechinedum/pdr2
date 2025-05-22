<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ProductCategoryController extends Controller
{
    /**
     * Show the categories page.
     */
    public function index()
    {
        return view('products.categories');
    }

    /**
     * Return JSON for DataTables.
     */
    public function data()
    {
        return DataTables::of(
            ProductCategory::with('creator')  // eager-load creator relationship
        )
            ->addColumn('creator', fn($cat) => $cat->creator->name)
            ->addColumn('actions', function ($cat) {
                // note: data-id on the Edit button matches our JS handler
                return '<button class="edit-btn px-3 py-1 bg-yellow-500 text-white rounded" '
                    . 'data-id="' . e($cat->id) . '">Edit</button>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Persist a new category.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'identifier_label' => 'required|string|max:100',
        ]);

        ProductCategory::create([
            'name'             => $data['name'],
            'label'            => Str::slug($data['name']),  // auto-generate label
            'identifier_label' => $data['identifier_label'],
            'user_id'          => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Category created successfully.',
        ], 201);
    }

    /**
     * Return a single category as JSON.
     */
    public function show(ProductCategory $category)
    {
        return response()->json($category);
    }

    /**
     * Update an existing category.
     */
    public function update(Request $request, ProductCategory $category)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'identifier_label' => 'required|string|max:100',
        ]);

        $category->update([
            'name'             => $data['name'],
            '// label'         => Str::slug($data['name']),  // if you want label to follow name
            'identifier_label' => $data['identifier_label'],
        ]);

        return response()->json([
            'message' => 'Category updated successfully.',
        ]);
    }
}
