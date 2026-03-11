@extends('backend.master')
@section('title', 'Due Payments Report')
@section('content')
<div class="card">
  <div class="card-header">
    <h4>Due Payments Report</h4>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('backend.admin.due.payments.report') }}" class="row mb-4">
      <div class="col-md-4">
        <label>Customer Name</label>
        <input type="text" name="search" class="form-control" placeholder="Search by name..." value="{{ $search }}">
      </div>
      <div class="col-md-3">
        <label>City</label>
        <select name="city" class="form-control">
          <option value="">All Cities</option>
          @foreach($cities as $c)
            <option value="{{ $c }}" @if($city == $c) selected @endif>{{ $c }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary btn-block">Filter</button>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="button" onclick="window.print()" class="btn btn-success btn-block no-print"><i class="fas fa-print"></i> Print</button>
      </div>
    </form>

    <table class="table table-hover table-striped">
      <thead class="thead-dark">
        <tr>
          <th>#</th>
          <th>Customer</th>
          <th>City</th>
          <th>Phone</th>
          <th>Total Paid {{ currency()->symbol??'' }}</th>
          <th>Total Due {{ currency()->symbol??'' }}</th>
          <th class="no-print">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($customers as $index => $customer)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $customer->name }}</td>
          <td>{{ $customer->city ?? '-' }}</td>
          <td>{{ $customer->phone ?? '-' }}</td>
          <td>{{ number_format($customer->total_paid, 2) }}</td>
          <td><strong class="text-danger">{{ number_format($customer->total_due, 2) }}</strong></td>
          <td class="no-print">
            <a href="{{ route('backend.admin.customers.orders', $customer->id) }}" class="btn btn-sm btn-info">View Orders</a>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center">No customers with due payments found.</td></tr>
        @endforelse
      </tbody>
      @if($customers->count() > 0)
      <tfoot>
        <tr class="font-weight-bold">
          <td colspan="4">Total</td>
          <td>{{ number_format($customers->sum('total_paid'), 2) }}</td>
          <td class="text-danger">{{ number_format($customers->sum('total_due'), 2) }}</td>
          <td class="no-print"></td>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
@endsection
@push('style')
<style>@media print { .no-print { display: none !important; } }</style>
@endpush
