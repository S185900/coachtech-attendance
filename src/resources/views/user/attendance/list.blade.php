@extends('user.layouts.user-header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user-attendance-list.css')}}">
@endsection

{{-- 勤怠一覧画面（一般ユーザー） http://localhost/attendance/list --}}
@section('content')
    <div class="attendance-list-container">
        <h1 class="page-title">勤怠一覧</h1>

        {{-- 月毎ナビゲーション部分 --}}
        <nav class="date-nav">

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

        </nav>

        <table class="attendance-table">

            <thead class="attendance-table-head">
                <tr class="attendance-table-row">
                    <th class="attendance-table-th">日付</th>
                    <th class="attendance-table-th">出勤</th>
                    <th class="attendance-table-th">退勤</th>
                    <th class="attendance-table-th">休憩</th>
                    <th class="attendance-table-th">合計</th>
                    <th class="attendance-table-th">詳細</th>
                </tr>
            </thead>

            <tbody class="attendance-table-body">

                @foreach($period as $date)
                    @php
                        $dateStr = $date->format('Y-m-d');
                        $attendance = $attendances->get($dateStr);
                    @endphp

                    <tr class="attendance-table-row">
                        <td class="attendance-table-td">
                            {{ $date->format('m/d') }}({{ $date->isoFormat('ddd') }})
                        </td>
                        <td class="attendance-table-td">
                            {{ $attendance && $attendance->start_time ? $attendance->start_time->format('H:i') : '' }}
                        </td>
                        <td class="attendance-table-td">
                            {{ $attendance && $attendance->end_time ? $attendance->end_time->format('H:i') : '' }}
                        </td>
                        <td class="attendance-table-td">
                            {{ $attendance && $attendance->display_total_rest_time !== '0:00' ? $attendance->display_total_rest_time : '' }}
                        </td>
                        <td class="attendance-table-td">
                            {{ $attendance && $attendance->display_work_time !== '0:00' ? $attendance->display_work_time : '' }}
                        </td>
                        <td class="attendance-table-td">
                            @if($attendance)
                                <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach

            </tbody>

        </table>

    </div>
@endsection