<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\approvar;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Product;
use App\Models\Department;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function Carbon\Traits\ne;

class RequisitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requisitions = Requisition::with('staff','department','designation','items.product')->latest()->get();
        return view('backend.pages.requisitions.index', compact('requisitions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $products = Product::all();
        $departments = Department::all();
        $staff = Admin::all();
//        $staff = Staff::all();
        // $staff = Auth::user()->staff;
        return view('backend.pages.requisitions.create', compact('products','staff','departments'));
    }

    public function getStaffByDepartment($departmentId)
    {
//        $staff = Staff::where('department_id', $departmentId)->get();
        $staff = Admin::all();
        return response()->json($staff);
    }

    public function getStaffDetails($staffId)
    {
        $staff = Staff::with('designation')->findOrFail($staffId);
        return response()->json([
            'designation' => $staff->designation->name ?? '',
        ]);
    }

    public function getStock($productId)
    {
        $product = Product::with('inventory')->findOrFail($productId);
        return response()->json([
            'current_stock' => $product->inventory->current_stock ?? 0
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'products.*'   => 'required|exists:products,id',
            'quantities.*' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();

        try {
            $staff = Auth::guard('admin')->user();

            $requisition = new Requisition();
            $requisition->staff_id       = $staff->id;
            $requisition->department_id  = $staff->department_id ?? null;
            $requisition->designation_id = $staff->designation_id ?? null;
            $requisition->rationale      = $request->rationale;
//            $requisition->save();

            foreach ($request->products as $index => $productId) {
                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'product_id'     => $productId,
                    'requested_qty'  => $request->quantities[$index],
                ]);
            }
            $findapprover = approvar::orderby('seq','asc')->get();
                if ($findapprover)
                {
                    foreach ($findapprover as $val){
                        $input =  new approvar();
                        $input->requisition_id = $requisition->id;
                        $input->emp_id = $findapprover['emp_id'];
                        $input->level = $findapprover['seq'];
                        $input->status = 'pending';
                        $input->save();
                    }

                }


            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Requisition submitted successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong, please try again!',
                'error'   => $e->getMessage() // helpful for debugging
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Requisition $requisition)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Requisition $requisition)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Requisition $requisition)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Requisition $requisition)
    {
        //
    }
}
