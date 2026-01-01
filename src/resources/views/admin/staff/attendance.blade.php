@extends('admin.layouts.admin-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-staff-attendance.css')}}">
@endsection

<!-- スタッフ別勤怠一覧画面（管理者） http://localhost/admin/attendance/staff/{id} -->
@section('content')
<div class="attendance-list-container">
    <h1 class="page-title">{{ $user->name }}さんの勤怠</h1>

    {{-- 日付表示と前月・翌月リンク --}}
    <div class="date-nav">
        {{-- 前月ボタン --}}
        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}" class="date-nav-link">
            <img src="{{ asset('images/arrow-image.png') }}" alt="前月" class="arrow-icon prev">
            前月
        </a>

        {{-- 中央の日付表示 --}}
        <div class="current-date-display">
            <img src="{{ asset('images/calender-image.png') }}" alt="" class="calendar-icon">
            {{-- コントローラーで $currentMonth を Carbon インスタンスとして渡している場合 --}}
            <span>{{ $currentMonth->format('Y/m') }}</span>
        </div>

        {{-- 翌月ボタン --}}
        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}" class="date-nav-link">
            翌月
            <img src="{{ asset('images/arrow-image.png') }}" alt="翌月" class="arrow-icon next">
        </a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>
            @foreach($attendances as $attendance)
            <tr>
                {{-- $attendance->work_date を $attendance->date に修正 --}}
                <td>{{ $attendance->date->format('Y/m/d') }}</td>

                <td>{{ $attendance->start_time->format('H:i') }}</td>
                <td>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '' }}</td>

                {{-- 合計休憩時間 (アクセサ getTotalRestDurationAttribute) --}}
                <td>{{ $attendance->total_rest_duration }}</td> 

                {{-- 合計勤務時間 (アクセサ getWorkTimeAttribute かな？) --}}
                <td>{{ $attendance->work_time }}</td>

                <td>
                    <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>

        <!-- <tbody>
            <tr>
                <td>06/01(木)</td>
                <td>09:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td>06/01(木)</td>
                <td>09:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td>06/01(木)</td>
                <td>09:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
        </tbody> -->

    </table>

</div>
@endsection