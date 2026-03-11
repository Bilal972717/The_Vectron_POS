@extends('backend.master')
@section('title', 'Add Staff Member')
@section('content')
<div class="card">
  <div class="card-header"><h4>Add Staff Member</h4></div>
  <div class="card-body">
    <form action="{{ route('backend.admin.hr.staff.store') }}" method="POST">
      @csrf
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Select User <span class="text-danger">*</span></label>
          <select name="user_id" class="form-control select2" required>
            <option value="">— Select User —</option>
            @foreach($users as $u)
              <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Employee Code</label>
          <input type="text" name="employee_code" class="form-control" value="{{ old('employee_code') }}" placeholder="e.g. EMP001">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Position / Designation</label>
          <input type="text" name="position" class="form-control" value="{{ old('position') }}" placeholder="e.g. Salesman">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Address</label>
          <input type="text" name="address" class="form-control" value="{{ old('address') }}">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Join Date</label>
          <input type="date" name="join_date" class="form-control" value="{{ old('join_date') }}">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Salary Type <span class="text-danger">*</span></label>
          <select name="salary_type" class="form-control" id="salary_type" required>
            <option value="fixed" {{ old('salary_type') == 'fixed' ? 'selected' : '' }}>Fixed Monthly</option>
            <option value="base_plus_commission" {{ old('salary_type') == 'base_plus_commission' ? 'selected' : '' }}>Base + Commission</option>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Base Salary <span class="text-danger">*</span></label>
          <input type="number" name="base_salary" class="form-control" min="0" step="0.01" value="{{ old('base_salary', 0) }}" required>
        </div>
        <div id="commission_fields" class="col-12 row" style="{{ old('salary_type') == 'base_plus_commission' ? '' : 'display:none' }}">
          <div class="col-md-6 mb-3">
            <label class="form-label">Packing Commission Rate (%)</label>
            <input type="number" name="packing_commission_rate" class="form-control" min="0" max="100" step="0.01" value="{{ old('packing_commission_rate', 0) }}">
            <small class="text-muted">% of each order total when this staff packs the order</small>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Delivery Commission Rate (%)</label>
            <input type="number" name="delivery_commission_rate" class="form-control" min="0" max="100" step="0.01" value="{{ old('delivery_commission_rate', 0) }}">
            <small class="text-muted">% of each order total when this staff delivers the order</small>
          </div>
        </div>
      </div>
      <a href="{{ route('backend.admin.hr.staff.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
    </form>
  </div>
</div>
@endsection
@push('script')
<script>
$('#salary_type').on('change', function() {
  $('#commission_fields').toggle($(this).val() === 'base_plus_commission');
});
</script>
@endpush
