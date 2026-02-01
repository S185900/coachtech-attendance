<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

// ID:9_勤怠一覧情報取得機能（一般ユーザー）のテスト
class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 自分が行った勤怠情報が全て表示されている
     */
    public function test_user_can_see_their_own_attendance_data()
    {
        Carbon::setTestNow('2026-01-15');

        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-15',
            'start_time' => '2026-01-15 09:00:00',
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-14',
            'start_time' => '2026-01-14 10:00:00',
        ]);

        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('10:00');
    }

    /**
     * 勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function test_current_month_is_displayed_initially()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Carbon::setTestNow('2026-01-15');

        $response = $this->get(route('attendance.list'));

        $response->assertSee('2026');
        $response->assertSee('01');

        Carbon::setTestNow();
    }

    /**
     * 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_previous_month_button_works()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Carbon::setTestNow('2026-01-15');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-12-01',
            'start_time' => '08:30:00',
        ]);

        $response = $this->get(route('attendance.list', ['month' => '2025-12']));

        $response->assertSee('2025');
        $response->assertSee('12');
        $response->assertSee('08:30');

        Carbon::setTestNow();
    }

    /**
     * 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_next_month_button_works()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Carbon::setTestNow('2026-01-15');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-02-01',
            'start_time' => '09:15:00',
        ]);

        $response = $this->get(route('attendance.list', ['month' => '2026-02']));

        $response->assertSee('2026');
        $response->assertSee('02');
        $response->assertSee('09:15');

        Carbon::setTestNow();
    }

    /**
     * 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_detail_button_redirects_to_correct_page()
    {
        Carbon::setTestNow('2026-01-15');
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-01-15',
            'start_time' => '2026-01-15 09:00:00',
            'status' => Attendance::STATUS_WORKING,
        ]);

        $response = $this->get(route('attendance.list'));
        $detailUrl = route('attendance.detail', ['id' => $attendance->id], false);
        $response->assertSee($detailUrl);
        $detailResponse = $this->get($detailUrl);
        $detailResponse->assertStatus(200);

        Carbon::setTestNow();
    }
}