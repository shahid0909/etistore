<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class StaffController extends Controller
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
     * Display a listing of the Staff.
     */
    public function index()
    {
        if (is_null($this->user) || !$this->user->can('admin.view')) {
            abort(403, 'You are unauthorized to view this page.');
        }

            // Fetch all departments
    $departments  = Department::where('status', 'Y')->get();

    // Pass departments to the view
    return view('backend.pages.staff.index', compact('departments'));
    }

    /**
     * Store or update Staff.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email,' . $request->staff_id,
            'phone' => 'required|string|max:15',
            'department_id' => 'required|exists:departments,id',
            'designation' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $staff = $request->staff_id
                ? Staff::findOrFail($request->staff_id)
                : new Staff();

            $staff->name = $request->name;
            $staff->email = $request->email;
            $staff->phone = $request->phone;
            $staff->department_id = $request->department_id;
            $staff->designation = $request->designation;
            $staff->status = $request->has('status') && $request->status === 'on' ? 'Y' : 'N';
            $staff->save();

            DB::commit();

            return response()->json([
                'success' => $request->staff_id
                    ? 'Staff updated successfully!'
                    : 'Staff created successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Operation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get Staff data for DataTables.
     */
    public function datatable()
    {
        try {
            $data = Staff::with('department')->orderBy('id', 'desc')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($item) {
                    return $item->status === 'Y' ? 'Active' : 'Inactive';
                })
                ->addColumn('department', function ($item) {
                    return $item->department->name ?? 'N/A';
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
     * Edit Staff.
     */
    public function edit($id)
    {
        $staff = Staff::findOrFail($id);
        return response()->json($staff);
    }

    /**
     * Delete Staff.
     */
    public function destroy($id)
    {
        try {
            Staff::findOrFail($id)->delete();
            return response()->json(['success' => 'Staff deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
