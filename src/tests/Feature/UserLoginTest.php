<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required()
    {
        // 1. ユーザーを登録する
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        // 2. メールアドレス以外のユーザー情報を入力する
        $data = [
            'email' => '', // メールアドレスを未入力にする
            'password' => 'password123',
        ];

        // 3. ログインの処理を行う
        $response = $this->post('/login', $data);

        // 「メールアドレスを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required()
    {
        // 1. ユーザーを登録する
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // 2. パスワード以外のユーザー情報を入力する
        $data = [
            'email' => 'test@example.com',
            'password' => '', // パスワードを未入力にする
        ];

        // 3. ログインの処理を行う
        $response = $this->post('/login', $data);

        // 「パスワードを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_login_fails_with_invalid_credentials()
    {
        // 1. ユーザーを登録する
        $user = User::factory()->create([
            'email' => 'registered@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 2. 誤ったメールアドレスのユーザー情報を入力する
        $data = [
            'email' => 'wrong@example.com', 
            'password' => 'password123',
        ];

        // 3. ログインの処理を行う
        $response = $this->post('/login', $data);

        // キーを「auth_error」にしてメッセージを確認する
        $response->assertSessionHasErrors(['auth_error' => 'ログイン情報が登録されていません']);
    }
}
