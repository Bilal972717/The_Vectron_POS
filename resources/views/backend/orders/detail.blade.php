@extends('backend.master')
@section('title', 'Order #'.$order->id.' — Detail')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h4 class="mb-0">Order #{{ $order->id }}</h4>
    <div class="d-flex gap-2 flex-wrap no-print">
      <a href="{{ route('backend.admin.orders.invoice', $order->id) }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-invoice"></i> Invoice
      </a>
      <a href="{{ route('backend.admin.orders.pos-invoice', $order->id) }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-receipt"></i> POS Receipt
      </a>
      @if(!$order->status)
      <a href="{{ route('backend.admin.due.collection', $order->id) }}" class="btn btn-warning btn-sm">
        <i class="fas fa-hand-holding-usd"></i> Collect Due
      </a>
      @endif
      <a href="{{ route('backend.admin.orders.return', $order->id) }}" class="btn btn-danger btn-sm">
        <i class="fas fa-undo"></i> Return
      </a>
      <a href="{{ route('backend.admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back
      </a>
    </div>
  </div>

  <div class="card-body">

    {{-- Top info row --}}
    <div class="row mb-4">

      {{-- Customer --}}
      <div class="col-md-3 mb-3">
        <div class="card h-100 border-left-primary shadow-sm">
          <div class="card-body">
            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Customer</div>
            <div class="h6 mb-0 font-weight-bold">{{ $order->customer->name ?? 'N/A' }}</div>
            <small class="text-muted">
              {{ $order->customer->phone ?? '' }}<br>
              {{ $order->customer->address ?? '' }}
              @if($order->customer->city ?? false), {{ $order->customer->city }}@endif
            </small>
          </div>
        </div>
      </div>

      {{-- Order Info --}}
      <div class="col-md-3 mb-3">
        <div class="card h-100 border-left-info shadow-sm">
          <div class="card-body">
            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Order Info</div>
            <table class="table table-borderless table-sm mb-0" style="font-size:13px;">
              <tr><td class="text-muted pl-0">Date</td><td>{{ $order->created_at->format('d M Y, h:i A') }}</td></tr>
              <tr><td class="text-muted pl-0">Status</td>
                <td>
                  @if($order->status)
                    <span class="badge badge-success">Paid</span>
                  @else
                    <span class="badge badge-danger">Due</span>
                  @endif
                  @if($order->is_returned) <span class="badge badge-warning ml-1">Has Returns</span> @endif
                </td>
              </tr>
              @if($order->promised_payment_date)
              <tr><td class="text-muted pl-0">Pay By</td><td>{{ $order->promised_payment_date->format('d M Y') }}</td></tr>
              @endif
            </table>
          </div>
        </div>
      </div>

      {{-- Packed & Delivered --}}
      <div class="col-md-3 mb-3">
        <div class="card h-100 shadow-sm" style="border-left: 4px solid #f6c23e;">
          <div class="card-body">
            <div class="text-xs font-weight-bold text-uppercase mb-2" style="color:#f6c23e;">Staff Assignment</div>
            <div class="mb-2">
              <span class="text-muted" style="font-size:12px;">PACKED BY</span><br>
              @if($order->packer)
                <span class="badge badge-pill" style="background:#e8f4fd;color:#2e86de;font-size:13px;padding:6px 10px;">
                  <i class="fas fa-box mr-1"></i>{{ $order->packer->name }}
                </span>
              @else
                <span class="text-muted font-italic">Not assigned</span>
              @endif
            </div>
            <div>
              <span class="text-muted" style="font-size:12px;">DELIVERED BY</span><br>
              @if($order->deliverer)
                <span class="badge badge-pill" style="background:#e8f9f0;color:#27ae60;font-size:13px;padding:6px 10px;">
                  <i class="fas fa-truck mr-1"></i>{{ $order->deliverer->name }}
                </span>
              @else
                <span class="text-muted font-italic">Not assigned</span>
              @endif
            </div>
          </div>
        </div>
      </div>

      {{-- Delivery Status --}}
      <div class="col-md-3 mb-3">
        <div class="card h-100 border-left-success shadow-sm">
          <div class="card-body">
            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Delivery</div>
            <div class="mb-1">
              @if($order->is_delivered)
                <span class="badge badge-success" style="font-size:14px;"><i class="fas fa-check-circle"></i> Delivered</span>
              @else
                <span class="badge badge-warning" style="font-size:14px;"><i class="fas fa-clock"></i> Pending</span>
              @endif
            </div>
            @if($order->delivery_note)
              <small class="text-muted">{{ $order->delivery_note }}</small>
            @endif
          </div>
        </div>
      </div>

    </div>

    {{-- Order Items --}}
    <h5 class="mb-3"><i class="fas fa-shopping-basket mr-1"></i> Items ({{ $order->total_item }})</h5>
    <div class="table-responsive mb-4">
      <table class="table table-striped table-bordered table-sm">
        <thead class="thead-dark">
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Product</th>
            <th>Category</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
            @if($order->is_returned)
            <th>Return Qty</th>
            <th>Return Amt</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @foreach($order->products as $item)
          <tr @if($item->return_qty > 0) style="background:#fff3cd;" @endif>
            <td>{{ $loop->index + 1 }}</td>
            <td><small class="text-muted">{{ $item->product->sku ?? '-' }}</small></td>
            <td>
              {{ $item->product->name }}
              @if($item->manual_price !== null)
                <br><small class="badge badge-info">Custom price</small>
              @endif
            </td>
            <td><small>{{ optional($item->product->category)->name ?? '-' }}</small></td>
            <td>{{ $item->quantity }} {{ optional($item->product->unit)->short_name }}</td>
            <td>{{ number_format($item->discounted_price, 2) }}</td>
            <td>{{ number_format($item->total, 2) }}</td>
            @if($order->is_returned)
            <td>{{ $item->return_qty > 0 ? $item->return_qty : '—' }}</td>
            <td>{{ $item->return_qty > 0 ? number_format($item->return_amount, 2) : '—' }}</td>
            @endif
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Totals + Transactions side by side --}}
    <div class="row">
      <div class="col-md-5 offset-md-7">
        <table class="table table-sm table-borderless">
          <tr><td>Sub Total</td><td class="text-right">{{ currency()->symbol ?? '' }} {{ number_format($order->sub_total, 2) }}</td></tr>
          <tr><td>Discount</td><td class="text-right">{{ currency()->symbol ?? '' }} {{ number_format($order->discount, 2) }}</td></tr>
          @if($order->is_returned)
          <tr class="text-danger"><td>Returns</td><td class="text-right">- {{ currency()->symbol ?? '' }} {{ number_format($order->products->sum('return_amount'), 2) }}</td></tr>
          @endif
          <tr class="font-weight-bold"><td>Total</td><td class="text-right">{{ currency()->symbol ?? '' }} {{ number_format($order->total, 2) }}</td></tr>
          <tr><td>Paid</td><td class="text-right text-success">{{ currency()->symbol ?? '' }} {{ number_format($order->paid, 2) }}</td></tr>
          <tr class="{{ $order->due > 0 ? 'text-danger' : '' }} font-weight-bold"><td>Due</td><td class="text-right">{{ currency()->symbol ?? '' }} {{ number_format($order->due, 2) }}</td></tr>
        </table>
      </div>
    </div>

    {{-- Payment transactions --}}
    @if($order->transactions->count() > 0)
    <h5 class="mt-3 mb-2"><i class="fas fa-exchange-alt mr-1"></i> Payment History</h5>
    <table class="table table-sm table-striped" style="max-width:500px;">
      <thead><tr><th>Date</th><th>Amount</th><th>Method</th></tr></thead>
      <tbody>
        @foreach($order->transactions as $tx)
        <tr>
          <td>{{ $tx->created_at->format('d M Y, h:i A') }}</td>
          <td>{{ currency()->symbol ?? '' }} {{ number_format($tx->amount, 2) }}</td>
          <td>{{ ucfirst($tx->paid_by ?? 'cash') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif

  </div>
</div>
@endsection

@push('style')
<style>
  .border-left-primary { border-left: 4px solid #4e73df !important; }
  .border-left-info    { border-left: 4px solid #36b9cc !important; }
  .border-left-success { border-left: 4px solid #1cc88a !important; }
  .text-xs { font-size: 0.7rem; }
  @media print { .no-print { display: none !important; } }
</style>
@endpush
