<?php

namespace App\Http\Controllers\Backend\HR;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('staff_view'), 403);

        if ($request->ajax()) {
            $staff = User::with('staffProfile', 'roles')->whereHas('staffProfile')->latest()->get();
            return DataTables::of($staff)
                ->addIndexColumn()
                ->addColumn('employee_code', fn($u) => optional($u->staffProfile)->employee_code ?? '-')
                ->addColumn('position', fn($u) => optional($u->staffProfile)->position ?? '-')
                ->addColumn('salary_type', fn($u) => optional($u->staffProfile)->salary_type === 'base_plus_commission' ? 'Base + Commission' : 'Fixed')
                ->addColumn('base_salary', fn($u) => number_format(optional($u->staffProfile)->base_salary ?? 0, 2))
                ->addColumn('status', fn($u) => optional($u->staffProfile)->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', function ($u) {
                    return '
                        <a href="'.route('backend.admin.hr.staff.edit', $u->id).'" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>
                        <a href="'.route('backend.admin.hr.attendance.index').'?user_id='.$u->id.'" class="btn btn-sm btn-info"><i class="fas fa-calendar-check"></i> Attendance</a>
                    ';
                })
                ->rawColumns(['status', 'action'])
                ->toJson();
        }

        return view('backend.hr.staff.index');
    }

    public function create()
    {
        abort_if(!auth()->user()->can('staff_create'), 403);
        $users = User::whereDoesntHave('staffProfile')->orderBy('name')->get();
        return view('backend.hr.staff.create', compact('users'));
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->can('staff_create'), 403);

        $request->validate([
            'user_id' => 'required|exists:users,id|unique:staff_profiles,user_id',
            'employee_code' => 'nullable|string|max:50|unique:staff_profiles,employee_code',
            'position' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'join_date' => 'nullable|date',
            'base_salary' => 'required|numeric|min:0',
            'salary_type' => 'required|in:fixed,base_plus_commission',
            'packing_commission_rate' => 'nullable|numeric|min:0|max:100',
            'delivery_commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        StaffProfile::create($request->only([
            'user_id', 'employee_code', 'position', 'phone', 'address',
            'join_date', 'base_salary', 'salary_type',
            'packing_commission_rate', 'delivery_commission_rate',
        ]));

        return redirect()->route('backend.admin.hr.staff.index')->with('success', 'Staff member created.');
    }

    public function edit($id)
    {
        abort_if(!auth()->user()->can('staff_update'), 403);
        $user = User::with('staffProfile')->findOrFail($id);
        $profile = $user->staffProfile ?? new StaffProfile(['user_id' => $id]);
        return view('backend.hr.staff.edit', compact('user', 'profile'));
    }

    public function update(Request $request, $id)
    {
        abort_if(!auth()->user()->can('staff_update'), 403);

        $request->validate([
            'employee_code' => 'nullable|string|max:50|unique:staff_profiles,employee_code,' . optional(StaffProfile::where('user_id', $id)->first())->id,
            'position' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'join_date' => 'nullable|date',
            'base_salary' => 'required|numeric|min:0',
            'salary_type' => 'required|in:fixed,base_plus_commission',
            'packing_commission_rate' => 'nullable|numeric|min:0|max:100',
            'delivery_commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $profile = StaffProfile::firstOrNew(['user_id' => $id]);
        $profile->fill($request->only([
            'employee_code', 'position', 'phone', 'address',
            'join_date', 'base_salary', 'salary_type',
            'packing_commission_rate', 'delivery_commission_rate', 'is_active',
        ]));
        $profile->user_id = $id;
        $profile->is_active = $request->boolean('is_active');
        $profile->save();

        return redirect()->route('backend.admin.hr.staff.index')->with('success', 'Staff profile updated.');
    }
}
