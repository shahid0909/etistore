<?php

namespace App\Http\Controllers;

use App\Models\Issuances;
use App\Models\Staff;
use App\Models\Department;
use App\Models\InventoryTransactions;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class IssuancesController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard('admin')->user();
            return $next($request);
        });
    }
    /**
     * Display issuance list view.
     */
    public function index()
    {
//        if (is_null($this->user) || !$this->user->can('admin.view')) {
//            abort(403, 'You are unauthorized to view this page.');
//        }
        return view('backend.pages.issuances.index');
    }

    /**
     * Fetch issuance data for DataTable.
     */
    public function datatable()
    {
        $data = Issuances::with(['staff', 'department', 'product', 'issuedBy'])->orderBy('id', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('staff_name', fn($item) => $item->staff->name ?? 'N/A')
            ->addColumn('department_name', fn($item) => $item->department->name ?? 'N/A')
            ->addColumn('product_name', fn($item) => $item->product->name ?? 'N/A')
            ->addColumn('quantity', fn($item) => $item->quantity)
            ->addColumn('issued_by_name', fn($item) => $item->issuedBy->name ?? 'N/A')
            ->addColumn('description', fn($item) => $item->description ?? 'N/A')

            ->addColumn('action', function ($item) {
                $usr = Auth::guard('admin')->user();
                $buttons = '';

                if ($usr->can('issue.edit')) {
                    $buttons .= '<button class="btn btn-primary btn-sm edit-btn" data-id="' . $item->id . '">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                     </button> ';
                }

                if ($usr->can('issue.delete')) {
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

    /**
     * Fetch products, departments, and staff for dropdowns.
     */
    public function getDropdownData()
    {
        $products = Product::all(['id', 'name']);
        $departments = Department::all(['id', 'name']);
        $staff = Staff::all(['id', 'name']);

        return response()->json([
            'products' => $products,
            'departments' => $departments,
            'staff' => $staff,
        ]);
    }

    /**
     * Check product Availability.
     */

    public function checkProductAvailability($productId)
    {
        $inventory = Inventory::where('product_id', $productId)->first();

        if (!$inventory) {
            return response()->json(['available_stock' => 0]);
        }

        return response()->json(['available_stock' => $inventory->current_stock]);
    }


    /**
     * Store a new issuance.
     */
    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'department_id' => 'required|exists:departments,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        // Fetch authenticated user's ID
        $issuedBy = auth('admin')->id();

        if (!$issuedBy) {
            return response()->json(['error' => 'Unauthorized: No user authenticated.'], 401);
        }

        // Fetch product and inventory
        $product = Product::find($request->product_id);
        $inventory = $product->inventory()->first();

        if (!$inventory || $inventory->current_stock < $request->quantity) {
            return response()->json([
                'error' => 'Insufficient stock for the selected product.',
            ], 400);
        }

        // Create Issuance record
        Issuances::create([
            'staff_id' => $request->staff_id,
            'department_id' => $request->department_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'issued_by' => $issuedBy,
            'description' => $request->description,
        ]);

        // Update inventory stock
        $inventory->current_stock -= $request->quantity;
        $inventory->save();

        // Log the transaction
        InventoryTransactions::create([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'type' => 'issuance',
        ]);

        return response()->json(['success' => 'Product issued successfully!']);
    }
    /**
     * Edit an issuance.
     */
    public function edit($id)
    {
        $issuance = Issuances::findOrFail($id);
        return response()->json($issuance);
    }

    /**
     * Update an issuance.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'staff_id' => 'required|exists:staff,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'issued_by' => 'required|exists:admins,id',
            'description' => 'nullable|string|max:255',
        ]);

        $issuance = Issuances::findOrFail($id);
        $issuance->update($request->all());

        return response()->json(['success' => 'Issuance updated successfully!']);
    }

    /**
     * Delete an issuance.
     */
    public function destroy($id)
    {
        $issuance = Issuances::findOrFail($id);
        $issuance->delete();

        return response()->json(['success' => 'Issuance deleted successfully!']);
    }
}
