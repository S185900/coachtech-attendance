<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テストの準備：画面表示に必要なルートを定義する
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 画面（header等）で使われている「まだ存在しないルート」をダミーで作成
        // これを setUp に書くことで、テスト実行前に確実にルートが登録されます
        Route::get('/dummy-list', function () {})->name('stamp_correction_request.list');
        Route::get('/dummy-approve', function () {})->name('stamp_correction_request.approve'); // 他にも足りないと言われそうなものを予備で追加
    }

    /**
     * 現在の日時情報がUIと同じ形式で出力されている
     */
    public function test_current_date_is_displayed_correctly()
    {
        // 1. Carbonを日本語設定にする
        \Carbon\Carbon::setLocale('ja');

        // 2. ユーザーを作成してログイン状態にする
        // 確実に認証を通すため、factoryでユーザーを作ります
        $user = \App\Models\User::factory()->create();

        // 3. 現在の日時を固定
        $now = \Carbon\Carbon::now();
        \Carbon\Carbon::setTestNow($now);

        // 4. 認証（ログイン）した状態で画面を開く
        $response = $this->actingAs($user)->get('/attendance');

        // 5. 判定用の文字列を生成
        $expectedDate = $now->isoFormat('YYYY年MM月DD日 (ddd)');

        // 6. チェック
        // 302エラーが出る場合は、ここでリダイレクト先を確認するために $response->dumpRedirects(); を入れると原因がわかります
        $response->assertStatus(200);
        $response->assertSee($expectedDate);

        // 7. 時刻の固定を解除
        \Carbon\Carbon::setTestNow();
    }
}
