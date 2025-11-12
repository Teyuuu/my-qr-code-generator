<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    // Show Departments page
    public function index()
    {
        return view('departments.index'); // resources/views/departments/index.blade.php
    }

    // API: List departments
    public function list(Request $request)
    {
        $query = Department::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%$search%")
                  ->orWhere('head', 'like', "%$search%");
        }

        $departments = $query->get()->map(function($dept){
            return [
                'id' => $dept->id,
                'name' => $dept->name,
                'head' => $dept->head,
                'staff_count' => $dept->users()->count() // assuming relation
            ];
        });

        return response()->json(['data' => $departments]);
    }

    // API: Show single department
    public function show($id)
    {
        $dept = Department::findOrFail($id);
        return response()->json($dept);
    }

    // API: Create department
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'head' => 'nullable|string|max:255',
        ]);

        $dept = Department::create($request->only('name','head'));

        return response()->json($dept);
    }

    // API: Update department
    public function update(Request $request, $id)
    {
        $dept = Department::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'head' => 'nullable|string|max:255',
        ]);

        $dept->update($request->only('name','head'));

        return response()->json($dept);
    }

    // API: Delete department
    public function destroy($id)
    {
        $dept = Department::findOrFail($id);
        $dept->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
