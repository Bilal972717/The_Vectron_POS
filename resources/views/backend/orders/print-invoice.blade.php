@extends('backend.master')
@section('title', 'Invoice_'.$order->id)
@section('content')
<div class="card">
  <div class="card-body">
    <!-- Main content -->
    <section class="invoice">
      <!-- title row -->
      <div class="row mb-4">
        <div class="col-4">
          <h2 class="page-header">
            @if(readConfig('is_show_logo_invoice'))
            <img src="{{ assetImage(readconfig('site_logo')) }}" height="40" width="40" alt="Logo"
              class="brand-image img-circle elevation-3" style="opacity: .8">
            @endif
            @if(readConfig('is_show_site_invoice')){{ readConfig('site_name') }} @endif
          </h2>
        </div>
        <div class="col-4 text-center">
          {{-- Feature 6: Show DUPLICATE label on reprints --}}
          @if(isset($isDuplicate) && $isDuplicate)
            <div style="border: 3px solid red; color: red; font-size: 22px; font-weight: bold; padding: 4px 12px; display: inline-block; transform: rotate(-5deg);">DUPLICATE</div>
          @endif
          <h4 class="page-header">Invoice</h4>
        </div>
        <div class="col-4">
          <small class="float-right text-small">Date: {{date('d/m/Y')}}</small>
        </div>
      </div>
      <!-- info row -->
      <div class="row invoice-info">
        <div class="col-sm-5 invoice-col">
          @if(readConfig('is_show_customer_invoice'))
          To
          <address>
            <strong>Name: {{$order->customer->name??"N/A"}}</strong><br>
            Address: {{$order->customer->address??"N/A"}}<br>
            {{-- Feature 4: show city if available --}}
            @if($order->customer->city)City: {{$order->customer->city}}<br>@endif
            Phone: {{$order->customer->phone??"N/A"}}<br>
          </address>
          @endif
        </div>
        <div class="col-sm-4 invoice-col">
          From
          <address>
            @if(readConfig('is_show_site_invoice'))<strong>Name:{{ readConfig('site_name') }}</strong><br> @endif
            @if(readConfig('is_show_address_invoice'))Address: {{ readConfig('contact_address') }}<br>@endif
            @if(readConfig('is_show_phone_invoice'))Phone: {{ readConfig('contact_phone') }}<br>@endif
            @if(readConfig('is_show_email_invoice'))Email: {{ readConfig('contact_email') }}<br>@endif
          </address>
        </div>
        <div class="col-sm-3 invoice-col">
          Info <br>
          Sale ID #{{$order->id}}<br>
          Sale Date: {{date('d/m/Y', strtotime($order->created_at))}}<br>
          {{-- Feature 11: Promised payment date --}}
          @if($order->promised_payment_date)
          <b>Payment Due By:</b> {{ $order->promised_payment_date->format('d/m/Y') }}<br>
          @endif
          {{-- Feature 12: Delivery status --}}
          <b>Delivery:</b>
          @if($order->is_delivered)
            <span class="badge badge-success">Delivered</span>
          @else
            <span class="badge badge-warning">Pending</span>
          @endif
          @if($order->delivery_note)<br><small>{{ $order->delivery_note }}</small>@endif
        </div>
      </div>

      <!-- Table row -->
      <div class="row">
        <div class="col-12 table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>SN</th>
                {{-- Feature 5: Article Code (SKU) --}}
                <th>Code</th>
                <th>Product</th>
                {{-- Feature 5: Sub-Category --}}
                <th>Sub-Category</th>
                <th>Quantity</th>
                <th>Price {{currency()->symbol??''}}</th>
                <th>Subtotal {{currency()->symbol??''}}</th>
                {{-- Feature 1 & 7: Return columns --}}
                @if($order->is_returned)<th>Return Qty</th><th>Return Amt {{currency()->symbol??''}}</th>@endif
              </tr>
            </thead>
            <tbody>
              @foreach ($order->products as $item)
              {{-- Feature 1 & 7: Highlight returned rows --}}
              <tr @if($item->return_qty > 0) style="background-color: #fff3cd;" @endif>
                <td>{{$loop->index + 1}}</td>
                {{-- Feature 5: SKU / Article Code --}}
                <td>{{ $item->product->sku ?? '-' }}</td>
                <td>
                  {{$item->product->name}}
                  @if($item->manual_price !== null)
                    <br><small class="text-info">(Custom price)</small>
                  @endif
                </td>
                {{-- Feature 5: Sub-category --}}
                <td>{{ optional($item->product->subCategory)->name ?? '-' }}</td>
                <td>
                  {{$item->quantity}} {{optional($item->product->unit)->short_name}}
                  @if($item->return_qty > 0)
                    <br><small class="text-danger">(Returned: {{$item->return_qty}})</small>
                  @endif
                </td>
                <td>
                  {{$item->discounted_price }}
                  @if ($item->price>$item->discounted_price)
                  <br><del>{{ $item->price }}</del>
                  @endif
                </td>
                <td>{{$item->total}}</td>
                @if($order->is_returned)
                <td>{{ $item->return_qty > 0 ? $item->return_qty : '-' }}</td>
                <td>{{ $item->return_qty > 0 ? number_format($item->return_amount, 2) : '-' }}</td>
                @endif
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>

      <div class="row">
        <div class="col-6">
          <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
            @if(readConfig('is_show_note_invoice')){{ readConfig('note_to_customer_invoice') }}@endif
          </p>
        </div>
        <div class="col-6">
          <div class="table-responsive">
            <table class="table">
              <tr>
                <th style="width:50%">Subtotal:</th>
                <td class="text-right">{{currency()->symbol.' '.number_format($order->sub_total,2,'.',',')}}</td>
              </tr>
              <tr>
                <th>Discount:</th>
                <td class="text-right">{{currency()->symbol.' '.number_format($order->discount,2,'.',',')}}</td>
              </tr>
              <tr>
                <th>Total:</th>
                <td class="text-right">{{currency()->symbol.' '.number_format($order->total,2,'.',',')}}</td>
              </tr>
              @if($order->is_returned)
              <tr class="text-danger">
                <th>Return Amount:</th>
                <td class="text-right">{{currency()->symbol.' '.number_format($order->products->sum('return_amount'),2,'.',',')}}</td>
              </tr>
              @endif
              <tr>
                <th>Paid:</th>
                <td class="text-right">{{currency()->symbol.' '.number_format($order->paid,2,'.',',')}}</td>
              </tr>
              <tr>
                <th>Due:</th>
                <td class="text-right">{{currency()->symbol.' '.number_format($order->due,2,'.',',')}}</td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      <div class="row no-print">
        <div class="col-12">
          <a href="{{ route('backend.admin.orders.return', $order->id) }}" class="btn btn-warning float-left"><i class="fas fa-undo"></i> Process Return</a>
          <button type="button" onclick="window.print()" class="btn btn-success float-right"><i class="fas fa-print"></i> Print</button>
        </div>
      </div>
    </section>
  </div>
</div>
@endsection

@push('style')
<style>
  .invoice { border: none !important; }
</style>
@endpush
@push('script')
<script>
  window.addEventListener("load", window.print());
</script>
@endpush
