@extends('user.layouts.user-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user-stamp_correction_request-list.blade.css')}}">
@endsection

<!-- 申請一覧画面（一般ユーザー） http://localhost/stamp_correction_request/list-->
@section('content')
<div class="stamp_correction_request-list-container">
    <h1 class="page-title">申請一覧</h1>

    {{-- タブメニュー部分 --}}
    <div class="tabs">
        <a href="{{ route('stamp_correction_request.list', ['tab' => 'pending']) }}" 
        class="tab-item {{ $tab === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('stamp_correction_request.list', ['tab' => 'approved']) }}" 
        class="tab-item {{ $tab === 'approved' ? 'active' : '' }}">
            承認済み
        </a>
    </div>

    <table class="stamp_correction_request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>

        <tbody>
            @foreach($requests as $request)
            <tr>
                <td>{{ $request->status === 0 ? '承認待ち' : '承認済み' }}</td>
                <td>{{ auth()->user()->name }}</td>
                <td>{{ $request->attendance->date->format('Y/m/d') }}</td>
                <td>{{ $request->reason }}</td>
                <td>{{ $request->created_at->format('Y/m/d') }}</td>
                <td>
                    {{-- 勤怠詳細画面(PG05)へ遷移 --}}
                    <a href="{{ route('attendance.detail', ['attendance_id' => $request->attendance_id]) }}" class="detail-link">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>

        <!-- <tbody>
            <tr>
                <td>承認待ち</td>
                <td>テスト太郎</td>
                <td>2023/06/01</td>
                <td>遅延のため</td>
                <td>2023/06/02</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td>承認待ち</td>
                <td>テスト太郎</td>
                <td>2023/06/01</td>
                <td>遅延のため</td>
                <td>2023/06/02</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td>承認待ち</td>
                <td>テスト太郎</td>
                <td>2023/06/01</td>
                <td>遅延のため</td>
                <td>2023/06/02</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
        </tbody> -->

    </table>

</div>
@endsection