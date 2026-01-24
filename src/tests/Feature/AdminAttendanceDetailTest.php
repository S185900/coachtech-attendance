<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

// ID:13_勤怠詳細情報取得・修正機能（管理者）のテスト
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
     * 勤怠詳細画面に表示されるデータが選択したものになっている
     */
    public function test_attendance_detail_page_displays_selected_data()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->get(route('admin.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('テストスタッフ');
        $response->assertSee('2026');
        $response->assertSee('1月11日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_error_when_start_time_is_after_end_time_on_admin_detail()
    {
        $this->actingAs($this->admin, 'admin');

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
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_error_when_rest_start_is_after_end_time_on_admin_detail()
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
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_error_when_rest_end_is_after_end_time_on_admin_detail()
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
     * 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_error_when_reason_is_missing_on_admin_detail()
    {
        $this->actingAs($this->admin, 'admin');

        $url = route('admin.attendance.approve', ['id' => $this->attendance->id]);
        $response = $this->post($url, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'reason' => '',
        ]);

        $response->assertSessionHasErrors();
        $this->assertTrue(collect(session('errors')->all())->contains('備考を記入してください'));
    }
}