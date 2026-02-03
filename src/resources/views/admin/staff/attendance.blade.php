@extends('admin.layouts.admin-header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-staff-attendance.css')}}">
@endsection

{{-- スタッフ別勤怠一覧画面（管理者） http://localhost/admin/attendance/staff/{id} --}}
@section('content')
    <div class="attendance-list-container">
        <h1 class="page-title">{{ $user->name }}さんの勤怠</h1>

        {{-- 日付ナビゲーション --}}
        <nav class="date-nav">

            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}" class="date-nav-link">
                <img src="{{ asset('images/arrow-image.png') }}" alt="" class="arrow-icon prev">
                前月
            </a>

            <span class="current-date-display">
                <img src="{{ asset('images/calender-image.png') }}" alt="" class="calendar-icon">
                <span>{{ $currentMonth->format('Y/m') }}</span>
            </span>

            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}" class="date-nav-link">
                翌月
                <img src="{{ asset('images/arrow-image.png') }}" alt="" class="arrow-icon next">
            </a>

        </nav>

        <table class="attendance-table">
            <thead>
                <tr class="attendance-table-row">
                    <th class="attendance-table-th">日付</th>
                    <th class="attendance-table-th">出勤</th>
                    <th class="attendance-table-th">退勤</th>
                    <th class="attendance-table-th">休憩</th>
                    <th class="attendance-table-th">合計</th>
                    <th class="attendance-table-th">詳細</th>
                </tr>
            </thead>

            <tbody>
                @foreach($attendances as $attendance)
                    <tr class="attendance-table-row">
                        <td class="attendance-table-td">
                            {{ $attendance->date->isoFormat('MM/DD(ddd)') }}
                        </td>
                        <td class="attendance-table-td">
                            {{ $attendance->start_time->format('H:i') }}
                        </td>
                        <td class="attendance-table-td">
                            {{ $attendance->end_time ? $attendance->end_time->format('H:i') : '' }}
                        </td>
                        <td class="attendance-table-td">
                            {{ $attendance->display_total_rest_time !== '0:00' ? $attendance->display_total_rest_time : '' }}
                        </td>
                        <td class="attendance-table-td">
                            {{ $attendance->display_work_time !== '0:00' ? $attendance->display_work_time : '' }}
                        </td>
                        <td class="attendance-table-td">
                            <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="csv-export-container">
            <a href="{{ route('admin.attendance.staff.csv', ['id' => $user->id, 'month' => $currentMonth->format('Y-m')]) }}" class="csv-button">
                CSV出力
            </a>
        </div>

    </div>
@endsection