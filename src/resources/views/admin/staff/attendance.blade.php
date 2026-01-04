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
                {{-- 修正後：06/01(木) 形式 --}}
                <td>{{ \Carbon\Carbon::parse($attendance->date)->isoFormat('MM/DD(ddd)') }}</td>

                <td>{{ $attendance->start_time->format('H:i') }}</td>
                <td>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '' }}</td>

                {{-- 合計休憩時間 (アクセサ getTotalRestDurationAttribute) --}}
                {{-- 休憩時間が 00:00 なら空白にする場合（任意） --}}
                <td>{{ $attendance->total_rest_duration !== '00:00' ? $attendance->total_rest_duration : '' }}</td>

                {{-- 合計勤務時間 (アクセサ getWorkTimeAttribute かな？) --}}
                {{-- 合計勤務時間が 00:00 なら空白にする場合 --}}
                <td>{{ $attendance->work_time !== '00:00' ? $attendance->work_time : '' }}</td>

                <td>
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