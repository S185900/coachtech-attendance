<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\RestTime;

// ID:10_勤怠詳細情報取得機能（一般ユーザー）のテスト
class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['name' => 'テスト太郎']);
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => '2026-01-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        RestTime::create([
            'attendance_id' => $this->attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);
    }

    /**
     * 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function test_attendance_detail_displays_user_name()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('attendance.detail', ['attendance_id' => $this->attendance->id]));

        $response->assertSee('テスト太郎');
    }

    /**
     * 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_attendance_detail_displays_correct_date()
    {
        $this->actingAs($this->user);
        $response = $this->get(route('attendance.detail', ['attendance_id' => $this->attendance->id]));

        $response->assertSee('2026年');
        $response->assertSee('1月15日');
    }

    /**
     * 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_attendance_detail_displays_correct_work_times()
    {
        $this->actingAs($this->user);
        $response = $this->get(route('attendance.detail', ['attendance_id' => $this->attendance->id]));

        $response->assertSee('value="09:00"', false);
        $response->assertSee('value="18:00"', false);
    }

    /**
     * 「休憩」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_attendance_detail_displays_correct_rest_times()
    {
        $this->actingAs($this->user);
        $response = $this->get(route('attendance.detail', ['attendance_id' => $this->attendance->id]));

        $response->assertSee('value="12:00"', false);
        $response->assertSee('value="13:00"', false);
    }
}