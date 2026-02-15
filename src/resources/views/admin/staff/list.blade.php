@extends('admin.layouts.admin-header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-staff-list.css')}}">
@endsection

{{-- スタッフ一覧画面（管理者） --}}
@section('content')
    <div class="staff-list-container">
        <h1 class="page-title">スタッフ一覧</h1>

        <table class="staff-table">

            <thead>
                <tr class="staff-table-row">
                    <th class="staff-table-th">名前</th>
                    <th class="staff-table-th">メールアドレス</th>
                    <th class="staff-table-th">月次勤怠</th>
                </tr>
            </thead>

            <tbody>
                @foreach($users as $user)
                    <tr class="staff-table-row">
                        <td class="staff-table-td">
                            {{ $user->name }}
                        </td>
                        <td class="staff-table-td">
                            {{ $user->email }}
                        </td>
                        <td class="staff-table-td">
                            <a href="{{ route('admin.attendance.staff', ['id' => $user->id]) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>

    </div>
@endsection