@extends('user.layouts.user-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user-attendance-list.css')}}">
@endsection

<!-- 勤怠一覧画面（一般ユーザー） http://localhost/attendance/list -->
@section('content')
<div class="attendance-list-container">
    <h1 class="page-title">勤怠一覧</h1>

    <div class="date-nav">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="date-nav-link">
            <img src="{{ asset('images/arrow-image.png') }}" alt="前月" class="arrow-icon prev">
            前月
        </a>
        <div class="current-date-display">
            <img src="{{ asset('images/calender-image.png') }}" alt="" class="calendar-icon">
            <span>{{ $displayMonth }}</span>
        </div>
        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="date-nav-link">
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
            @php
                // コントローラーから渡された開始日〜終了日の期間をループ
                $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
            @endphp

            @foreach($period as $date)
                @php
                    $dateStr = $date->format('Y-m-d');
                    // その日の勤怠データがあれば取得（AttendanceControllerでkeyBy('date')している前提）
                    $attendance = $attendances->get($dateStr);
                @endphp
                <tr>
                    {{-- 日付と曜日 (例: 06/01(木)) --}}
                    <td>{{ $date->format('m/d') }}({{ $date->isoFormat('ddd') }})</td>

                    {{-- データがある場合のみ表示。ない場合は空文字 --}}
                    <td>{{ $attendance && $attendance->start_time ? $attendance->start_time->format('H:i') : '' }}</td>
                    <td>{{ $attendance && $attendance->end_time ? $attendance->end_time->format('H:i') : '' }}</td>

                    {{-- モデルに定義したアクセサを使用 --}}
                    <td>{{ $attendance ? $attendance->total_rest_time : '' }}</td>
                    <td>{{ $attendance ? $attendance->work_time : '' }}</td>

                    {{-- @if を使う場合（データがある日だけリンクを出す） --}}
                    <td>
                        @if($attendance)
                            <a href="{{ route('attendance.detail', ['attendance_id' => $attendance->id]) }}" class="detail-link">詳細</a>
                        @endif
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