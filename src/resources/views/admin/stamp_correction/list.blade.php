@extends('admin.layouts.admin-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-stamp_correction_request-list.css')}}">
@endsection

<!-- 申請一覧画面（管理者） http://localhost/stamp_correction_request/list-->
@section('content')
<div class="stamp_correction_request-list-container">
    <h1 class="page-title">申請一覧</h1>

    {{-- タブメニュー部分 --}}
    <div class="tabs">
        <a href="{{ route('admin.stamp_correction.list', ['tab' => 'pending']) }}" class="tab-item {{ $tab === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('admin.stamp_correction.list', ['tab' => 'approved']) }}"  class="tab-item {{ $tab === 'approved' ? 'active' : '' }}">
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
                <td>
                    <span class="status-badge {{ $request->status === 1 ? 'approved' : 'pending' }}">
                        {{ $request->status === 1 ? '承認済み' : '承認待ち' }}
                    </span>
                </td>
                <td>{{ $request->user->name }}</td>
                <td>{{ $request->attendance->date->format('Y/m/d') }}</td>
                <td class="reason-cell">{{ Str::limit($request->reason, 20) }}</td>
                <td>{{ $request->created_at->format('Y/m/d') }}</td>
                <td>
                    {{-- PG13: 承認画面へのリンク --}}
                    <a href="{{ route('admin.stamp_correction.approve', ['attendance_correct_request_id' => $request->id]) }}" class="detail-link">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>

    </table>

</div>
@endsection