<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentsController extends Controller
{
    /**
     * Server-side DataTables endpoint
     */
    public function datatables(Request $request)
    {
        $columns = ['name', 'head', 'staff_count'];

        $totalData = Department::count();
        $totalFiltered = $totalData;

        $limit = $request->input('length');
        $start = $request->input('start');
        $orderColumn = $request->input('order.0.column');
        $order = $columns[$orderColumn] ?? 'name';
        $dir = $request->input('order.0.dir') ?? 'asc';
        $search = $request->input('search.value');

        $query = Department::withCount('users');

        // 🔍 Search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('head', 'LIKE', "%{$search}%");
            });
            $totalFiltered = $query->count();
        }

        // 🗂 Pagination + Ordering
        $departments = $query
            ->offset($start)
            ->limit($limit)
            ->orderBy($order, $dir)
            ->get();

        // ⚙ Format for DataTables
        $data = $departments->map(function ($dept) {
            return [
                'id' => $dept->id,
                'name' => $dept->name,
                'head' => $dept->head ?? '—',
                'staff_count' => $dept->users_count,
                'action' => '
                    <button class="btn btn-sm btn-outline-secondary edit-dept" data-id="'.$dept->id.'"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger delete-dept" data-id="'.$dept->id.'"><i class="bi bi-trash"></i></button>'
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    /**
     * Store a new department
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments,name',
            'head' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $department = Department::create([
            'name' => $request->name,
            'head' => $request->head,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully',
            'data' => $department
        ], 201);
    }

    /**
     * Show a specific department
     */
    public function show($id)
    {
        $department = Department::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $department
        ]);
    }

    /**
     * Update a department
     */
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments,name,' . $id,
            'head' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $department->update([
            'name' => $request->name,
            'head' => $request->head,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully',
            'data' => $department
        ]);
    }

    /**
     * Delete a department
     */
    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        if ($department->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department with assigned staff members'
            ], 403);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully'
        ]);
    }
}
