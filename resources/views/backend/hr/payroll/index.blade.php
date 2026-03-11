@extends('backend.master')
@section('title', 'Payroll')
@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4>Payroll — {{ \Carbon\Carbon::parse($month)->format('F Y') }}</h4>
    <div class="d-flex gap-2">
      <form method="GET" class="d-flex gap-2">
        <input type="month" name="month" class="form-control" value="{{ $month }}">
        <button type="submit" class="btn btn-secondary">View</button>
      </form>
      <form method="POST" action="{{ route('backend.admin.hr.payroll.generate') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <button type="submit" class="btn btn-warning" onclick="return confirm('Generate/recalculate payroll for {{ \Carbon\Carbon::parse($month)->format('F Y') }}?')">
          <i class="fas fa-calculator"></i> Generate Payroll
        </button>
      </form>
      <a href="{{ route('backend.admin.hr.payroll.commissions', ['month' => $month]) }}" class="btn btn-info">
        <i class="fas fa-award"></i> Commissions
      </a>
    </div>
  </div>
  <div class="card-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    @if($runs->count() > 0)
    <table class="table table-striped">
      <thead>
        <tr>
          <th>#</th><th>Staff</th><th>Days</th><th>Present</th>
          <th>Base Salary</th><th>Pack Comm.</th><th>Del. Comm.</th>
          <th>Bonus</th><th>Deductions</th><th>Net Salary</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($runs as $i => $run)
        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ $run->user->name }}</td>
          <td>{{ $run->working_days }}</td>
          <td>{{ $run->present_days }} + {{ $run->half_days }}×½</td>
          <td>{{ number_format($run->base_salary, 2) }}</td>
          <td>{{ number_format($run->packing_commission, 2) }}</td>
          <td>{{ number_format($run->delivery_commission, 2) }}</td>
          <td>
            <form action="{{ route('backend.admin.hr.payroll.update', $run->id) }}" method="POST" class="d-flex gap-1">
              @csrf @method('PUT')
              <input type="number" name="bonus" value="{{ $run->bonus }}" class="form-control form-control-sm" style="width:80px" min="0" step="0.01">
          </td>
          <td>
              <input type="number" name="deductions" value="{{ $run->deductions }}" class="form-control form-control-sm" style="width:80px" min="0" step="0.01">
          </td>
          <td><strong>{{ number_format($run->net_salary, 2) }}</strong></td>
          <td>
            @if($run->status == 'paid')
              <span class="badge badge-success">Paid</span>
              <br><small>{{ $run->paid_date?->format('d M Y') }}</small>
            @else
              <span class="badge badge-warning">Draft</span>
            @endif
          </td>
          <td class="d-flex gap-1 flex-wrap">
              <button type="submit" class="btn btn-xs btn-primary"><i class="fas fa-save"></i></button>
            </form>
            @if($run->status != 'paid')
            <form action="{{ route('backend.admin.hr.payroll.mark-paid', $run->id) }}" method="POST" style="display:inline">
              @csrf @method('PUT')
              <button type="submit" class="btn btn-xs btn-success" onclick="return confirm('Mark as paid?')"><i class="fas fa-check"></i> Paid</button>
            </form>
            @endif
            <a href="{{ route('backend.admin.hr.payroll.payslip', $run->id) }}" class="btn btn-xs btn-secondary" target="_blank"><i class="fas fa-print"></i></a>
          </td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr class="font-weight-bold">
          <td colspan="4">Totals</td>
          <td>{{ number_format($runs->sum('base_salary'), 2) }}</td>
          <td>{{ number_format($runs->sum('packing_commission'), 2) }}</td>
          <td>{{ number_format($runs->sum('delivery_commission'), 2) }}</td>
          <td>{{ number_format($runs->sum('bonus'), 2) }}</td>
          <td>{{ number_format($runs->sum('deductions'), 2) }}</td>
          <td>{{ number_format($runs->sum('net_salary'), 2) }}</td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    </table>
    @else
    <div class="alert alert-info">No payroll generated yet for this month. Click <strong>Generate Payroll</strong> to start.</div>
    @endif
  </div>
</div>
@endsection
