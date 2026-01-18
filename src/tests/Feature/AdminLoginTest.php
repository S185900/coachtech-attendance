<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Master;

// ログイン認証機能（管理者）のテスト
class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_email_is_required()
    {
        $admin = Master::factory()->create([
            'password' => bcrypt('admin-pass'),
        ]);

        $data = [
            'email' => '',
            'password' => 'admin-pass',
        ];

        $response = $this->post('/admin/login', $data);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_password_is_required()
    {
        $admin = Master::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $data = [
            'email' => 'admin@example.com',
            'password' => '',
        ];

        $response = $this->post('/admin/login', $data);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_fails_with_invalid_credentials()
    {
        $admin = Master::factory()->create([
            'email' => 'master@example.com',
            'password' => bcrypt('password123'),
        ]);

        $data = [
            'email' => 'wrong-admin@example.com', 
            'password' => 'password123',
        ];

        $response = $this->post('/admin/login', $data);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
