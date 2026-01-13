<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // 管理者ユーザーを作成
        $this->admin = User::factory()->create(['name' => '管理者']);
        // 一般スタッフを作成
        $this->staff = User::factory()->create([
            'name' => 'テストスタッフ',
            'email' => 'staff@example.com'
        ]);
    }

    /**
     * 1. 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
     */
    public function test_admin_can_see_staff_list()
    {
        $this->actingAs($this->admin, 'admin');

        // スタッフ一覧ページを開く
        $response = $this->get(route('admin.staff.list'));

        $response->assertStatus(200);
        // 全ての一般ユーザーの氏名とメールアドレスが表示されているか
        $response->assertSee('テストスタッフ');
        $response->assertSee('staff@example.com');
    }

    /**
     * 2. ユーザーの勤怠情報が正しく表示される
     */
    public function test_admin_can_see_individual_staff_attendance()
    {
        $this->actingAs($this->admin, 'admin');

        // 今月の勤怠データを作成
        Attendance::create([
            'user_id' => $this->staff->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        // 選択したユーザーの勤怠一覧ページを開く
        $response = $this->get(route('admin.attendance.staff', ['id' => $this->staff->id]));

        $response->assertStatus(200);
        $response->assertSee('テストスタッフ');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * 3. 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_admin_can_navigate_to_previous_month()
    {
        $this->actingAs($this->admin, 'admin');
        $lastMonth = Carbon::now()->subMonth();

        // 前月のデータを作成
        Attendance::create([
            'user_id' => $this->staff->id,
            'date' => $lastMonth->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        // クエリパラメータで前月を指定してアクセス
        $response = $this->get(route('admin.attendance.staff', [
            'id' => $this->staff->id,
            'month' => $lastMonth->format('Y-m')
        ]));

        $response->assertStatus(200);
        // 前月の情報が表示されているか（例：2025/12）
        $response->assertSee($lastMonth->format('Y/m'));
        $response->assertSee('10:00');
    }

    /**
     * 4. 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_admin_can_navigate_to_next_month()
    {
        $this->actingAs($this->admin, 'admin');
        $nextMonth = Carbon::now()->addMonth();

        // 翌月のデータを作成
        Attendance::create([
            'user_id' => $this->staff->id,
            'date' => $nextMonth->format('Y-m-d'),
            'start_time' => '11:00:00',
            'end_time' => '20:00:00',
        ]);

        // クエリパラメータで翌月を指定してアクセス
        $response = $this->get(route('admin.attendance.staff', [
            'id' => $this->staff->id,
            'month' => $nextMonth->format('Y-m')
        ]));

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('Y/m'));
        $response->assertSee('11:00');
    }

    /**
     * 5. 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
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

        // 勤怠一覧ページを開く
        $response = $this->get(route('admin.attendance.staff', ['id' => $this->staff->id]));

        // 詳細画面へのリンクが存在するか確認
        $detailUrl = route('admin.attendance.detail', ['id' => $attendance->id]);
        $response->assertSee($detailUrl);

        // 実際にアクセスして遷移できるか確認
        $response = $this->get($detailUrl);
        $response->assertStatus(200);
    }
}