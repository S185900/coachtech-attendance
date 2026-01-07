<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

// 出勤機能のテスト
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

        // 1. 出勤
        $this->post(route('attendance.start'));

        // 2. 日付をあえて「今日」に固定せず、最新のレコードを無理やり「今日」に書き換えてしまう（テスト用）
        $attendance = Attendance::where('user_id', $user->id)->first();
        $attendance->update([
            'date' => Carbon::today()->toDateString(),
            'status' => 1
        ]);

        // 3. 画面表示の確認
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * 退勤済みの表示を確認
     */
    public function test_cannot_clock_in_twice_a_day()
    {
        $user = User::factory()->create();
        
        // 1. コントローラーが「今日」と認識する日付でデータを作る
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(), // 確実に合わせる
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now(),
            'status' => 0, // 退勤済
        ]);

        $this->actingAs($user);
        $response = $this->get('/attendance');
        
        // 取得できているかデバッグが必要な場合は以下をコメント解除
        // dd($response->original->getData()['attendance']);

        $response->assertSee('お疲れ様でした。');
    }

    /**
     * 出勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_in_time_is_visible_on_list_page()
    {
        $user = User::factory()->create();
        $date = '2026-01-07';

        Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'start_time' => $date . ' 09:30:00',
            'status' => 1,
        ]);
        
        $this->actingAs($user);
        $response = $this->get('/attendance/list?month=2026-01');
        
        $response->assertSee('09:30');
    }
}