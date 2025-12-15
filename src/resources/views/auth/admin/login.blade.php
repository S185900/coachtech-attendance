@extends('layouts.guest')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css')}}">
@endsection

<!-- 管理者ログイン画面 -->
@section('content')
<h2 class="section-title">管理者ログイン</h2>
<section class="login">
    <!-- <form-->

        <div class="login-item">
            <label for="email" class="login-label">メールアドレス</label>
            <input id="email" type="email" class="input-form" name="email" value="{{ old('email') }}" required autocomplete="email">
            <!-- <input id="email" type="email" class="login-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email"> -->
        </div>

        <div class="login-item">
            <label for="password" class="login-label">パスワード</label>
            <input id="password" type="password" class="input-form" name="password" required autocomplete="password">
            <!-- <input id="password" type="password" class="login-input @error('password') is-invalid @enderror" name="password" required autocomplete="new-password"> -->
        </div>

        <div class="login-item">
            <button class="login-button" type="submit">
                管理者ログインする
            </button>
        </div>
    <!-- </form> -->

    <nav class="register-nav">
        <a class="register-link" href="#">会員登録はこちら</a>
        <!-- <a class="register-link" href="">会員登録はこちら</a> -->
    </nav>
</section>
@endsection