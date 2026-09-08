<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Services\AuditService;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount(['doctors', 'staff', 'rooms', 'appointments'])->get();
        return view('admin.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:departments',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $dept = Department::create($validated);
        AuditService::log('CREATE', 'departments', $dept->id, "Created department {$dept->name}");

        return back()->with('success', "Department {$dept->name} created successfully.");
    }

    public function update(Request $request, $id)
    {
        $dept = Department::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:departments,code,' . $dept->id,
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $dept->update($validated);
        AuditService::log('UPDATE', 'departments', $dept->id, "Updated department {$dept->name}");

        return back()->with('success', "Department {$dept->name} updated successfully.");
    }

    public function destroy($id)
    {
        $dept = Department::findOrFail($id);
        $name = $dept->name;
        $dept->delete();

        AuditService::log('DELETE', 'departments', $id, "Deleted department {$name}");

        return back()->with('success', "Department {$name} deleted.");
    }
}
