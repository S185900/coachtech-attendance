<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

// ID:15_勤怠情報修正機能（管理者）のテスト
class AdminStampCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['name' => '管理者']);
        $this->staff = User::factory()->create(['name' => 'テストスタッフ']);

        $this->attendance = Attendance::create([
            'user_id' => $this->staff->id,
            'date' => '2026-01-11',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);
    }

    /**
     * 承認待ちの修正申請が全て表示されている
     */
    public function test_admin_can_see_pending_requests()
    {
        StampCorrectionRequest::create([
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->staff->id,
            'corrected_start_time' => '08:30:00',
            'corrected_end_time' => '18:30:00',
            'reason' => '電車遅延のため',
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin, 'admin');
        $response = $this->get(route('admin.stamp_correction.list', ['tab' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('テストスタッフ');
        $response->assertSee('2026/01/11');
        $response->assertSee('電車遅延のため');
    }

    /**
     * 承認済みの修正申請が全て表示されている
     */
    public function test_admin_can_see_approved_requests()
    {
        StampCorrectionRequest::create([
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->staff->id,
            'corrected_start_time' => '08:00:00',
            'corrected_end_time' => '17:00:00',
            'reason' => '早退のため',
            'status' => StampCorrectionRequest::STATUS_APPROVED,
        ]);

        $this->actingAs($this->admin, 'admin');
        $response = $this->get(route('admin.stamp_correction.list', ['tab' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('テストスタッフ');
        $response->assertSee('早退のため');
    }

    /**
     * 修正申請の詳細内容が正しく表示されている
     */
    public function test_admin_can_see_request_detail()
    {
        $request = StampCorrectionRequest::create([
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->staff->id,
            'corrected_start_time' => '08:45:00',
            'corrected_end_time' => '18:15:00',
            'reason' => '打刻忘れ修正',
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin, 'admin');
        $response = $this->get(route('admin.stamp_correction.approve', ['attendance_correct_request_id' => $request->id]));

        $response->assertStatus(200);
        $response->assertSee('テストスタッフ');
        $response->assertSee('08:45');
        $response->assertSee('18:15');
        $response->assertSee('打刻忘れ修正');
    }

    /**
     * 修正申請の承認処理が正しく行われる
     */
    public function test_admin_approval_process_updates_attendance()
    {
        $request = StampCorrectionRequest::create([
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->staff->id,
            'corrected_start_time' => '08:00:00',
            'corrected_end_time' => '17:00:00',
            'reason' => '承認テスト',
            'status' => StampCorrectionRequest::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin, 'admin');

        $response = $this->post(route('admin.stamp_correction.update', ['id' => $request->id]));

        $response->assertStatus(302);

        $this->assertEquals(1, $request->fresh()->status);

        $attendance = $this->attendance->fresh();

        $this->assertEquals('08:00:00', Carbon::parse($attendance->start_time)->format('H:i:s'));
        $this->assertEquals('17:00:00', Carbon::parse($attendance->end_time)->format('H:i:s'));
    }
}