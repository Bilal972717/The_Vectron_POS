@extends('backend.master')
@section('title', 'Sales Ledger')
@section('content')
<div class="card">
  <div class="card-header"><h4>Sales Ledger by Customer</h4></div>
  <div class="card-body">
    <form method="GET" action="{{ route('backend.admin.sales.ledger') }}" class="row mb-4 no-print">
      <div class="col-md-3">
        <label>Customer</label>
        <select name="customer_id" class="form-control" required>
          <option value="">— Select Customer —</option>
          @foreach($customers as $c)
            <option value="{{ $c->id }}" @if($customer_id == $c->id) selected @endif>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <label>From</label>
        <input type="date" name="start_date" class="form-control" value="{{ $start_date->format('Y-m-d') }}">
      </div>
      <div class="col-md-2">
        <label>To</label>
        <input type="date" name="end_date" class="form-control" value="{{ $end_date->format('Y-m-d') }}">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary btn-block">Generate</button>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="button" onclick="window.print()" class="btn btn-success btn-block"><i class="fas fa-print"></i> Print</button>
      </div>
    </form>

    @if($customer)
    <div class="print-header text-center mb-3" style="display:none;">
      <h5>Sales Ledger: {{ $customer->name }}</h5>
      <p>{{ $start_date->format('d M Y') }} — {{ $end_date->format('d M Y') }}</p>
    </div>

    <table class="table table-hover table-striped">
      <thead class="thead-dark">
        <tr>
          <th>#</th>
          <th>Date</th>
          <th>Invoice</th>
          <th>Items</th>
          <th>Sub Total</th>
          <th>Discount</th>
          <th>Total</th>
          <th>Paid</th>
          <th>Due</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $index => $order)
        <tr>
          <td>{{ $index + 1 }}</td>
          <td>{{ $order->created_at->format('d-m-Y') }}</td>
          <td>#{{ $order->id }}</td>
          <td>{{ $order->total_item }}</td>
          <td>{{ number_format($order->sub_total, 2) }}</td>
          <td>{{ number_format($order->discount, 2) }}</td>
          <td>{{ number_format($order->total, 2) }}</td>
          <td>{{ number_format($order->paid, 2) }}</td>
          <td @if($order->due > 0) class="text-danger" @endif>{{ number_format($order->due, 2) }}</td>
          <td>
            @if($order->status)
              <span class="badge badge-success">Paid</span>
            @else
              <span class="badge badge-danger">Due</span>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="10" class="text-center">No orders found for this customer in the selected period.</td></tr>
        @endforelse
      </tbody>
      @if($orders->count() > 0)
      <tfoot class="font-weight-bold">
        <tr>
          <td colspan="4">Totals</td>
          <td>{{ number_format($totals['sub_total'], 2) }}</td>
          <td>{{ number_format($totals['discount'], 2) }}</td>
          <td>{{ number_format($totals['total'], 2) }}</td>
          <td>{{ number_format($totals['paid'], 2) }}</td>
          <td class="text-danger">{{ number_format($totals['due'], 2) }}</td>
          <td></td>
        </tr>
      </tfoot>
      @endif
    </table>
    @endif
  </div>
</div>
@endsection
@push('style')
<style>
  @media print {
    .no-print { display: none !important; }
    .print-header { display: block !important; }
  }
</style>
@endpush
