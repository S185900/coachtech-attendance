<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Str;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 独自のLoginRequestを使用
        $this->app->singleton(
            \Laravel\Fortify\Http\Requests\LoginRequest::class,
            LoginRequest::class
        );

        Fortify::createUsersUsing(CreateNewUser::class);

        // ビューの切り替え
        Fortify::loginView(function () {
            if (request()->is('admin/*') || request()->is('admin')) {
                return view('admin.auth.login');
            }
            return view('user.auth.login');
        });

        Fortify::registerView(fn() => view('user.auth.register'));

        // メール認証誘導画面のビューを指定
        Fortify::verifyEmailView(function () {
            return view('user.auth.verify-email');
        });

        // 認証ロジック
        Fortify::authenticateUsing(function ($request) {
            $isAdmin = $request->is('admin/*') || $request->is('admin');
            $model = $isAdmin ? \App\Models\Master::class : \App\Models\User::class;
            $guard = $isAdmin ? 'admin' : 'web';

            $user = $model::where('email', $request->email)->first();

            // ↓ ここでパスワードの合致やユーザーの有無を判定
            if ($user && Hash::check($request->password, $user->password)) {
                // セッションにどちらの種別か保存（リダイレクト判定用）
                session(['login_type' => $isAdmin ? 'admin' : 'user']);

                // 管理者の場合は、WebガードではなくAdminガードを指定してログインさせる
                auth()->guard($guard)->login($user);

                return $user;
            }

            throw \Illuminate\Validation\ValidationException::withMessages([
                'auth_error' => 'ログイン情報が登録されていません',
            ]);
        });

        // 1. ログアウト応答のカスタマイズ (LogoutResponse)
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                // URLにadminが含まれるか、adminガードなら管理者ログインへ
                return ($request->is('admin/*') || $request->is('admin'))
                    ? redirect('/admin/login')
                    : redirect('/login');
            }
        });

        // 2. ログイン成功時の遷移先カスタマイズ (LoginResponse)
        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                // セッションの login_type が admin なら管理者画面へ
                if (session('login_type') === 'admin') {
                    return redirect()->intended('/admin/attendance/list');
                }
                return redirect()->intended('/attendance');
            }
        });

        // レートリミッター
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
            return Limit::perMinute(30)->by($throttleKey);
        });
    }
}