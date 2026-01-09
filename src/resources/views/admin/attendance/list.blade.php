@extends('admin.layouts.admin-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-list.css')}}">
@endsection

<!-- 勤怠一覧画面（管理者） http://localhost/admin/attendance/list -->
@section('content')
<div class="attendance-list-container">
    <h1 class="page-title">{{ $currentDate->format('Y年n月j日') }}の勤怠</h1>

    <div class="date-nav">
        {{-- 前日へのリンク --}}
        <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="date-nav-link">
            <img src="{{ asset('images/arrow-image.png') }}" alt="前日" class="arrow-icon prev">
            前日
        </a>

        {{-- 中央の日付表示 --}}
        <div class="current-date-display">
            <img src="{{ asset('images/calender-image.png') }}" alt="" class="calendar-icon">
            <span>{{ $currentDate->format('Y/m/d') }}</span>
        </div>

        {{-- 翌日へのリンク --}}
        <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="date-nav-link">
            翌日
            <img src="{{ asset('images/arrow-image.png') }}" alt="翌日" class="arrow-icon next">
        </a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
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
                <td>{{ $attendance->user->name }}</td>
                <td>{{ $attendance->start_time->format('H:i') }}</td>
                <td>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '' }}</td>
                {{-- 合計休憩時間を計算（モデルにアクセサを作っておくと便利です） --}}
                <td>{{ $attendance->total_rest_time }}</td> 
                {{-- 勤務合計時間を計算 --}}
                <td>{{ $attendance->work_time }}</td>
                <td>
                    {{-- 管理者用の詳細画面（PG10）へのリンク --}}
                    <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection