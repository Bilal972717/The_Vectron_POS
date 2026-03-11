@extends('backend.master')
@section('title', 'Process Return - Order #'.$order->id)
@section('content')
<div class="card">
  <div class="card-header">
    <h4>Process Return — Order #{{ $order->id }} ({{ $order->customer->name ?? 'N/A' }})</h4>
  </div>
  <div class="card-body">
    <form action="{{ route('backend.admin.orders.return.store', $order->id) }}" method="POST">
      @csrf
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Product</th>
            <th>Sold Qty</th>
            <th>Already Returned</th>
            <th>Return Qty</th>
            <th>Unit Price</th>
          </tr>
        </thead>
        <tbody>
          @foreach($order->products as $item)
          @php $returnable = $item->quantity - $item->return_qty; @endphp
          @if($returnable > 0)
          <tr>
            <td>{{ $item->product->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->return_qty }}</td>
            <td>
              <input type="hidden" name="returns[{{ $loop->index }}][order_product_id]" value="{{ $item->id }}">
              <input type="number" name="returns[{{ $loop->index }}][return_qty]"
                class="form-control form-control-sm" min="0" max="{{ $returnable }}" value="0" style="width:80px;">
            </td>
            <td>{{ number_format($item->quantity > 0 ? $item->total / $item->quantity : 0, 2) }} {{ currency()->symbol }}</td>
          </tr>
          @endif
          @endforeach
        </tbody>
      </table>
      <div class="row mt-3">
        <div class="col">
          <a href="{{ route('backend.admin.orders.invoice', $order->id) }}" class="btn btn-secondary">Cancel</a>
          <button type="submit" class="btn btn-warning"><i class="fas fa-undo"></i> Submit Return</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
