<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Http\Requests\RegisterRequest;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // 1. RegisterRequestのインスタンスを生成
        $request = new RegisterRequest();

        // 2. 独自のリクエストバリデーションを実行
        // $input（配列）を対象に、RegisterRequestで定義したルールでチェック
        \Validator::make($input, $request->rules(), $request->messages())->validate();

        // 3. ユーザー作成
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
