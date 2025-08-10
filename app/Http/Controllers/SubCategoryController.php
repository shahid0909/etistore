<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SubCategoryController extends Controller
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
     * Display a listing of the sub_category.
     */
    public function index()
    {
        if (is_null($this->user) || !$this->user->can('sub-category.view')) {
            abort(403, 'You are unauthorized to view this page.');
        }

            // Fetch all categorys
    $categorys  = Category::where('status', 'Y')->get();

    // Pass categorys to the view
    return view('backend.pages.sub_category.index', compact('categorys'));
    }

    /**
     * Store or update sub_category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);

        try {
            DB::beginTransaction();

            $sub_category = $request->sub_category_id
                ? SubCategory::findOrFail($request->sub_category_id)
                : new SubCategory();

            $sub_category->name = $request->name;
            $sub_category->category_id = $request->category_id;
            $sub_category->description = $request->description;
            $sub_category->status = $request->has('status') && $request->status === 'on' ? 'Y' : 'N';
            $sub_category->save();

            DB::commit();

            return response()->json([
                'success' => $request->sub_category_id
                    ? 'sub_category updated successfully!'
                    : 'sub_category created successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Operation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get sub_category data for DataTables.
     */
    public function datatable()
    {
        try {
            $data = SubCategory::with('category')->orderBy('id', 'desc')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($item) {
                    return $item->status === 'Y' ? 'Active' : 'Inactive';
                })
                ->addColumn('category', function ($item) {
                    return $item->category->name ?? 'N/A';
                })
                ->addColumn('action', function ($item) {
                    $usr = Auth::guard('admin')->user();
                    $buttons = '';

                    if ($usr->can('sub-category.edit')) {
                        $buttons .= '<button class="btn btn-primary btn-sm edit-btn" data-id="' . $item->id . '">
                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                     </button> ';
                    }

                    if ($usr->can('sub-category.delete')) {
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
     * Edit sub_category.
     */
    public function edit($id)
    {
        $sub_category = SubCategory::findOrFail($id);
        return response()->json($sub_category);
    }

    /**
     * Delete sub_category.
     */
    public function destroy($id)
    {
        try {
            SubCategory::findOrFail($id)->delete();
            return response()->json(['success' => 'sub_category deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
