<?php

namespace App\Http\Controllers\Backend\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Order;
use App\Models\PayrollRun;
use App\Models\StaffProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('payroll_view'), 403);

        $month = $request->input('month', Carbon::today()->format('Y-m'));

        $runs = PayrollRun::with('user')
            ->where('month', $month)
            ->get();

        $staff = User::whereHas('staffProfile')->with('staffProfile')->orderBy('name')->get();

        return view('backend.hr.payroll.index', compact('runs', 'staff', 'month'));
    }

    /**
     * Generate / recalculate payroll for all active staff for a given month
     */
    public function generate(Request $request)
    {
        abort_if(!auth()->user()->can('payroll_generate'), 403);

        $request->validate(['month' => 'required|date_format:Y-m']);
        $month = $request->month;

        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth   = Carbon::parse($month)->endOfMonth();
        $workingDays  = $this->countWorkingDays($startOfMonth, $endOfMonth);

        $staff = User::whereHas('staffProfile', fn($q) => $q->where('is_active', true))
            ->with('staffProfile')
            ->get();

        foreach ($staff as $user) {
            $profile = $user->staffProfile;

            // Count attendance
            $attendance = Attendance::where('user_id', $user->id)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->get();

            $presentDays = $attendance->where('status', 'present')->count();
            $halfDays    = $attendance->where('status', 'half_day')->count();

            // Salary calculation
            $effectiveDays = $presentDays + ($halfDays * 0.5);
            $dailyRate     = $workingDays > 0 ? $profile->base_salary / $workingDays : 0;
            $earnedSalary  = round($dailyRate * $effectiveDays, 2);

            // Commission calculation
            $packingCommission   = 0;
            $deliveryCommission  = 0;

            if ($profile->salary_type === 'base_plus_commission') {
                // Orders packed by this user this month
                $packedOrders = Order::where('packed_by', $user->id)
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->sum('total');
                $packingCommission = round($packedOrders * ($profile->packing_commission_rate / 100), 2);

                // Orders delivered by this user this month
                $deliveredOrders = Order::where('delivered_by', $user->id)
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->sum('total');
                $deliveryCommission = round($deliveredOrders * ($profile->delivery_commission_rate / 100), 2);
            }

            $net = $earnedSalary + $packingCommission + $deliveryCommission;

            // Upsert — keep existing bonus/deductions/note if already created
            $existing = PayrollRun::where('user_id', $user->id)->where('month', $month)->first();
            $bonus      = $existing ? $existing->bonus : 0;
            $deductions = $existing ? $existing->deductions : 0;
            $note       = $existing ? $existing->note : null;
            $status     = $existing ? $existing->status : 'draft';

            PayrollRun::updateOrCreate(
                ['user_id' => $user->id, 'month' => $month],
                [
                    'working_days'        => $workingDays,
                    'present_days'        => $presentDays,
                    'half_days'           => $halfDays,
                    'base_salary'         => $earnedSalary,
                    'packing_commission'  => $packingCommission,
                    'delivery_commission' => $deliveryCommission,
                    'bonus'               => $bonus,
                    'deductions'          => $deductions,
                    'net_salary'          => round($net + $bonus - $deductions, 2),
                    'status'              => $status,
                    'note'                => $note,
                    'processed_by'        => auth()->id(),
                ]
            );
        }

        return redirect()->route('backend.admin.hr.payroll.index', ['month' => $month])
            ->with('success', 'Payroll generated for ' . Carbon::parse($month)->format('F Y'));
    }

    /**
     * Update bonus/deductions/note for a single payroll run
     */
    public function update(Request $request, $id)
    {
        abort_if(!auth()->user()->can('payroll_generate'), 403);

        $run = PayrollRun::findOrFail($id);
        $request->validate([
            'bonus'      => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'note'       => 'nullable|string|max:500',
        ]);

        $run->bonus      = $request->bonus ?? 0;
        $run->deductions = $request->deductions ?? 0;
        $run->note       = $request->note;
        $run->net_salary = round(
            $run->base_salary + $run->packing_commission + $run->delivery_commission
            + $run->bonus - $run->deductions,
            2
        );
        $run->save();

        return back()->with('success', 'Payroll updated.');
    }

    /**
     * Mark payroll as paid
     */
    public function markPaid(Request $request, $id)
    {
        abort_if(!auth()->user()->can('payroll_generate'), 403);

        $run = PayrollRun::findOrFail($id);
        $run->status    = 'paid';
        $run->paid_date = Carbon::today();
        $run->save();

        return back()->with('success', 'Marked as paid.');
    }

    /**
     * Payslip for a single staff member
     */
    public function payslip($id)
    {
        abort_if(!auth()->user()->can('payroll_view'), 403);
        $run = PayrollRun::with(['user.staffProfile'])->findOrFail($id);
        return view('backend.hr.payroll.payslip', compact('run'));
    }

    /**
     * Commission summary — who packed/delivered what
     */
    public function commissions(Request $request)
    {
        abort_if(!auth()->user()->can('payroll_view'), 403);

        $month = $request->input('month', Carbon::today()->format('Y-m'));
        $startOfMonth = Carbon::parse($month)->startOfMonth();
        $endOfMonth   = Carbon::parse($month)->endOfMonth();

        $staff = User::whereHas('staffProfile')->with('staffProfile')->get()->map(function ($user) use ($startOfMonth, $endOfMonth) {
            $profile = $user->staffProfile;

            $packedOrders = Order::where('packed_by', $user->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->get();
            $deliveredOrders = Order::where('delivered_by', $user->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->get();

            $user->packed_count    = $packedOrders->count();
            $user->packed_total    = $packedOrders->sum('total');
            $user->packing_comm    = round($user->packed_total * ($profile->packing_commission_rate / 100), 2);
            $user->delivered_count = $deliveredOrders->count();
            $user->delivered_total = $deliveredOrders->sum('total');
            $user->delivery_comm   = round($user->delivered_total * ($profile->delivery_commission_rate / 100), 2);

            return $user;
        });

        return view('backend.hr.payroll.commissions', compact('staff', 'month'));
    }

    private function countWorkingDays(Carbon $start, Carbon $end): int
    {
        $days = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            if (!$current->isWeekend()) $days++;
            $current->addDay();
        }
        return $days;
    }
}
