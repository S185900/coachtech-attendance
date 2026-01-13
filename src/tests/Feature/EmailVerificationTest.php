<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use App\Models\User;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. 会員登録後、認証メールが送信される
     */
    public function test_verification_email_is_sent_after_registration()
    {
        Notification::fake();

        // 実際の実装に合わせて登録処理を実行
        $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        // 認証メールが送信されたことを検証
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * 2. メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     */
    public function test_can_see_email_verification_notice_page()
    {
        // メール未認証のユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // ログインして勤怠画面（/attendance）にアクセス。verifiedミドルウェアによりリダイレクトされる
        $response = $this->actingAs($user)->get('/attendance');

        // 誘導画面（/email/verify）へリダイレクトされることを確認
        $response->assertRedirect('/email/verify');
        
        $response = $this->get('/email/verify');
        $response->assertStatus(200);

        // 実際のHTMLに含まれる文言をチェック
        $response->assertSee('認証メールを送付しました');
        $response->assertSee('認証はこちらから');
        
        // ボタンのリンク先（MailHog等）のURLが含まれているか確認
        $response->assertSee('http://localhost:8025');
    }

    /**
     * 3. メール認証サイトのメール認証を完了すると、勤怠登録画面に遷移する
     */
    public function test_email_can_be_verified_and_redirects_to_attendance()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // routes/web.php の 'verification.verify' に基づいて署名付きURLを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 認証URLにアクセス
        $response = $this->actingAs($user)->get($verificationUrl);

        // 1. ユーザーの email_verified_at が更新されているか確認
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // 2. ルート定義の通り /attendance にリダイレクトされるか確認
        $response->assertRedirect('/attendance');
    }
}