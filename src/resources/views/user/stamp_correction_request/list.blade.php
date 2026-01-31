@extends('user.layouts.user-header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user-stamp_correction_request-list.blade.css')}}">
@endsection

{{-- 申請一覧画面（一般ユーザー） http://localhost/stamp_correction_request/list --}}
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
                @forelse($requests as $request)

                    <tr>
                        <td>
                            {{ $request->status === \App\Models\StampCorrectionRequest::STATUS_PENDING ? '承認待ち' : '承認済み' }}
                        </td>
                        <td>
                            {{ $request->user->name }}
                        </td>
                        <td>
                            {{ $request->attendance->date->format('Y/m/d') }}
                        </td>
                        <td>
                            {{ $request->reason }}
                        </td>
                        <td>
                            {{ $request->created_at->format('Y/m/d') }}
                        </td>
                        <td>
                            <a href="{{ route('attendance.detail', ['id' => $request->attendance_id]) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="empty-message">
                            現在、{{ $tab === 'pending' ? '承認待ち' : '承認済み' }}の申請はありません。
                        </td>
                    </tr>

                @endforelse
            </tbody>
        </table>

    </div>
@endsection