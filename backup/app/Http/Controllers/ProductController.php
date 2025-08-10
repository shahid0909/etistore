<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    /**
     * Display the listing of Products.
     */
    public function index()
    {
        $categories = Category::all();
        $subcategories = Subcategory::all();

        return view('backend.pages.products.index', compact('categories', 'subcategories'));
    }

    /**
     * get sub categories depends on categories.
     */
    public function getSubcategories(Request $request)
    {
        $subcategories = Subcategory::where('category_id', $request->category_id)->get();

        return response()->json($subcategories);
    }

    /**
     * Store or update Product.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:sub_categories,id',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'reorder_level' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Check if the request is for updating or creating a new product
            $product = $request->product_id
                ? Product::findOrFail($request->product_id)
                : new Product();

            // Save product information
            $product->name = $request->name;
            $product->category_id = $request->category_id;
            $product->subcategory_id = $request->subcategory_id;
            $product->is_returnable = filter_var($request->is_returnable, FILTER_VALIDATE_BOOLEAN);
            $product->unit = $request->unit;
            $product->description = $request->description;
            $product->save();

            // Handle Inventory (set current_stock to 0 only if not already set)
            $inventory = $product->inventory ?: new Inventory(); // If inventory does not exist, create it
            $inventory->product_id = $product->id;
            $inventory->reorder_level = $request->reorder_level;

            // Only set current_stock to 0 if it's not already set
            if ($inventory->current_stock === null) {
                $inventory->current_stock = 0; // Set to 0 if no current stock is set
            }

            $inventory->save();

            DB::commit();

            return response()->json([
                'success' => $request->product_id
                    ? 'Product updated successfully along with inventory!'
                    : 'Product created successfully along with inventory!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Operation failed: ' . $e->getMessage()], 500);
        }
    }



    /**
     * Get Product data for DataTables.
     */
    public function datatable()
    {
        try {
            // Fetch products with inventory relationship
            $data = Product::with('category', 'subcategory', 'inventory')->orderBy('id', 'desc')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('is_returnable', function ($item) {
                    return $item->is_returnable ? 'Yes' : 'No';
                })
                ->addColumn('category', function ($item) {
                    return $item->category->name ?? 'N/A';
                })
                ->addColumn('subcategory', function ($item) {
                    return $item->subcategory->name ?? 'N/A';
                })
                ->addColumn('current_stock', function ($item) {
                    return $item->inventory ? $item->inventory->current_stock : 'N/A';
                })
                ->addColumn('reorder_level', function ($item) {
                    return $item->inventory ? $item->inventory->reorder_level : 'N/A';
                })
                ->addColumn('action', function ($item) {
                    return '
                        <button class="btn btn-primary btn-sm edit-btn" data-id="' . $item->id . '"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></button>
                        <button class="btn btn-danger btn-sm delete-btn" data-id="' . $item->id . '"><i class="fa fa-trash" aria-hidden="true"></i></button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Edit Product.
     */
    public function edit($id)
    {
        $product = Product::with('inventory')->findOrFail($id); // Ensure inventory is loaded
        return response()->json($product);
    }

    /**
     * Delete Product.
     */
    public function destroy($id)
    {
        try {
            Product::findOrFail($id)->delete();
            return response()->json(['success' => 'Product deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
