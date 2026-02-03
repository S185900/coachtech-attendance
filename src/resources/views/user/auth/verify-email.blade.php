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

        <nav class="verify-email-nav">
            <a class="verify-email-link" href="#"
                @if (App::environment(['local', 'testing']))
                    onclick="window.open('{{ config('mail.local_url') }}', '_blank'); return false;"
                @endif>
                認証はこちらから
            </a>
        </nav>

        <form class="resend-verification-form" method="POST" action="{{ route('verification.send') }}" novalidate>
            @csrf
            <button class="resend-verification-link" type="submit">認証メールを再送する</button>
        </form>

        @if (session('message'))
            <p class="send-message js-flash-message">認証メールを再送しました</p>

            <script>
                setTimeout(() => {
                    const msg = document.querySelector('.js-flash-message');
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