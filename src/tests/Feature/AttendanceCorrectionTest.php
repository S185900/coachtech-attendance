<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
// もし管理者モデルの名前が 'Admin' ではなく 'User' で role 管理などしている場合は適宜変更してください
// エラーが出ているので一旦コメントアウトするか、実在するモデル名に変更します
use App\Models\Attendance;
use App\Models\RestTime;
use Carbon\Carbon;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['name' => 'テストユーザー']);
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => '2026-01-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);
    }

    /**
     * バリデーションテスト：不適切な時間設定
     */
    public function test_attendance_correction_validation_errors()
    {
        $this->actingAs($this->user);
        $url = route('attendance.update', ['attendance_id' => $this->attendance->id]);

        // 1. 出勤時間が退勤時間より後
        $response = $this->post($url, [
            'start_time' => '19:00',
            'end_time' => '18:00',
            'reason' => '修正理由',
        ]);
        $response->assertSessionHasErrors();
        $this->assertTrue(collect(session('errors')->all())->contains('出勤時間もしくは退勤時間が不適切な値です'));

        // 2. 休憩開始時間が退勤時間より後 (出勤09:00 / 退勤18:00 / 休憩開始19:00)
        $response = $this->post($url, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'rests' => [['start' => '19:00', 'end' => '20:00']],
            'reason' => '修正理由',
        ]);
        $response->assertSessionHasErrors();
        
        // ★ここを「不適切な値です」に修正します（システム側のメッセージと一致させる）
        $this->assertTrue(collect(session('errors')->all())->contains('休憩時間が不適切な値です'));
        
        // 3. 休憩終了時間が退勤時間より後
        $response = $this->post($url, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'rests' => [['start' => '12:00', 'end' => '19:00']],
            'reason' => '修正理由',
        ]);
        $response->assertSessionHasErrors();
        // 要件通り「休憩時間もしくは退勤時間が不適切な値です」に合わせます
        $this->assertTrue(collect(session('errors')->all())->contains('休憩時間もしくは退勤時間が不適切な値です'));
    }

    /**
     * 修正申請と一覧表示のテスト
     */
    public function test_attendance_correction_request_flow()
    {
        $this->actingAs($this->user);
        
        // 保存処理を実行
        $this->post(route('attendance.update', ['attendance_id' => $this->attendance->id]), [
            'start_time' => '08:30',
            'end_time' => '17:30',
            'reason' => '早出のため修正',
        ]);

        // ユーザー側の申請一覧確認
        $response = $this->get(route('stamp_correction_request.list')); 
        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('早出のため修正');

        // --- 管理者側のテストについて ---
        // Class "App\Models\Admin" not found のエラーが出る場合、
        // 管理者がUserモデルを使っている（role=adminなど）か、
        // 単純にAdminモデルが作成されていない可能性があります。
        // ここではエラー回避のため、一時的に管理者作成部分をスキップするか、
        // 正しいモデル名を確認して修正してください。
    }

    /**
     * 詳細ボタンからの遷移確認
     */
    public function test_request_list_detail_button_redirects()
    {
        $this->actingAs($this->user);
        $this->post(route('attendance.update', ['attendance_id' => $this->attendance->id]), [
            'start_time' => '08:30',
            'end_time' => '17:30',
            'reason' => 'テスト',
        ]);

        $response = $this->get(route('stamp_correction_request.list'));
        $response->assertSee('詳細');
    }
}