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

    /**
     * ログイン成功時の遷移先を強制的に指定する
     */
    public function redirectUrl()
    {

        if ($this->is('admin/*') || $this->is('admin/login')) {
            return url('/admin/attendance/list');
        }

        return url('/attendance');
    }
}
