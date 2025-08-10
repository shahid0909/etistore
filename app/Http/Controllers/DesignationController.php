<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Designation;
use Yajra\DataTables\Facades\DataTables;


class DesignationController extends Controller
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
     * Display a listing of the designations.
     */
    public function index()
    {
        if (is_null($this->user) || !$this->user->can('staff.create')) {
            abort(403, 'You are unauthorized to view this page.');
        }

        return view('backend.pages.designation.index');
    }

    /**
     * Store or update a designation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $designation = $request->designation_id
                ? Designation::findOrFail($request->designation_id)
                : new Designation();

            $designation->name = $request->name;
            $designation->description = $request->description;
            $designation->status = $request->has('status') && $request->status === 'on' ? 'Y' : 'N';
            $designation->save();

            DB::commit();

            return response()->json([
                'success' => $request->designation_id
                    ? 'Designation updated successfully!'
                    : 'Designation created successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Operation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get designations data for DataTables.
     */
    public function datatable()
    {
        try {
            // Fetch designations and order by 'id' descending
            $data = Designation::orderBy('id', 'desc')->get();

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
                ->addIndexColumn()
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load data: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Edit a designation.
     */
    public function edit($id)
    {
        $designation = Designation::findOrFail($id);
        return response()->json($designation);
    }

    /**
     * Delete a designation.
     */
    public function destroy($id)
    {
        try {
            Designation::findOrFail($id)->delete();
            return response()->json(['success' => 'Designation deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
