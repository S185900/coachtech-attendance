<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// ID:1_認証機能（一般ユーザー）のテスト
class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 名前が未入力の場合、バリデーションメッセージが表示される
     */
    public function test_name_is_required()
    {
        $data = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /**
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required()
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * パスワードが8文字未満の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_at_least_8_characters()
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /**
     * パスワードが一致しない場合、バリデーションメッセージが表示される
     */
    public function test_password_must_match_confirmation()
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    /**
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required()
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ];

        $response = $this->post('/register', $data);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * フォームに内容が入力されていた場合、データが正常に保存される
     */
    public function test_user_can_register_with_valid_data()
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $data);

        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect('/attendance');
    }
}
