<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;


// 認証機能（一般ユーザー）の登録テスト
class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 名前が未入力の場合、バリデーションメッセージが表示される
     */
    public function test_name_is_required()
    {
        // 1. 名前以外のユーザー情報を入力する
        $data = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 2. 会員登録の処理を行う
        $response = $this->post('/register', $data);

        // 「お名前を入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /**
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required()
    {
        // 1. メールアドレス以外のユーザー情報を入力する
        $data = [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 2. 会員登録の処理を行う
        $response = $this->post('/register', $data);

        // 「メールアドレスを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * パスワードが8文字未満の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_at_least_8_characters()
    {
        // 1. パスワードを8文字未満にし、ユーザー情報を入力する
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ];

        // 2. 会員登録の処理を行う
        $response = $this->post('/register', $data);

        // 「パスワードは8文字以上で入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /**
     * パスワードが一致しない場合、バリデーションメッセージが表示される
     */
    public function test_password_must_match_confirmation()
    {
        // 1. 確認用のパスワードとパスワードを一致させず、ユーザー情報を入力する
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        // 2. 会員登録の処理を行う
        $response = $this->post('/register', $data);

        // 「パスワードと一致しません」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    /**
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required()
    {
        // 1. パスワード以外のユーザー情報を入力する
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ];

        // 2. 会員登録の処理を行う
        $response = $this->post('/register', $data);

        // 「パスワードを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * フォームに内容が入力されていた場合、データが正常に保存される
     */
    public function test_user_can_register_with_valid_data()
    {
        // 1. ユーザー情報を入力する
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 2. 会員登録の処理を行う
        $response = $this->post('/register', $data);

        // データベースに登録したユーザー情報が保存される
        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 正常保存後の遷移先確認（必要に応じて）
        $response->assertRedirect('/attendance');
    }
}
