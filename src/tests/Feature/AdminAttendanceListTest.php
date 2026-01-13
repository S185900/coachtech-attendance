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
    }

    /**
     * PG08: 管理者勤怠一覧
     */
    public function test_admin_can_see_attendance_list_of_the_day()
    {
        $today = Carbon::today();
        
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => $today->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($this->admin, 'admin');
        $response = $this->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        
        // 画面の「2026年1月11日」という表記に合わせて n月j日 に修正
        $response->assertSee($today->format('Y年n月j日'));
        $response->assertSee('テスト太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 「前日」移動のテスト
     */
    public function test_admin_can_navigate_to_previous_day()
    {
        $yesterday = Carbon::yesterday();
        
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => $yesterday->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        $this->actingAs($this->admin, 'admin');
        $response = $this->get(route('admin.attendance.list', ['date' => $yesterday->format('Y-m-d')]));

        $response->assertStatus(200);
        // フォーマットを n月j日 に修正
        $response->assertSee($yesterday->format('Y年n月j日'));
        $response->assertSee('10:00');
    }

    /**
     * 「翌日」移動のテスト
     */
    public function test_admin_can_navigate_to_next_day()
    {
        $tomorrow = Carbon::tomorrow();
        
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => $tomorrow->format('Y-m-d'),
            'start_time' => '11:00:00',
            'end_time' => '20:00:00',
        ]);

        $this->actingAs($this->admin, 'admin');
        $response = $this->get(route('admin.attendance.list', ['date' => $tomorrow->format('Y-m-d')]));

        $response->assertStatus(200);
        // フォーマットを n月j日 に修正
        $response->assertSee($tomorrow->format('Y年n月j日'));
        $response->assertSee('11:00');
    }
}