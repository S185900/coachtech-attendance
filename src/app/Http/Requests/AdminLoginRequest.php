<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }

//     「ログイン情報が登録されていません」というメッセージは、DBの照合結果（パスワードが違う、またはメールアドレスが存在しない）に基づいて表示させます。

// AdminLoginController.php（例）

// public function login(AdminLoginRequest $request)
// {
//     // 1. 未入力チェック（AdminLoginRequestで自動実行済み）
//     $credentials = $request->only('email', 'password');

//     // 2. 管理者ガード（admin）を使用して認証
//     if (Auth::guard('admin')->attempt($credentials)) {
//         $request->session()->regenerate();
//         return redirect()->route('admin.dashboard');
//     }

//     // 3. 認証失敗時：「ログイン情報が登録されていません」を返す
//     return back()->withErrors([
//         'email' => 'ログイン情報が登録されていません',
//     ])->onlyInput('email'); 
// }
}
