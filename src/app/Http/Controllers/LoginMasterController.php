<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginMasterController extends Controller
{
    /**
     * 管理者ログイン画面の表示
     */
    public function create()
    {
        return view('admin.auth.login');
    }

    /**
     * 管理者ログイン処理
     */
    public function store(AdminLoginRequest $request)
    {
        if (Auth::guard('admin')->attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            $request->session()->regenerate();
            return redirect()->intended('/admin/attendance/list');
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }
}
