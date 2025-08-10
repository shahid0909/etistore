<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Inventory;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $subcategories = SubCategory::all();
        $units = Unit::all();
        $products = Product::with('category', 'SubCategory', 'unit', 'inventory')->where('is_returnable', 0)->get();

        return view('backend.pages.products.index', compact('categories', 'subcategories', 'units', 'products'));
    }

    /**
     * get sub categories depends on categories.
     */
    public function getSubcategories(Request $request)
    {
        $subcategories = SubCategory::where('category_id', $request->category_id)->get();

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
            'SubCategory_id' => 'nullable|exists:sub_categories,id',
            'unit_id' => 'required|exists:units,id',
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
            $product->subCategory_id = $request->subcategory_id;
            $product->unit_id = $request->unit_id;
            $product->is_returnable = 0; // Default to false
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
            $data = Product::with('category', 'subcategory', 'unit', 'inventory')->where('is_returnable', 0)->orderBy('id', 'desc')->get();

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
                ->addColumn('unit', function ($item) {
                    return $item->unit->name ?? 'N/A';
                })
                ->addColumn('current_stock', function ($item) {
                    return $item->inventory ? $item->inventory->current_stock : 'N/A';
                })
                ->addColumn('reorder_level', function ($item) {
                    return $item->inventory ? $item->inventory->reorder_level : 'N/A';
                })
                ->addColumn('action', function ($item) {
                    $usr = Auth::guard('admin')->user();
                    $buttons = '';

                    if ($usr->can('product.edit')) {
                        $buttons .= '<button class="btn btn-primary btn-sm edit-btn" data-id="' . $item->id . '">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                     </button> ';
                    }

                    if ($usr->can('product.delete')) {
                        $buttons .= '<button class="btn btn-danger btn-sm delete-btn" data-id="' . $item->id . '">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                     </button>';
                    }

                    return $buttons;
                })

                ->rawColumns(['action'])
                ->addIndexColumn()
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
