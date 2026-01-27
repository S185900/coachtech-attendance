@extends('user.layouts.header')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user-verify-email.css')}}">
@endsection

{{-- メール認証誘導画面 --}}
@section('content')
    <div class="notice">
        <p class="notice-text">
            <span class="break-tablet">登録していただいたメールアドレスに</span>認証メールを送付しました。
            <br>
            メール認証を完了してください。
        </p>
    </div>

    <section class="verify-email">

        <nav class="verify-email__nav">
            <a class="verify-email__link" href="#"
                @if (App::environment(['local', 'testing']))
                    onclick="window.open('http://localhost:8025', '_blank'); return false;"
                @endif>
                認証はこちらから
            </a>
        </nav>

        <form class="resend-verification__form" method="POST" action="{{ route('verification.send') }}" novalidate>
            @csrf
            <button class="resend-verification__link" type="submit">認証メールを再送する</button>
        </form>

        @if (session('message'))
            <p id="js-flash-message" class="send-message">認証メールを再送しました</p>

            <script>
                setTimeout(() => {
                    const msg = document.getElementById('js-flash-message');
                    if (msg) {
                        msg.style.transition = 'opacity 1s';
                        msg.style.opacity = '0';
                        setTimeout(() => msg.remove(), 1000);
                    }
                }, 3000);
            </script>
        @endif

    </section>
@endsection