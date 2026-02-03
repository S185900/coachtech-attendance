@extends('admin.layouts.admin-header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance-list.css')}}">
@endsection

{{-- 勤怠一覧画面（管理者） http://localhost/admin/attendance/list --}}
@section('content')
    <div class="attendance-list-container">
        <h1 class="page-title">{{ $currentDate->format('Y年n月j日') }}の勤怠</h1>

        {{-- 日付ナビゲーション --}}
        <nav class="date-nav">

            <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="date-nav-link">
                <img src="{{ asset('images/arrow-image.png') }}" alt="" class="arrow-icon prev">
                前日
            </a>

            <span class="current-date-display">
                <img src="{{ asset('images/calender-image.png') }}" alt="" class="calendar-icon">
                <span>{{ $currentDate->format('Y/m/d') }}</span>
            </span>

            <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="date-nav-link">
                翌日
                <img src="{{ asset('images/arrow-image.png') }}" alt="" class="arrow-icon next">
            </a>

        </nav>

        <table class="attendance-table">

            <thead class="attendance-table-head">
                <tr class="attendance-table-row">
                    <th class="attendance-table-th">名前</th>
                    <th class="attendance-table-th">出勤</th>
                    <th class="attendance-table-th">退勤</th>
                    <th class="attendance-table-th">休憩</th>
                    <th class="attendance-table-th">合計</th>
                    <th class="attendance-table-th">詳細</th>
                </tr>
            </thead>

            <tbody class="attendance-table-body">

                @foreach($attendances as $attendance)
                    <tr class="attendance-table-row">
                        <td class="attendance-table-td">
                            {{ $attendance->user->name }}
                        </td>
                        <td class="attendance-table-td">
                            {{ $attendance->start_time->format('H:i') }}
                        </td>
                        <td class="attendance-table-td">
                            {{ $attendance->end_time ? $attendance->end_time->format('H:i') : '' }}
                        </td>
                        <td class="attendance-table-td">
                            {{ $attendance->total_rest_time }}
                        </td>
                        <td class="attendance-table-td">
                            {{ $attendance->work_time }}
                        </td>
                        <td class="attendance-table-td">
                            <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                @endforeach

            </tbody>

        </table>

    </div>
@endsection