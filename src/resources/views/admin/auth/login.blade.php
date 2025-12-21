@extends('admin.layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css')}}">
@endsection

<!-- 管理者ログイン画面 http://localhost/admin/login-->
@section('content')
<h1 class="section-title">管理者ログイン</h1>
<section class="login-section">
    <form action="{{ route('admin.login') }}" method="POST" novalidate>
        @csrf

        <div class="login-item">
            <label for="email" class="login-label">メールアドレス</label>
            <input id="email" type="email" class="input-form" name="email" value="{{ old('email') }}" required autocomplete="email">

            @error('email')
                <span class="form-error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="login-item">
            <label for="password" class="login-label">パスワード</label>
            <input id="password" type="password" class="input-form" name="password" required autocomplete="password">

            @error('password')
                <span class="form-error-message">{{ $message }}</span>
            @enderror
            @error('auth_error')
                <span class="auth-error-message">{{ $message }}</span>
            @enderror
        </div>

        <div class="login-item">
            <button class="login-button" type="submit">
                管理者ログインする
            </button>
        </div>
    </form>

</section>
@endsection