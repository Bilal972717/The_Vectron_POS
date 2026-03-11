@extends('backend.master')
@section('title', 'Edit Staff: '.$user->name)
@section('content')
<div class="card">
  <div class="card-header"><h4>Edit Staff: {{ $user->name }}</h4></div>
  <div class="card-body">
    <form action="{{ route('backend.admin.hr.staff.update', $user->id) }}" method="POST">
      @csrf @method('PUT')
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">User</label>
          <input type="text" class="form-control" value="{{ $user->name }} ({{ $user->email }})" disabled>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Employee Code</label>
          <input type="text" name="employee_code" class="form-control" value="{{ old('employee_code', $profile->employee_code) }}">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Position</label>
          <input type="text" name="position" class="form-control" value="{{ old('position', $profile->position) }}">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" value="{{ old('phone', $profile->phone) }}">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Address</label>
          <input type="text" name="address" class="form-control" value="{{ old('address', $profile->address) }}">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Join Date</label>
          <input type="date" name="join_date" class="form-control" value="{{ old('join_date', $profile->join_date?->format('Y-m-d')) }}">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Salary Type <span class="text-danger">*</span></label>
          <select name="salary_type" class="form-control" id="salary_type" required>
            <option value="fixed" {{ old('salary_type', $profile->salary_type) == 'fixed' ? 'selected' : '' }}>Fixed Monthly</option>
            <option value="base_plus_commission" {{ old('salary_type', $profile->salary_type) == 'base_plus_commission' ? 'selected' : '' }}>Base + Commission</option>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Base Salary <span class="text-danger">*</span></label>
          <input type="number" name="base_salary" class="form-control" min="0" step="0.01" value="{{ old('base_salary', $profile->base_salary) }}" required>
        </div>
        <div id="commission_fields" class="col-12 row" style="{{ old('salary_type', $profile->salary_type) == 'base_plus_commission' ? '' : 'display:none' }}">
          <div class="col-md-6 mb-3">
            <label class="form-label">Packing Commission Rate (%)</label>
            <input type="number" name="packing_commission_rate" class="form-control" min="0" max="100" step="0.01" value="{{ old('packing_commission_rate', $profile->packing_commission_rate) }}">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Delivery Commission Rate (%)</label>
            <input type="number" name="delivery_commission_rate" class="form-control" min="0" max="100" step="0.01" value="{{ old('delivery_commission_rate', $profile->delivery_commission_rate) }}">
          </div>
        </div>
        <div class="col-md-6 mb-3">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $profile->is_active) ? 'checked' : '' }}>
            <label class="custom-control-label" for="is_active">Active</label>
          </div>
        </div>
      </div>
      <a href="{{ route('backend.admin.hr.staff.index') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
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
