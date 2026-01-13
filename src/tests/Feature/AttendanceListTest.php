<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. 自分が行った勤怠情報が全て表示されている
     */
    public function test_user_can_see_their_own_attendance_data()
    {
        // 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 複数の勤怠データを作成
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'start_time' => '09:00:00',
        ]);
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::yesterday()->format('Y-m-d'),
            'start_time' => '10:00:00',
        ]);

        // 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list'));

        // 自分の勤怠情報が表示されていることを確認する
        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('10:00');
    }

    /**
     * 2. 勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function test_current_month_is_displayed_initially()
    {
        // ユーザーにログインをする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 現在時刻を固定
        Carbon::setTestNow('2026-01-15');

        // 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list'));

        // 現在の月（2026/01 または 2026年01月）が表示されている
        $response->assertSee('2026');
        $response->assertSee('01');

        Carbon::setTestNow(); // 解除
    }

    /**
     * 3. 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_previous_month_button_works()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Carbon::setTestNow('2026-01-15');

        // 前月（2025年12月）のデータを作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-12-01',
            'start_time' => '08:30:00',
        ]);

        // 勤怠一覧ページを開き、「前月」リンクのURLへアクセス
        // Bladeの href="{{ route('attendance.list', ['month' => $prevMonth]) }}" に合わせる
        $response = $this->get(route('attendance.list', ['month' => '2025-12']));

        // 前月の情報が表示されている
        $response->assertSee('2025');
        $response->assertSee('12');
        $response->assertSee('08:30');

        Carbon::setTestNow();
    }

    /**
     * 4. 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_next_month_button_works()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Carbon::setTestNow('2026-01-15');

        // 翌月（2026年2月）のデータを作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-02-01',
            'start_time' => '09:15:00',
        ]);

        // 勤怠一覧ページを開き、「翌月」リンクのURLへアクセス
        $response = $this->get(route('attendance.list', ['month' => '2026-02']));

        // 翌月の情報が表示されている
        $response->assertSee('2026');
        $response->assertSee('02');
        $response->assertSee('09:15');

        Carbon::setTestNow();
    }

    /**
     * 5. 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_detail_button_redirects_to_correct_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
        ]);

        // 勤怠一覧ページを開く
        $response = $this->get(route('attendance.list'));

        // 「詳細」ボタン（リンク）が正しいIDを指しているか確認
        // URLは route('attendance.detail', ['attendance_id' => $attendance->id]) の形式を想定
        $detailUrl = route('attendance.detail', ['attendance_id' => $attendance->id]);
        $response->assertSee($detailUrl);

        // 実際にそのURLにアクセスして200が返るか確認
        $detailResponse = $this->get($detailUrl);
        $detailResponse->assertStatus(200);
    }
}