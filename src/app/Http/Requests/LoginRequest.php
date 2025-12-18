<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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

// 「ログイン情報が登録されていません」の処理方法
// 「入力内容が正しい形式か」ではなく、「DBに登録があるか/一致するか」というチェックは、コントローラーでの認証処理（Auth::attempt）時に行います。

// 以下のようにコントローラーを記述することで、ご要望通りのエラーメッセージを表示できます。

// LoginController.php（例）

// public function login(LoginRequest $request)
// {
//     // 1. バリデーション（LoginRequestで自動実行）
//     $credentials = $request->only('email', 'password');

//     // 2. 認証処理
//     if (Auth::attempt($credentials)) {
//         // 成功時の処理
//         $request->session()->regenerate();
//         return redirect()->intended('dashboard');
//     }

//     // 3. 認証失敗時：エラーメッセージを返却
//     return back()->withErrors([
//         'login_error' => 'ログイン情報が登録されていません',
//     ])->onlyInput('email'); // メールアドレスだけ入力値を残す
// }
}
