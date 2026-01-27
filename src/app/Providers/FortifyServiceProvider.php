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
        $this->app->singleton(
            \Laravel\Fortify\Http\Requests\LoginRequest::class,
            LoginRequest::class
        );

        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::loginView(function () {
            if (request()->is('admin/*') || request()->is('admin')) {
                return view('admin.auth.login');
            }
            return view('user.auth.login');
        });

        Fortify::registerView(fn() => view('user.auth.register'));

        Fortify::verifyEmailView(function () {
            return view('user.auth.verify-email');
        });

        Fortify::authenticateUsing(function ($request) {
            $isAdmin = $request->is('admin/*') || $request->is('admin');
            $model = $isAdmin ? \App\Models\Master::class : \App\Models\User::class;
            $guard = $isAdmin ? 'admin' : 'web';

            $user = $model::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {

                session(['login_type' => $isAdmin ? 'admin' : 'user']);

                auth()->guard($guard)->login($user);

                return $user;
            }

            throw \Illuminate\Validation\ValidationException::withMessages([
                'auth_error' => 'ログイン情報が登録されていません',
            ]);
        });

        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                return ($request->is('admin/*') || $request->is('admin'))
                    ? redirect('/admin/login')
                    : redirect('/login');
            }
        });

        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                if (session('login_type') === 'admin') {
                    return redirect()->intended('/admin/attendance/list');
                }
                return redirect()->intended('/attendance');
            }
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
            return Limit::perMinute(30)->by($throttleKey);
        });
    }
}