<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Purchases;
use Yajra\DataTables\Facades\DataTables;

class PurchaseReportController extends Controller
{
    public function index()
    {
        return view('backend.pages.purchase_reports.index');
    }

    public function fetchReportData(Request $request)
    {
        $query = Purchases::with('product') // Assuming Purchases has a relation with Product
            ->when($request->product_id, function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            })
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                $q->whereBetween('created_at', [$request->start_date, $request->end_date]);
            })
            ->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('product_name', fn($item) => $item->product->name ?? 'N/A')
            ->addColumn('quantity', fn($item) => $item->quantity)
            ->addColumn('date', fn($item) => $item->created_at->format('Y-m-d'))
            ->make(true);
    }

    public function getDropdownData()
    {
        return response()->json([
            'products' => Product::select('id', 'name')->get(),
        ]);
    }
}
