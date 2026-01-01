@extends('admin.layouts.admin-header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-staff-list.css')}}">
@endsection

<!-- スタッフ一覧画面（管理者） http://localhost/admin/admin/staff/list -->
@section('content')
<div class="staff-list-container">
    <h1 class="page-title">スタッフ一覧</h1>

    <table class="staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>

        <tbody>
            @foreach($users as $user)
            <tr>
                <td class="user-name">{{ $user->name }}</td>
                <td class="user-email">{{ $user->email }}</td>
                <td>
                    {{-- リンク先は各スタッフの勤怠一覧画面 --}}
                    <a href="{{ route('admin.attendance.staff', ['id' => $user->id]) }}" class="detail-link">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>


        <!-- <tbody>
            <tr>
                <td>山田 太郎</td>
                <td>test@mail.com</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td>山田 太郎</td>
                <td>test@mail.com</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
            <tr>
                <td>山田 太郎</td>
                <td>test@mail.com</td>
                <td><a href="#" class="detail-link">詳細</a></td>
            </tr>
        </tbody> -->
    </table>

</div>
@endsection