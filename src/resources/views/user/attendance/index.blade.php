@extends('user.layouts.user-header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user-index.css')}}">
@endsection

{{-- 出勤登録画面（一般ユーザー） http://localhost/attendance --}}
@section('content')
    <div class="attendance-container">

        <div class="attendance-card">

            <div class="attendance-status">
                @if(!$attendance)
                    <span class="status-badge status-out">勤務外</span>
                @elseif($attendance->status == \App\Models\Attendance::STATUS_WORKING)
                    <span class="status-badge status-working">出勤中</span>
                @elseif($attendance->status == \App\Models\Attendance::STATUS_RESTING)
                    <span class="status-badge status-break">休憩中</span>
                @elseif($attendance->status == \App\Models\Attendance::STATUS_FINISHED)
                    <span class="status-badge status-done">退勤済</span>
                @endif
            </div>

            <p class="attendance-date">
                {{ \Carbon\Carbon::now()->isoFormat('YYYY年M月D日(ddd)') }}
            </p>

            <h1 class="attendance-time js-realtime-clock">
                {{ date('H:i') }}
            </h1>

            <div class="attendance-controls">

                @if(!$attendance)

                    {{-- 未出勤（勤務外） --}}
                    <form action="{{ route('attendance.start') }}" method="POST">
                        @csrf
                        <button type="submit" class="attendance-button">出勤</button>
                    </form>

                @elseif($attendance->status == \App\Models\Attendance::STATUS_WORKING)

                    {{-- 出勤中 --}}
                    <div class="button-group">
                        <form action="{{ route('attendance.end') }}" method="POST">
                            @csrf
                            <button type="submit" class="attendance-button btn-black">退勤</button>
                        </form>
                        <form action="{{ route('attendance.rest-start') }}" method="POST">
                            @csrf
                            <button type="submit" class="attendance-button btn-white">休憩入</button>
                        </form>
                    </div>

                @elseif($attendance->status == \App\Models\Attendance::STATUS_RESTING)

                    {{-- 休憩中 --}}
                    <form action="{{ route('attendance.rest-end') }}" method="POST">
                        @csrf
                        <button type="submit" class="attendance-button btn-white">休憩戻</button>
                    </form>

                @elseif($attendance->status == \App\Models\Attendance::STATUS_FINISHED)

                    {{-- 退勤済 --}}
                    <p class="thanks-message">お疲れ様でした。</p>

                @endif

            </div>
        </div>
    </div>

    {{-- リアルタイム時計用 --}}
    <script>
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const target = document.querySelector('.js-realtime-clock');
            if (target) {
                target.textContent = `${hours}:${minutes}`;
            }
        }

        setInterval(updateTime, 1000);
        updateTime();

    </script>
@endsection