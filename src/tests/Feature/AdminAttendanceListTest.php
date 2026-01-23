<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => 'テスト太郎']);
        $this->admin = User::factory()->create(['name' => '管理者ユーザー']);

        Carbon::setTestNow('2026-01-23');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * 1. 全ユーザーの勤怠情報が正確に確認できる
     * 2. 遷移した際に現在の日付が表示される
     */
    public function test_admin_can_see_attendance_list_with_current_date()
    {
        $this->actingAs($this->admin, 'admin');

        Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-01-23',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->get(route('admin.attendance.list'));

        $response->assertStatus(200);

        $response->assertSee('2026年1月23日');

        $response->assertSee('テスト太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 3.「前日」を押下した時に前の日の勤怠情報が表示される
     */
    public function test_admin_can_navigate_to_previous_day()
    {
        $this->actingAs($this->admin, 'admin');

        Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-01-22',
            'start_time' => '10:00:00',
        ]);

        $response = $this->get(route('admin.attendance.list', ['date' => '2026-01-22']));

        $response->assertStatus(200);

        $response->assertSee('2026年1月22日');
        $response->assertSee('10:00');
    }

    /**
     * 4.「翌日」を押下した時に次の日の勤怠情報が表示される
     */
    public function test_admin_can_navigate_to_next_day()
    {
        $this->actingAs($this->admin, 'admin');

        Attendance::create([
            'user_id' => $this->user->id,
            'date' => '2026-01-24',
            'start_time' => '11:00:00',
        ]);

        $response = $this->get(route('admin.attendance.list', ['date' => '2026-01-24']));

        $response->assertStatus(200);

        $response->assertSee('2026年1月24日');
        $response->assertSee('11:00');
    }
}