<?php

namespace App\Http\Requests;

use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class LoginRequest extends FortifyLoginRequest
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
     * バリデーションルール
     * Fortifyの標準ルールをオーバーライド
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

    // 「ログイン情報が登録されていません」の処理方法：
    // これは、FortifyServiceProvider.phpで処理されているため、ここでは不要

    /**
     * ログイン成功時の遷移先を強制的に指定する
     */
    public function redirectUrl()
    {
        // 送信先が管理者ログインURL、またはパスに admin が含まれる場合
        if ($this->is('admin/*') || $this->is('admin/login')) {
            return url('/admin/attendance/list');
        }

        // それ以外（一般ユーザー）
        return url('/attendance');
    }
}
