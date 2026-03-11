<?php

namespace App\Http\Controllers\Backend\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\StaffProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('attendance_view'), 403);

        $month = $request->input('month', Carbon::today()->format('Y-m'));
        $userId = $request->input('user_id');

        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth   = Carbon::parse($month)->endOfMonth();

        $staffQuery = User::whereHas('staffProfile', fn($q) => $q->where('is_active', true))
            ->with(['staffProfile', 'attendances' => fn($q) => $q->whereBetween('date', [$startOfMonth, $endOfMonth])]);

        if ($userId) {
            $staffQuery->where('id', $userId);
        }

        $staff = $staffQuery->get();

        // Build day columns for the month
        $days = [];
        $d = $startOfMonth->copy();
        while ($d->lte($endOfMonth)) {
            $days[] = $d->copy();
            $d->addDay();
        }

        $allStaff = User::whereHas('staffProfile')->orderBy('name')->get();

        return view('backend.hr.attendance.index', compact('staff', 'days', 'month', 'userId', 'allStaff'));
    }

    /**
     * Bulk mark attendance for a single day (all staff at once)
     */
    public function markDay(Request $request)
    {
        abort_if(!auth()->user()->can('attendance_mark'), 403);

        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.user_id' => 'required|exists:users,id',
            'attendance.*.status' => 'required|in:present,absent,half_day,leave',
            'attendance.*.note' => 'nullable|string|max:255',
        ]);

        foreach ($request->attendance as $entry) {
            Attendance::updateOrCreate(
                ['user_id' => $entry['user_id'], 'date' => $request->date],
                [
                    'status' => $entry['status'],
                    'note' => $entry['note'] ?? null,
                    'marked_by' => auth()->id(),
                ]
            );
        }

        return back()->with('success', 'Attendance saved for ' . Carbon::parse($request->date)->format('d M Y'));
    }

    /**
     * Quick-mark a single staff member's attendance
     */
    public function markSingle(Request $request)
    {
        abort_if(!auth()->user()->can('attendance_mark'), 403);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,half_day,leave',
            'note' => 'nullable|string|max:255',
        ]);

        Attendance::updateOrCreate(
            ['user_id' => $request->user_id, 'date' => $request->date],
            [
                'status'     => $request->status,
                'note'       => $request->note,
                'marked_by'  => auth()->id(),
            ]
        );

        return response()->json(['message' => 'Attendance updated.']);
    }

    /**
     * Daily attendance sheet — mark all staff for one date
     */
    public function daily(Request $request)
    {
        abort_if(!auth()->user()->can('attendance_mark'), 403);

        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        $staff = User::whereHas('staffProfile', fn($q) => $q->where('is_active', true))
            ->with(['staffProfile', 'attendances' => fn($q) => $q->where('date', $date)])
            ->get();

        return view('backend.hr.attendance.daily', compact('staff', 'date'));
    }
}
