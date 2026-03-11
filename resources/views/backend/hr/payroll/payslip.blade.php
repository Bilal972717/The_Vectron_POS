@extends('backend.master')
@section('title', 'Payslip')
@section('content')
<div class="card" style="max-width:600px;margin:auto;">
  <div class="card-body">
    <div class="text-center mb-3">
      @if(readConfig('is_show_logo_invoice'))<img src="{{ assetImage(readconfig('site_logo')) }}" height="50" alt="Logo"><br>@endif
      <h4>{{ readConfig('site_name') }}</h4>
      <small>{{ readConfig('contact_address') }}</small>
      <h5 class="mt-2">SALARY PAYSLIP</h5>
      <p>{{ \Carbon\Carbon::parse($run->month)->format('F Y') }}</p>
    </div>
    <hr>
    <div class="row mb-3">
      <div class="col-6">
        <strong>Employee:</strong> {{ $run->user->name }}<br>
        <strong>Code:</strong> {{ optional($run->user->staffProfile)->employee_code ?? '-' }}<br>
        <strong>Position:</strong> {{ optional($run->user->staffProfile)->position ?? '-' }}
      </div>
      <div class="col-6 text-right">
        <strong>Status:</strong> <span class="badge badge-{{ $run->status == 'paid' ? 'success' : 'warning' }}">{{ ucfirst($run->status) }}</span><br>
        @if($run->paid_date)<strong>Paid On:</strong> {{ $run->paid_date->format('d M Y') }}<br>@endif
      </div>
    </div>
    <hr>
    <table class="table table-borderless">
      <tr><td>Working Days</td><td class="text-right">{{ $run->working_days }}</td></tr>
      <tr><td>Present Days</td><td class="text-right">{{ $run->present_days }}</td></tr>
      <tr><td>Half Days</td><td class="text-right">{{ $run->half_days }}</td></tr>
      <tr class="table-light"><td><strong>Earned Base Salary</strong></td><td class="text-right"><strong>{{ number_format($run->base_salary, 2) }}</strong></td></tr>
      @if($run->packing_commission > 0)
      <tr><td>Packing Commission</td><td class="text-right">{{ number_format($run->packing_commission, 2) }}</td></tr>
      @endif
      @if($run->delivery_commission > 0)
      <tr><td>Delivery Commission</td><td class="text-right">{{ number_format($run->delivery_commission, 2) }}</td></tr>
      @endif
      @if($run->bonus > 0)
      <tr class="text-success"><td>Bonus</td><td class="text-right">+ {{ number_format($run->bonus, 2) }}</td></tr>
      @endif
      @if($run->deductions > 0)
      <tr class="text-danger"><td>Deductions</td><td class="text-right">- {{ number_format($run->deductions, 2) }}</td></tr>
      @endif
    </table>
    <hr>
    <div class="d-flex justify-content-between">
      <h5>Net Salary</h5>
      <h5><strong>{{ currency()->symbol ?? '' }} {{ number_format($run->net_salary, 2) }}</strong></h5>
    </div>
    @if($run->note)<p class="text-muted"><small>Note: {{ $run->note }}</small></p>@endif
    <hr>
    <div class="text-center no-print mt-3">
      <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
    </div>
  </div>
</div>
@endsection
@push('style')
<style>@media print { .no-print { display:none !important; } }</style>
@endpush
@push('script')
<script>window.print();</script>
@endpush
