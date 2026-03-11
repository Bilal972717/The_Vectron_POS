@extends('backend.master')
@section('title', 'Staff Management')
@section('content')
<div class="card">
  <div class="mt-n5 mb-3 d-flex justify-content-end">
    @can('staff_create')
    <a href="{{ route('backend.admin.hr.staff.create') }}" class="btn bg-gradient-primary"><i class="fas fa-plus-circle"></i> Add Staff</a>
    @endcan
  </div>
  <div class="card-body p-2 p-md-4 pt-0">
    <table id="datatables" class="table table-hover">
      <thead>
        <tr>
          <th>#</th><th>Name</th><th>Code</th><th>Position</th>
          <th>Salary Type</th><th>Base Salary</th><th>Status</th><th>Action</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection
@push('script')
<script>
$(function() {
  $('#datatables').DataTable({
    processing: true, serverSide: true,
    ajax: { url: '{{ route("backend.admin.hr.staff.index") }}', type: 'GET' },
    columns: [
      {data:'DT_RowIndex',name:'DT_RowIndex',orderable:false,searchable:false},
      {data:'name',name:'name'},
      {data:'employee_code',name:'employee_code'},
      {data:'position',name:'position'},
      {data:'salary_type',name:'salary_type'},
      {data:'base_salary',name:'base_salary'},
      {data:'status',name:'status'},
      {data:'action',name:'action',orderable:false,searchable:false},
    ]
  });
});
</script>
@endpush
