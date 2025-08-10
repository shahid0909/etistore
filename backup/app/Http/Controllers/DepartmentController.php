<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class DepartmentController extends Controller
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
     * Display a listing of the Departments.
     */
    public function index()
    {
        if (is_null($this->user) || !$this->user->can('admin.view')) {
            abort(403, 'You are unauthorized to view this page.');
        }

        return view('backend.pages.department.index');
    }

    /**
     * Store or update a Department.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $Department = $request->department_id
                ? Department::findOrFail($request->department_id)
                : new Department();

            $Department->name = $request->name;
            $Department->description = $request->description;
            $Department->status = $request->has('status') && $request->status === 'on' ? 'Y' : 'N';
            $Department->save();

            DB::commit();

            return response()->json([
                'success' => $request->department_id
                    ? 'Department updated successfully!'
                    : 'Department created successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Operation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get Departments data for DataTables.
     */
    public function datatable()
    {
        try {
            // Fetch Departments and order by 'id' descending
            $data = Department::orderBy('id', 'desc')->get();
    
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
     * Edit a Department.
     */
    public function edit($id)
    {
        $Department = Department::findOrFail($id);
        return response()->json($Department);
    }

    /**
     * Delete a Department.
     */
    public function destroy($id)
    {
        try {
            Department::findOrFail($id)->delete();
            return response()->json(['success' => 'Department deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
