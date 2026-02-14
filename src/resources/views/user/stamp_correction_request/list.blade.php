@extends('user.layouts.user-header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user-stamp_correction_request-list.css')}}">
@endsection

{{-- 申請一覧画面（一般ユーザー） http://localhost/stamp_correction_request/list --}}
@section('content')
    <div class="stamp-correction-request-list-container">

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

        <table class="stamp-correction-request-table">
            <thead class="table-head">
                <tr class="table-row">
                    <th class="table-th">状態</th>
                    <th class="table-th">名前</th>
                    <th class="table-th">対象日時</th>
                    <th class="table-th">申請理由</th>
                    <th class="table-th">申請日時</th>
                    <th class="table-th">詳細</th>
                </tr>
            </thead>

            <tbody class="table-body">
                @forelse($requests as $request)

                    <tr class="table-row">
                        <td class="table-td">
                            {{ $request->status_label }}
                        </td>
                        <td class="table-td">
                            {{ $request->user->name }}
                        </td>
                        <td class="table-td">
                            {{ $request->attendance->date->format('Y/m/d') }}
                        </td>
                        <td class="table-td">
                            {{ Str::limit($request->reason, 20) }}
                        </td>
                        <td class="table-td">
                            {{ $request->created_at->format('Y/m/d') }}
                        </td>
                        <td class="table-td">
                            <a href="{{ route('attendance.detail', ['id' => $request->attendance_id]) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>

                @empty

                    <tr class="table-row">
                        <td class="empty-message" colspan="6">
                            現在、{{ $tab === 'pending' ? '承認待ち' : '承認済み' }}の申請はありません。
                        </td>
                    </tr>

                @endforelse
            </tbody>
        </table>

    </div>
@endsection