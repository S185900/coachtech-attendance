<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. 勤務外
     */
    public function test_status_is_out_of_work_when_not_clocked_in()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /**
     * 2. 出勤中 (status = 1)
     */
    public function test_status_is_working_when_clocked_in()
    {
        $user = User::factory()->create();
        $today = Carbon::today()->toDateString();

        DB::table('attendances')->insert([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => '09:00',
            'status' => 1, // ここが重要：出勤中
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /**
     * 3. 休憩中 (status = 2)
     */
    public function test_status_is_resting_when_taking_a_break()
    {
        $user = User::factory()->create();
        $today = Carbon::today()->toDateString();

        $attendanceId = DB::table('attendances')->insertGetId([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => '09:00',
            'status' => 2, // ここが重要：休憩中
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('rest_times')->insert([
            'attendance_id' => $attendanceId,
            'start_time' => '12:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /**
     * 4. 退勤済 (status = 0)
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
            'status' => 0, // ここが重要：退勤済
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}