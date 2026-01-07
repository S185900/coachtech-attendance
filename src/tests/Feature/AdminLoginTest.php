<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Master;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_email_is_required()
    {
        // 1. ユーザー（管理者）を登録する
        $admin = Master::factory()->create([
            'password' => bcrypt('admin-pass'),
        ]);

        // 2. メールアドレス以外のユーザー情報を入力する
        $data = [
            'email' => '', // メールアドレスを未入力にする
            'password' => 'admin-pass',
        ];

        // 3. ログインの処理を行う
        // 管理者用のログインパスに合わせて適宜変更してください（例: /admin/login）
        $response = $this->post('/admin/login', $data);

        // 「メールアドレスを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_password_is_required()
    {
        // 1. ユーザー（管理者）を登録する
        $admin = Master::factory()->create([
            'email' => 'admin@example.com',
        ]);

        // 2. パスワード以外のユーザー情報を入力する
        $data = [
            'email' => 'admin@example.com',
            'password' => '', // パスワードを未入力にする
        ];

        // 3. ログインの処理を行う
        $response = $this->post('/admin/login', $data);

        // 「パスワードを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_fails_with_invalid_credentials()
    {
        // 1. ユーザー（管理者）を登録する
        $admin = Master::factory()->create([
            'email' => 'master@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 2. 誤ったメールアドレスのユーザー情報を入力する
        $data = [
            'email' => 'wrong-admin@example.com', 
            'password' => 'password123',
        ];

        // 3. ログインの処理を行う
        $response = $this->post('/admin/login', $data);

        // 管理者の場合はキーが「email」になっているため、こちらで確認する
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
