<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

// ID:8_退勤機能のテスト
class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 退勤ボタンが正しく機能する
     */
    public function test_clock_out_button_functions_correctly()
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now()->subHours(2),
            'end_time' => null,
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertSee('退勤');

        $this->post(route('attendance.end'));

        $finalResponse = $this->get('/attendance');
        $finalResponse->assertSee('退勤済');
        $finalResponse->assertSee('退勤');
        $finalResponse->assertSee('お疲れ様でした。');
    }

    /**
     * 退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_out_time_is_visible_on_attendance_list()
    {
        $user = User::factory()->create();
        $user->markEmailAsVerified(); 

        $this->actingAs($user);

        $now = Carbon::create(2026, 1, 11, 18, 0, 0);
        Carbon::setTestNow($now);

        $this->post('/attendance/start');

        $this->post('/attendance/end');

        $listResponse = $this->get('/attendance/list?month=2026-01');
        $listResponse->assertStatus(200);

        $listResponse->assertSee('18:00');

        Carbon::setTestNow();
    }
}