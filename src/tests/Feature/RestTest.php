<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

// ID:7_休憩機能のテスト
class RestTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * 休憩ボタンが正しく機能する
     */
    public function test_rest_start_button_works_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::now()->subHour(),
            'status' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        $this->post('/attendance/rest-start');

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /**
     * 休憩は一日に何回でもできる
     */
    public function test_can_rest_multiple_times_a_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::now()->subHours(2),
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->post('/attendance/rest-start');
        $this->post('/attendance/rest-end');

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    /**
     * 休憩戻ボタンが正しく機能する
     */
    public function test_rest_end_button_works_correctly()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::now()->subHour(),
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->post('/attendance/rest-start');

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        $this->post('/attendance/rest-end');

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * 休憩戻は一日に何回でもできる
     */
    public function test_can_rest_end_multiple_times_a_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::now()->subHours(3),
            'status' => Attendance::STATUS_WORKING,
        ]);

        $this->post('/attendance/rest-start'); // 1回目入
        $this->post('/attendance/rest-end');   // 1回目戻
        $this->post('/attendance/rest-start'); // 2回目入

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /**
     * 休憩時刻が勤怠一覧画面で確認できる
     */
    public function test_rest_times_are_visible_on_list_page()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $today = \Carbon\Carbon::create(2026, 1, 9);
        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => $today->toDateString(),
            'start_time' => '09:00:00',
            'status' => Attendance::STATUS_WORKING,
        ]);

        \Carbon\Carbon::setTestNow($today->copy()->setTime(12, 0, 0));
        $this->post('/attendance/rest-start');

        \Carbon\Carbon::setTestNow($today->copy()->setTime(13, 0, 0));
        $this->post('/attendance/rest-end');

        $response = $this->get('/attendance/list?month=' . $today->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee('01:00');

        \Carbon\Carbon::setTestNow();
    }
}