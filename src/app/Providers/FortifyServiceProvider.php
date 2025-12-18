<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LogoutResponse;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Str;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\RegisterRequest;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::createUsersUsing(CreateNewUser::class);

        // 一般ユーザー用ビュー
        Fortify::loginView(fn() => view('user.auth.login'));
        Fortify::registerView(fn() => view('user.auth.register'));

        // 認証ロジックのカスタマイズ
        Fortify::authenticateUsing(function ($request) {
            // 管理者ログイン画面からのリクエストか判定
            $guard = $request->is('admin/*') ? 'admin' : 'web';
            $model = $guard === 'admin' ? \App\Models\Master::class : \App\Models\User::class;

            $user = $model::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }
        });

        // ログアウト応答のカスタマイズ
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                // 管理者ガードで認証されていた、またはURLにadminが含まれる場合
                return $request->is('admin/*') || $request->is('admin')
                    ? redirect('/admin/login')
                    : redirect('/login');
            }
        });
    }
}
