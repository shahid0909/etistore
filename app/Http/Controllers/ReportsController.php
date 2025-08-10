<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Issuances;
use App\Models\InventoryTransactions;
use App\Models\Department;
use App\Models\Staff;
use Yajra\DataTables\Facades\DataTables;

class ReportsController extends Controller
{
    public function index()
    {
        return view('backend.pages.reports.index');
    }

    public function fetchReportData(Request $request)
    {
        $query = Issuances::with(['product', 'staff', 'department'])
            ->when($request->product_id, function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            })
            ->when($request->department_id, function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            })
            ->when($request->staff_id, function ($q) use ($request) {
                $q->where('staff_id', $request->staff_id);
            })
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                $q->whereBetween('created_at', [$request->start_date, $request->end_date]);
            })
            ->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('product_name', fn($item) => $item->product->name ?? 'N/A')
            ->addColumn('department_name', fn($item) => $item->department->name ?? 'N/A')
            ->addColumn('staff_name', fn($item) => $item->staff->name ?? 'N/A')
            ->addColumn('quantity', fn($item) => $item->quantity)
            ->addColumn('date', fn($item) => $item->created_at->format('Y-m-d'))
            ->make(true);
    }

    public function getDropdownData()
    {
        return response()->json([
            'products' => Product::select('id', 'name')->get(),
            'departments' => Department::select('id', 'name')->get(),
            'staff' => Staff::select('id', 'name')->get(),
        ]);
    }
}
