@extends('user.layouts.user-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css')}}">
@endsection

<!-- 出勤登録画面（一般ユーザー） http://localhost/attendance-->
@section('content')
<div class="attendance-container">
    <div class="attendance-card">
        <div class="attendance-status">
            <span class="status-badge">勤務外</span>
        </div>

        <p class="attendance-date">2023年6月1日(木)</p>

        <h1 class="attendance-time">08:00</h1>

        <div class="attendance-controls">
            <!-- <form action="#" method="POST">
                @csrf -->
                <button type="submit" class="attendance-button">出勤</button>
            <!-- </form> -->
        </div>
    </div>
</div>
@endsection