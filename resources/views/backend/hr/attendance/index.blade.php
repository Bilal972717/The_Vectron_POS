@extends('backend.master')
@section('title', 'Attendance Sheet')
@section('content')
<div class="card">
  <div class="card-header">
    <h4>Monthly Attendance Sheet</h4>
  </div>
  <div class="card-body">
    <form method="GET" class="row mb-4">
      <div class="col-md-3">
        <label>Month</label>
        <input type="month" name="month" class="form-control" value="{{ $month }}">
      </div>
      <div class="col-md-3">
        <label>Filter Staff</label>
        <select name="user_id" class="form-control">
          <option value="">All Staff</option>
          @foreach($allStaff as $s)
            <option value="{{ $s->id }}" {{ $userId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary btn-block">View</button>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <a href="{{ route('backend.admin.hr.attendance.daily') }}" class="btn btn-success btn-block">Mark Today</a>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="button" onclick="window.print()" class="btn btn-secondary btn-block no-print">Print</button>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-bordered table-sm" style="font-size:12px;">
        <thead class="thead-dark">
          <tr>
            <th>Staff</th>
            @foreach($days as $day)
              <th class="text-center {{ $day->isWeekend() ? 'bg-secondary text-white' : '' }}" title="{{ $day->format('D') }}">
                {{ $day->format('d') }}
              </th>
            @endforeach
            <th>P</th><th>A</th><th>H</th><th>L</th>
          </tr>
        </thead>
        <tbody>
          @foreach($staff as $user)
          @php
            $attMap = $user->attendances->keyBy(fn($a) => $a->date->format('Y-m-d'));
            $p = $user->attendances->where('status','present')->count();
            $a = $user->attendances->where('status','absent')->count();
            $h = $user->attendances->where('status','half_day')->count();
            $l = $user->attendances->where('status','leave')->count();
          @endphp
          <tr>
            <td><strong>{{ $user->name }}</strong><br><small>{{ optional($user->staffProfile)->position }}</small></td>
            @foreach($days as $day)
              @php
                $rec = $attMap[$day->format('Y-m-d')] ?? null;
                $statusMap = ['present'=>'P','absent'=>'A','half_day'=>'H','leave'=>'L'];
                $colorMap = ['present'=>'success','absent'=>'danger','half_day'=>'warning','leave'=>'info'];
                $s = $rec ? $rec->status : null;
              @endphp
              <td class="text-center {{ $day->isWeekend() ? 'bg-light' : '' }}">
                @if($s)
                  <span class="badge badge-{{ $colorMap[$s] }}" title="{{ $rec->note }}">{{ $statusMap[$s] }}</span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
            @endforeach
            <td class="text-success font-weight-bold">{{ $p }}</td>
            <td class="text-danger font-weight-bold">{{ $a }}</td>
            <td class="text-warning font-weight-bold">{{ $h }}</td>
            <td class="text-info font-weight-bold">{{ $l }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <small class="text-muted">P=Present, A=Absent, H=Half Day, L=Leave. Grey columns = weekends.</small>
  </div>
</div>
@endsection
@push('style')
<style>@media print { .no-print { display:none !important; } }</style>
@endpush
