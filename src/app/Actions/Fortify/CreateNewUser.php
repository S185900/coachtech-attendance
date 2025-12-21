<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Http\Requests\RegisterRequest;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * 会員登録処理
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // 1. RegisterRequestのインスタンスを生成
        $request = new RegisterRequest();

        // 2. バリデーションを実行
        // $inputを対象に、RegisterRequestで定義したルールとメッセージを適用
        Validator::make($input, $request->rules(), $request->messages())->validate();

        // 3. ユーザー作成
        return User::create([
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
