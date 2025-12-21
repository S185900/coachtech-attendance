@extends('user.layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css')}}">
@endsection

<!-- 一般ユーザーログイン画面 http://localhost/login-->
@section('content')
<h1 class="section-title">ログイン</h1>
<section class="login-section">
    <form action="{{ route('login') }}" method="POST" novalidate>
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
                ログインする
            </button>
        </div>
    </form>

    <nav class="register-nav">
        <a class="register-link" href="{{ route('register') }}">会員登録はこちら</a>
    </nav>
</section>
@endsection