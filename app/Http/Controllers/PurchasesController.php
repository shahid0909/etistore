<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Purchases;
use Illuminate\Http\Request;
use App\Models\InventoryTransactions;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;

class PurchasesController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('backend.pages.purchases.index', compact('categories'));
    }

    public function getCategories()
    {
        $categories = Category::all(['id', 'name']);
        return response()->json($categories);
    }

    public function getSubCategories($categoryId)
    {
        $subCategories = SubCategory::where('category_id', $categoryId)->get(['id', 'name']);
        return response()->json($subCategories);
    }

    public function getProducts($subCategoryId)
    {
        $products = Product::where('subcategory_id', $subCategoryId)->get(['id', 'name']);
        return response()->json($products);
    }

    public function datatable()
    {
        $data = Purchases::with('product')->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('product_name', fn($item) => $item->product->name ?? 'N/A')
            ->addColumn('chalan_no', fn($item) => $item->chalan_no)
            ->addColumn('quantity', fn($item) => $item->quantity)
            ->addColumn('purchase_date', fn($item) => $item->purchase_date ? date('Y-m-d', strtotime($item->purchase_date)) : 'N/A')
//            ->addColumn('action', function ($item) {
//                return '
//                    <button class="btn btn-danger btn-sm delete-btn" data-id="' . $item->id . '">
//                        <i class="fa fa-trash"></i>
//                    </button>
//                ';
//            //     <button class="btn btn-warning btn-sm edit-btn" data-id="' . $item->id . '">
//            //     <i class="fa fa-edit"></i>
//            // </button>
//            })
            ->addColumn('action', function ($item) {
                $usr = Auth::guard('admin')->user();
                $buttons = '';

                if ($usr->can('purchase.edit')) {
                    $buttons .= '<button class="btn btn-primary btn-sm edit-btn" data-id="' . $item->id . '">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                     </button> ';
                }

                if ($usr->can('purchase.delete')) {
                    $buttons .= '<button class="btn btn-danger btn-sm delete-btn" data-id="' . $item->id . '">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                     </button>';
                }

                return $buttons;
            })

            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
//            'purchase_date' => 'required|date',
//            'chalan_no' => 'required|string',
//            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
        ]);

        $filePath = $request->hasFile('file') ? $request->file('file')->store('uploads/purchases') : null;

        $purchase = Purchases::create(array_merge($request->all(), ['file_path' => $filePath]));

        InventoryTransactions::create([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'transaction_type' => 'purchase',
        ]);

        $product = Product::find($request->product_id);
        $product->updateStock($request->quantity, 'add');

        return response()->json(['success' => 'Purchase added successfully!']);
    }

    public function edit($id)
    {
        $purchase = Purchases::findOrFail($id); // Ensure inventory is loaded
        return response()->json($purchase);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
//            'purchase_date' => 'required|date',
//            'chalan_no' => 'required|string',
//            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
        ]);

        $purchase = Purchases::findOrFail($id);
        $originalQuantity = $purchase->quantity;

        $filePath = $purchase->file_path;
        if ($request->hasFile('file')) {
            if ($filePath) {
                Storage::delete($filePath);
            }
            $filePath = $request->file('file')->store('uploads/purchases');
        }

        $purchase->update(array_merge($request->all(), ['file_path' => $filePath]));

        $product = Product::find($request->product_id);
        $stockChange = $request->quantity - $originalQuantity;
        $product->updateStock($stockChange > 0 ? $stockChange : abs($stockChange), $stockChange > 0 ? 'add' : 'subtract');

        return response()->json(['success' => 'Purchase updated successfully!']);
    }

    public function destroy($id)
    {
        $purchase = Purchases::findOrFail($id);
        $product = Product::find($purchase->product_id);

//        if ($product->inventory->current_stock < $purchase->quantity) {
//            return response()->json([
//                'error' => 'Cannot delete purchase. Current stock is less than the purchase quantity.',
//            ], 400);
//        }

        $product->updateStock($purchase->quantity, 'subtract');

        if ($purchase->file_path) {
            Storage::delete($purchase->file_path);
        }

        InventoryTransactions::where('product_id', $purchase->product_id)
            ->where('type', 'purchase')
            ->delete();

        $purchase->delete();

        return response()->json(['success' => 'Purchase deleted successfully!']);
    }
}
