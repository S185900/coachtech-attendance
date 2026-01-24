<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

// ID:14_ユーザー情報取得機能（管理者）のテスト
class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['name' => '管理者']);

        $this->staff = User::factory()->create([
            'name' => 'テストスタッフ',
            'email' => 'staff@example.com'
        ]);
    }

    /**
     * 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
     */
    public function test_admin_can_see_staff_list()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->get(route('admin.staff.list'));

        $response->assertStatus(200);
        $response->assertSee($this->staff->name);
        $response->assertSee($this->staff->email);
    }

    /**
     * ユーザーの勤怠情報が正しく表示される
     */
    public function test_admin_can_see_individual_staff_attendance()
    {
        Carbon::setTestNow('2026-01-15');

        $this->actingAs($this->admin, 'admin');

        Attendance::create([
            'user_id' => $this->staff->id,
            'date' => '2026-01-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->get(route('admin.attendance.staff', ['id' => $this->staff->id]));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_admin_can_navigate_to_previous_month()
    {
        $this->actingAs($this->admin, 'admin');
        $lastMonth = Carbon::now()->subMonth();

        Attendance::create([
            'user_id' => $this->staff->id,
            'date' => $lastMonth->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        $response = $this->get(route('admin.attendance.staff', [
            'id' => $this->staff->id,
            'month' => $lastMonth->format('Y-m')
        ]));

        $response->assertStatus(200);
        $response->assertSee($lastMonth->format('Y/m'));
        $response->assertSee('10:00');
    }

    /**
     * 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_admin_can_navigate_to_next_month()
    {
        $this->actingAs($this->admin, 'admin');
        $nextMonth = Carbon::now()->addMonth();

        Attendance::create([
            'user_id' => $this->staff->id,
            'date' => $nextMonth->format('Y-m-d'),
            'start_time' => '11:00:00',
            'end_time' => '20:00:00',
        ]);

        $response = $this->get(route('admin.attendance.staff', [
            'id' => $this->staff->id,
            'month' => $nextMonth->format('Y-m')
        ]));

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('Y/m'));
        $response->assertSee('11:00');
    }

    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_admin_can_click_detail_and_redirect()
    {
        $this->actingAs($this->admin, 'admin');

        $attendance = Attendance::create([
            'user_id' => $this->staff->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->get(route('admin.attendance.staff', ['id' => $this->staff->id]));

        $detailUrl = route('admin.attendance.detail', ['id' => $attendance->id]);
        $response->assertSee($detailUrl);

        $response = $this->get($detailUrl);
        $response->assertStatus(200);
    }
}