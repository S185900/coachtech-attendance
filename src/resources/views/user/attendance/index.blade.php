@extends('user.layouts.user-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css')}}">
@endsection

<!-- 出勤登録画面（一般ユーザー） http://localhost/attendance-->
@section('content')
<div class="attendance-container">
    <div class="attendance-card">
        <div class="attendance-status">
            {{-- 状態に応じたバッジの表示 --}}
            @if(!$attendance)
                <span class="status-badge status-out">勤務外</span>
            @elseif($attendance->status == 1)
                <span class="status-badge status-working">出勤中</span>
            @elseif($attendance->status == 2)
                <span class="status-badge status-break">休憩中</span>
            @elseif($attendance->status == 0)
                <span class="status-badge status-done">退勤済</span>
            @endif
        </div>

        <p class="attendance-date">{{ date('Y年m月d日') }} ({{ ['日', '月', '火', '水', '木', '金', '土'][date('w')] }})</p>

        <h1 class="attendance-time" id="realtime">{{ date('H:i') }}</h1>

        <div class="attendance-controls">
            @if(!$attendance)
                {{-- 勤務外：出勤ボタンのみ表示 --}}
                <form action="{{ route('attendance.start') }}" method="POST">
                    @csrf
                    <button type="submit" class="attendance-button">出勤</button>
                </form>

            @elseif($attendance->status == 1)
                {{-- 出勤中：退勤と休憩入を横並びで表示 --}}
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

            @elseif($attendance->status == 2)
                {{-- 休憩中：休憩戻のみ表示 --}}
                <form action="{{ route('attendance.rest-end') }}" method="POST">
                    @csrf
                    <button type="submit" class="attendance-button btn-white">休憩戻</button>
                </form>

            @elseif($attendance->status == 0)
                {{-- 退勤後：メッセージ表示 --}}
                <p class="thanks-message">お疲れ様でした。</p>
            @endif
        </div>
    </div>
</div>
@endsection