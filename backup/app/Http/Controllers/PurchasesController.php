<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Purchases;
use Illuminate\Http\Request;
use App\Models\InventoryTransactions;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;


class PurchasesController extends Controller
{
    /**
     * Display the purchase list.
     */
    public function index()
    {
        $purchases = Purchases::with('product')->get();
        return view('backend.pages.purchases.index', compact('purchases'));
    }

    public function getProducts()
    {
        $products = Product::all(['id', 'name']);
        return response()->json($products);
    }

    /**
     * Fetch purchase data for DataTable.
     */
    public function datatable()
    {
        $data = Purchases::with('product')->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('product_name', fn($item) => $item->product->name ?? 'N/A')
            ->addColumn('chalan_no', fn($item) => $item->chalan_no)  // Alias chalan-no to chalan_no
            ->addColumn('quantity', fn($item) => $item->quantity)
            ->addColumn('purchase_date', fn($item) => $item->purchase_date ? date('Y-m-d', strtotime($item->purchase_date)) : 'N/A')
            ->addColumn('action', function ($item) {
                return '
                    <button class="btn btn-warning btn-sm edit-btn" data-id="' . $item->id . '">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm delete-btn" data-id="' . $item->id . '">
                        <i class="fa fa-trash"></i>
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
    }
    /**
     * Store a new purchase.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'purchase_date' => 'required|date',
            'chalan_no' => 'required|string', // Added chalan_no validation if not already
        ]);

        // Create a new purchase
        $purchase = Purchases::create($request->all());

        // Log the transaction
        InventoryTransactions::create([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'transaction_type' => 'purchase',
            // Optionally, add the user who created the transaction
            // 'user_id' => auth()->id(), // Assuming you're using Laravel's authentication
        ]);

        // Update inventory - Ensure inventory exists for product
        $product = Product::find($request->product_id);

        // Get or create inventory record for product
        $inventory = $product->inventory()->first();
        if (!$inventory) {
            $inventory = Inventory::create([
                'product_id' => $product->id,
                'current_stock' => 0,
            ]);
        }

        // Update stock (add quantity to current stock)
        $inventory->current_stock += $request->quantity;
        $inventory->save();

        return response()->json(['success' => 'Purchase added successfully!']);
    }

    /**
     * Update a purchase.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'purchase_date' => 'required|date',
            'chalan_no' => 'required|string', // Added chalan_no validation
        ]);

        $purchase = Purchases::findOrFail($id);
        $originalQuantity = $purchase->quantity;

        // Update purchase
        $purchase->update($request->all());

        // Adjust stock
        $product = Product::find($request->product_id);

        // Get the inventory record
        $inventory = $product->inventory()->first();
        if (!$inventory) {
            // If inventory does not exist, create it
            $inventory = Inventory::create([
                'product_id' => $product->id,
                'current_stock' => 0,
            ]);
        }

        // Calculate stock change
        $stockChange = $request->quantity - $originalQuantity;
        $inventory->current_stock += $stockChange;
        $inventory->save();

        // Update transaction log
        $transaction = InventoryTransactions::where('product_id', $request->product_id)
            ->where('transaction_type', 'purchase')
            ->first();
        $transaction->update([
            'quantity' => $request->quantity,
            'chalan_no' => $request->chalan_no,
            // 'user_id' => auth()->id(), // Optional user tracking
        ]);

        return response()->json(['success' => 'Purchase updated successfully!']);
    }


    public function edit($id)
    {
        $purchase = Purchases::findOrFail($id); // Ensure inventory is loaded
        return response()->json($purchase);
    }

    /**
     * Delete a purchase.
     */
    public function destroy($id)
    {
        $purchase = Purchases::findOrFail($id);

        $product = Product::find($purchase->product_id);

        if ($product->current_stock < $purchase->quantity) {
            return response()->json([
                'error' => 'Cannot delete purchase. Current stock is less than the purchase quantity.',
            ], 400);
        }

        // Update stock
        $product->updateStock($purchase->quantity, 'subtract');

        // Remove transaction log
        InventoryTransactions::where('product_id', $purchase->product_id)
            ->where('transaction_type', 'purchase')
            ->delete();

        $purchase->delete();

        return response()->json(['success' => 'Purchase deleted successfully!']);
    }
}
