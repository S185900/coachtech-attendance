<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

// ID:11_勤怠詳細情報修正機能（一般ユーザー）のテスト
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
     * 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_error_when_start_time_is_after_end_time()
    {
        $this->actingAs($this->user);
        $url = route('attendance.update', ['attendance_id' => $this->attendance->id]);

        $response = $this->post($url, [
            'start_time' => '19:00',
            'end_time' => '18:00',
            'reason' => '修正理由',
        ]);

        $response->assertSessionHasErrors();
        $this->assertTrue(
            collect(session('errors')->all())->contains('出勤時間もしくは退勤時間が不適切な値です'),
            '「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示されること'
        );
    }

    /**
     * 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_error_when_rest_start_is_after_end_time()
    {
        $this->actingAs($this->user);
        $url = route('attendance.update', ['attendance_id' => $this->attendance->id]);

        $response = $this->post($url, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'rests' => [['start' => '19:00', 'end' => '20:00']],
            'reason' => '修正理由',
        ]);

        $response->assertSessionHasErrors();
        $this->assertTrue(
            collect(session('errors')->all())->contains('休憩時間が不適切な値です'),
            '「休憩時間が不適切な値です」というバリデーションメッセージが表示されること'
        );
    }

    /**
     * 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_error_when_rest_end_is_after_end_time()
    {
        $this->actingAs($this->user);
        $url = route('attendance.update', ['attendance_id' => $this->attendance->id]);

        $response = $this->post($url, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'rests' => [['start' => '12:00', 'end' => '19:00']],
            'reason' => '修正理由',
        ]);

        $response->assertSessionHasErrors();
        $this->assertTrue(
            collect(session('errors')->all())->contains('休憩時間もしくは退勤時間が不適切な値です'),
            '「休憩時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示されること'
        );
    }

    /**
     * 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_error_when_reason_is_missing()
    {
        $this->actingAs($this->user);
        $url = route('attendance.update', ['attendance_id' => $this->attendance->id]);

        $response = $this->post($url, [
            'start_time' => '09:00',
            'end_time' => '18:00',
            'reason' => '',
        ]);

        $response->assertSessionHasErrors();
        $this->assertTrue(
            collect(session('errors')->all())->contains('備考を記入してください'),
            '「備考を記入してください」というバリデーションメッセージが表示されること'
        );
    }

    /**
     * 「承認待ち」にログインユーザーが行った申請が全て表示されていること
     */
    public function test_request_list_shows_pending_requests()
    {
        $this->actingAs($this->user);

        $this->post(route('attendance.update', ['attendance_id' => $this->attendance->id]), [
            'start_time' => '08:30',
            'end_time' => '17:30',
            'reason' => '早出のため修正',
        ]);

        $response = $this->get(route('stamp_correction_request.list'));
        $response->assertStatus(200);

        $response->assertSee('承認待ち');
        $response->assertSee('早出のため修正');
    }

    /**
     * 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
     */
    public function test_request_list_detail_button_redirects_to_detail_page()
    {
        $this->actingAs($this->user);

        $this->post(route('attendance.update', ['attendance_id' => $this->attendance->id]), [
            'start_time' => '08:30',
            'end_time' => '17:30',
            'reason' => '詳細ボタンのテスト',
        ]);

        $response = $this->get(route('stamp_correction_request.list'));

        $response->assertSee('詳細');
    }
}