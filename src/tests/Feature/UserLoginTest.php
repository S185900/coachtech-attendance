<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

// ID:2_ログイン認証機能（一般ユーザー）のテスト
class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $data = [
            'email' => '',
            'password' => 'password123',
        ];

        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => '',
        ];

        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'registered@example.com',
            'password' => bcrypt('password123'),
        ]);

        $data = [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ];

        $response = $this->post('/login', $data);

        $response->assertSessionHasErrors(['auth_error' => 'ログイン情報が登録されていません']);
    }
}
