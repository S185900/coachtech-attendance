@extends('user.layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css')}}">
@endsection

<!-- 一般ユーザー会員登録画面 http://localhost/register-->
@section('content')
<h1 class="section-title">会員登録</h1>
<section class="register-section">
    <!-- <form-->

        <div class="register-item">
            <label for="name" class="register-label">名前</label>
            <input id="name" type="name" class="input-form" name="name" value="{{ old('name') }}" required autocomplete="name">
            <!-- <input id="email" type="email" class="login-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email"> -->
        </div>

        <div class="register-item">
            <label for="email" class="register-label">メールアドレス</label>
            <input id="email" type="email" class="input-form" name="email" value="{{ old('email') }}" required autocomplete="email">
            <!-- <input id="email" type="email" class="login-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email"> -->
        </div>

        <div class="register-item">
            <label for="password" class="register-label">パスワード</label>
            <input id="password" type="password" class="input-form" name="password" required autocomplete="password">
            <!-- <input id="password" type="password" class="login-input @error('password') is-invalid @enderror" name="password" required autocomplete="new-password"> -->
        </div>

        <div class="register-item">
            <label for="password_confirmation" class="register-label">パスワード確認</label>
            <input id="password_confirmation" type="password_confirmation" class="input-form" name="password_confirmation" required autocomplete="password_confirmation">
            <!-- <input id="password" type="password" class="login-input @error('password') is-invalid @enderror" name="password" required autocomplete="new-password"> -->
        </div>

        <div class="register-item">
            <button class="register-button" type="submit">
                登録する
            </button>
        </div>
    <!-- </form> -->

    <nav class="login-nav">
        <a class="login-link" href="{{ route('login') }}">ログインはこちら</a>
    </nav>
</section>
@endsection