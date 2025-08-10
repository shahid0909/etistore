<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
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
     * Display a listing of the categorys.
     */
    public function index()
    {
        if (is_null($this->user) || !$this->user->can('admin.view')) {
            abort(403, 'You are unauthorized to view this page.');
        }

        return view('backend.pages.category.index');
    }

    /**
     * Store or update a category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $category = $request->category_id
                ? Category::findOrFail($request->category_id)
                : new Category();

            $category->name = $request->name;
            $category->description = $request->description;
            $category->status = $request->has('status') && $request->status === 'on' ? 'Y' : 'N';
            $category->save();

            DB::commit();

            return response()->json([
                'success' => $request->category_id
                    ? 'category updated successfully!'
                    : 'category created successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Operation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get categorys data for DataTables.
     */
    public function datatable()
    {
        try {
            // Fetch categorys and order by 'id' descending
            $data = Category::orderBy('id', 'desc')->get();
    
            // Return datatable JSON response
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($item) {
                    return $item->status === 'Y' ? 'Active' : 'Inactive';
                })
                ->addColumn('action', function ($item) {
                    return '
                        <button class="btn btn-primary btn-sm edit-btn" data-id="' . $item->id . '">Edit</button>
                        <button class="btn btn-danger btn-sm delete-btn" data-id="' . $item->id . '">Delete</button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load data: ' . $e->getMessage()], 500);
        }
    }
    

    /**
     * Edit a category.
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    /**
     * Delete a category.
     */
    public function destroy($id)
    {
        try {
            Category::findOrFail($id)->delete();
            return response()->json(['success' => 'Category deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
