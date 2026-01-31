<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// ID:5_ステータス確認機能のテスト
class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤務外の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_is_out_of_work_when_not_clocked_in()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /**
     * 出勤中の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_is_working_when_clocked_in()
    {
        $user = User::factory()->create();
        $today = Carbon::today()->toDateString();

        DB::table('attendances')->insert([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => '09:00',
            'status' => Attendance::STATUS_WORKING,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /**
     * 休憩中の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_is_resting_when_taking_a_break()
    {
        $user = User::factory()->create();
        $today = Carbon::today()->toDateString();

        $id = DB::table('attendances')->insertGetId([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => '09:00',
            'status' => Attendance::STATUS_RESTING,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('rest_times')->insert([
            'attendance_id' => $id,
            'start_time' => '12:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /**
     * 退勤済の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_is_finished_after_clocking_out()
    {
        $user = User::factory()->create();
        $today = Carbon::today()->toDateString();

        DB::table('attendances')->insert([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => '09:00',
            'end_time' => '18:00',
            'status' => Attendance::STATUS_RETIRED,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}