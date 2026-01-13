<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['name' => 'テストスタッフ']);
        $this->admin = User::factory()->create(['name' => '管理者']);
        
        $this->attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-01-11',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);
    }

    /**
     * 1. 勤怠詳細画面の表示チェック
     */
    public function test_admin_can_see_attendance_details()
    {
        $this->actingAs($this->admin, 'admin');

        // ルート名を admin.attendance.detail に
        $response = $this->get(route('admin.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('テストスタッフ');
        $response->assertSee('2026');
        $response->assertSee('1月11日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 2. 出勤時間 > 退勤時間 のバリデーション
     */
    public function test_admin_validation_start_time_after_end_time()
    {
        $this->actingAs($this->admin, 'admin');
        // ルート名を admin.attendance.approve に修正
        $url = route('admin.attendance.approve', ['id' => $this->attendance->id]);

        $response = $this->post($url, [
            'start_time' => '19:00',
            'end_time' => '18:00',
            'reason' => '修正理由',
        ]);

        $response->assertSessionHasErrors();
        $this->assertTrue(collect(session('errors')->all())->contains('出勤時間もしくは退勤時間が不適切な値です'));
    }

    /**
     * 3. 休憩開始 > 退勤時間 のバリデーション
     */
    public function test_admin_validation_rest_start_after_end_time()
    {
        $this->actingAs($this->admin, 'admin');
        $url = route('admin.attendance.approve', ['id' => $this->attendance->id]);

        $response = $this->post($url, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'rests' => [['start' => '19:00', 'end' => '20:00']],
            'reason' => '修正理由',
        ]);

        $response->assertSessionHasErrors();
        $this->assertTrue(collect(session('errors')->all())->contains('休憩時間が不適切な値です'));
    }

    /**
     * 4. 休憩終了 > 退勤時間 のバリデーション
     */
    public function test_admin_validation_rest_end_after_end_time()
    {
        $this->actingAs($this->admin, 'admin');
        $url = route('admin.attendance.approve', ['id' => $this->attendance->id]);

        $response = $this->post($url, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'rests' => [['start' => '12:00', 'end' => '19:00']],
            'reason' => '修正理由',
        ]);

        $response->assertSessionHasErrors();
        $this->assertTrue(collect(session('errors')->all())->contains('休憩時間もしくは退勤時間が不適切な値です'));
    }

    /**
     * 5. 備考欄未入力のバリデーション
     */
    public function test_admin_validation_reason_required()
    {
        $this->actingAs($this->admin, 'admin');
        $url = route('admin.attendance.approve', ['id' => $this->attendance->id]);

        $response = $this->post($url, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'reason' => '', // 未入力
        ]);

        $response->assertSessionHasErrors();
        $this->assertTrue(collect(session('errors')->all())->contains('備考を記入してください'));
    }
}