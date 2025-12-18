<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginMasterController extends Controller
{
    public function create()
    {
        return view('admin.auth.login');
    }

    public function store(AdminLoginRequest $request)
    {
        if (Auth::guard('admin')->attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            $request->session()->regenerate();
            return redirect()->intended('/admin/attendance/list'); // 管理者ログイン後のリダイレクト先
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }
}
