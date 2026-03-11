@extends('backend.master')
@section('title', 'Commission Summary')
@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4>Commission Summary — {{ \Carbon\Carbon::parse($month)->format('F Y') }}</h4>
    <div class="d-flex gap-2">
      <form method="GET">
        <input type="month" name="month" class="form-control" value="{{ $month }}" onchange="this.form.submit()">
      </form>
      <button onclick="window.print()" class="btn btn-success no-print"><i class="fas fa-print"></i> Print</button>
    </div>
  </div>
  <div class="card-body">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>#</th><th>Staff</th><th>Position</th>
          <th>Orders Packed</th><th>Packed Value</th><th>Pack Rate</th><th>Pack Commission</th>
          <th>Orders Delivered</th><th>Delivered Value</th><th>Del. Rate</th><th>Del. Commission</th>
          <th>Total Commission</th>
        </tr>
      </thead>
      <tbody>
        @foreach($staff as $i => $user)
        <tr>
          <td>{{ $i+1 }}</td>
          <td>{{ $user->name }}</td>
          <td>{{ optional($user->staffProfile)->position ?? '-' }}</td>
          <td>{{ $user->packed_count }}</td>
          <td>{{ number_format($user->packed_total, 2) }}</td>
          <td>{{ $user->staffProfile->packing_commission_rate }}%</td>
          <td><strong>{{ number_format($user->packing_comm, 2) }}</strong></td>
          <td>{{ $user->delivered_count }}</td>
          <td>{{ number_format($user->delivered_total, 2) }}</td>
          <td>{{ $user->staffProfile->delivery_commission_rate }}%</td>
          <td><strong>{{ number_format($user->delivery_comm, 2) }}</strong></td>
          <td class="text-primary font-weight-bold">{{ number_format($user->packing_comm + $user->delivery_comm, 2) }}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr class="font-weight-bold">
          <td colspan="6">Totals</td>
          <td>{{ number_format($staff->sum('packing_comm'), 2) }}</td>
          <td colspan="3"></td>
          <td>{{ number_format($staff->sum('delivery_comm'), 2) }}</td>
          <td class="text-primary">{{ number_format($staff->sum(fn($u) => $u->packing_comm + $u->delivery_comm), 2) }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
@endsection
@push('style')
<style>@media print { .no-print { display:none !important; } }</style>
@endpush
