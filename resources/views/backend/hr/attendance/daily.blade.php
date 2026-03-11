@extends('backend.master')
@section('title', 'Daily Attendance')
@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4>Daily Attendance</h4>
    <form method="GET" class="d-flex gap-2">
      <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
    </form>
  </div>
  <div class="card-body">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <form action="{{ route('backend.admin.hr.attendance.mark-day') }}" method="POST">
      @csrf
      <input type="hidden" name="date" value="{{ $date }}">
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Position</th>
            <th>Status</th>
            <th>Note</th>
          </tr>
        </thead>
        <tbody>
          @forelse($staff as $index => $user)
          @php $att = $user->attendances->first(); @endphp
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ optional($user->staffProfile)->position ?? '-' }}</td>
            <td>
              <input type="hidden" name="attendance[{{ $index }}][user_id]" value="{{ $user->id }}">
              <select name="attendance[{{ $index }}][status]" class="form-control form-control-sm" style="width:130px">
                @foreach(['present' => 'Present', 'absent' => 'Absent', 'half_day' => 'Half Day', 'leave' => 'Leave'] as $val => $label)
                  <option value="{{ $val }}" {{ optional($att)->status == $val ? 'selected' : ($val == 'present' && !$att ? 'selected' : '') }}>{{ $label }}</option>
                @endforeach
              </select>
            </td>
            <td>
              <input type="text" name="attendance[{{ $index }}][note]" class="form-control form-control-sm" placeholder="Note..." value="{{ optional($att)->note }}">
            </td>
          </tr>
          @empty
          <tr><td colspan="5" class="text-center">No active staff found.</td></tr>
          @endforelse
        </tbody>
      </table>
      @if($staff->count() > 0)
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Attendance</button>
      @endif
    </form>
  </div>
</div>
@endsection
