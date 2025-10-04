<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Department;
use App\Models\Designation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
    $designations = Designation::where('status', 'Y')->get();

    // Pass departments to the view
    return view('backend.pages.staff.index', compact('departments','designations'));
    }

    /**
     * Store or update Staff.
     */
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email|unique:staff,email,' . $request->staff_id,
        'phone' => 'required|string|max:15',
        'department_id' => 'required|exists:departments,id',
        'designation_id' => 'required|exists:designations,id',
        'role' => 'nullable|string|exists:roles,name', // <-- optional role input
    ]);

    try {
        DB::beginTransaction();

        if ($request->staff_id) {
            // Update existing staff + linked user
            $staff = Staff::findOrFail($request->staff_id);
            $user = $staff->user;
        } else {
            $staff = new Staff();

            // Create linked User
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make('123456'); // default password
            $user->save();
        }

        // Keep user updated
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        // 🔹 Assign role (default employee if none passed)
        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        } else {
            $user->syncRoles(['employee']);
        }

        // Save Staff and link user
        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->phone = $request->phone;
        $staff->department_id = $request->department_id;
        $staff->designation_id = $request->designation_id;
        $staff->status = $request->has('status') && $request->status === 'on' ? 'Y' : 'N';
        $staff->user_id = $user->id;
        $staff->save();

        DB::commit();

        return response()->json([
            'success' => $request->staff_id
                ? 'Staff updated successfully!'
                : 'Staff & User created successfully with role!',
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
            $data = Staff::with(['department', 'designation'])->orderBy('id', 'desc')->get();
    
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($item) {
                    return $item->status === 'Y' ? 'Active' : 'Inactive';
                })
                ->addColumn('department', function ($item) {
                    return $item->department->name ?? 'N/A';
                })
                ->addColumn('designation', function ($item) {
                    return $item->designation->name ?? 'N/A';
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
