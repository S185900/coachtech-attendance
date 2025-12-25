@extends('admin.layouts.admin-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-list.css')}}">
@endsection

<!-- 勤怠一覧画面（管理者） http://localhost/admin/attendance/list -->
@section('content')
<div class="attendance-list-container">
    <h1 class="page-title">2023年6月1日の勤怠</h1>

    <div class="date-nav">
        <a href="#" class="date-nav-link">
            <img src="{{ asset('images/arrow-image.png') }}" alt="前日" class="arrow-icon prev">
            前日
        </a>
        <div class="current-date-display">
            <img src="{{ asset('images/calender-image.png') }}" alt="" class="calendar-icon">
            <span>2023/06/01</span>
        </div>
        <a href="#" class="date-nav-link">
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
            <tr>
                <td>山田 太郎</td>
                <td>09:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td>西 怜奈</td>
                <td>09:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td>中西 敦夫</td>
                <td>09:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
        </tbody>
    </table>

</div>
@endsection