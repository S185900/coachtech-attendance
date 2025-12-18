<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // ログインしていない場合に飛ばされる先（/login）を、アクセスしたURLによって切り替える
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }
            return route('login');
        }
    }
}
