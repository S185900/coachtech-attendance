<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

// ID:6_出勤機能のテスト
class AttendancePostTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * 出勤ボタンが正しく機能する
     */
    public function test_clock_in_button_works_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤');

        $response = $this->post('/attendance/start');

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status'  => Attendance::STATUS_WORKING,
        ]);
    }

    /**
     * 出勤は一日一回のみできる
     */
    public function test_cannot_clock_in_twice_a_day()
    {
        $user = User::factory()->create();

        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => \Carbon\Carbon::today()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'status' => Attendance::STATUS_RETIRED,
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('お疲れ様でした。');
        $response->assertDontSee('class="attendance-button">出勤</button>', false);
        $response->assertSee('status-done">退勤済</span>', false);
    }

    /**
     * 出勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_in_time_is_visible_on_list_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $knownTime = \Carbon\Carbon::create(2026, 1, 13, 9, 30, 0);
        \Carbon\Carbon::setTestNow($knownTime);

        $this->post('/attendance/start');

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:30');

        \Carbon\Carbon::setTestNow();
    }
}