<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 退勤ボタンが正しく機能する
     */
    public function test_clock_out_button_functions_correctly()
    {
        // 1. ユーザー作成
        $user = User::factory()->create();
        
        // 2. 「出勤中」の状態を作る (Viewの条件に合わせて status を 1 にする)
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now()->subHours(2),
            'end_time' => null,
            'status' => 1, // ★ Viewの @elseif($attendance->status == 1) に合わせる
        ]);

        $this->actingAs($user);

        // 3. 画面に「退勤」ボタンが表示されていることを確認する
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤');

        // 4. 退勤の処理を行う
        // Viewの form action="{{ route('attendance.end') }}" に合わせる
        $postResponse = $this->post(route('attendance.end')); 

        // 5. 処理後にステータスが「退勤済」のバッジになり、「お疲れ様でした。」が表示される
        $finalResponse = $this->get('/attendance');
        $finalResponse->assertSee('退勤済');
        $finalResponse->assertSee('お疲れ様でした。');
    }

    /**
     * 退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_out_time_is_visible_on_attendance_list()
    {
        // 1. ステータスが勤務外のユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 現在時刻を固定（テストの正確性を保つため）
        $now = Carbon::create(2026, 1, 11, 18, 0, 0);
        Carbon::setTestNow($now);

        // 2. 出勤と退勤の処理を行う
        // 出勤POST
        $this->post('/attendance/work-start');
        // 退勤POST
        $this->post('/attendance/leave');

        // 3. 勤怠一覧画面から退勤の日付を確認する
        $listResponse = $this->get('/attendance/list');
        $listResponse->assertStatus(200);

        // 勤怠一覧画面に退勤時刻(18:00)が正確に記録されていることを確認
        $listResponse->assertSee('18:00');
        
        // テスト終了後は時刻固定を解除
        Carbon::setTestNow();
    }
}