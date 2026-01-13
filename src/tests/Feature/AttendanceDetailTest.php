<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\RestTime;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠詳細画面の各項目が正しく表示されているか
     */
    public function test_attendance_detail_page_displays_correct_information()
    {
        // 1. ユーザー作成
        $user = User::factory()->create(['name' => 'テスト太郎']);
        $this->actingAs($user);

        // 2. 勤怠データ作成（2026年1月15日）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-01-15',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 休憩データ作成
        RestTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        // 3. 勤怠詳細ページを開く
        $response = $this->get(route('attendance.detail', ['attendance_id' => $attendance->id]));
        $response->assertStatus(200);

        // --- 各項目の確認 ---

        // 名前
        $response->assertSee('テスト太郎');

        // 日付：Viewの表示「2026年」「1月15日」に合わせる
        $response->assertSee('2026年');
        $response->assertSee('1月15日');

        // 出勤・退勤：inputタグのvalue属性として入っているか確認
        $response->assertSee('value="09:00"', false); // falseを渡すとHTMLエスケープを無視して検索できます
        $response->assertSee('value="18:00"', false);

        // 休憩：個別の休憩時間が表示されているか確認
        $response->assertSee('value="12:00"', false);
        $response->assertSee('value="13:00"', false);
    }
}