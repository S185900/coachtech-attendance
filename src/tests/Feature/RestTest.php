<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\RestTime;
use Carbon\Carbon;

class RestTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * 休憩ボタンが正しく機能する
     */
    public function test_rest_start_button_works_correctly()
    {
        // 1. ステータスが出勤中のユーザーを作成し、ログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤レコードを作成（ステータス1: 出勤中）
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::now()->subHour(),
            'status' => 1,
        ]);

        // 2. 画面に「休憩入」ボタンが表示されていることを確認する
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // 3. 休憩の処理を行う（POSTリクエスト）
        $this->post('/attendance/rest-start');

        // 処理後に画面上のステータスが「休憩中」になることを確認する
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /**
     * 休憩は一日に何回でもできる
     */
    public function test_can_rest_multiple_times_a_day()
    {
        // 1. ステータスが出勤中であるユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::now()->subHours(2),
            'status' => 1,
        ]);

        // 2. 休憩入と休憩戻の処理を行う（1回目）
        $this->post('/attendance/rest-start');
        $this->post('/attendance/rest-end');

        // 3. 「休憩入」ボタンが再度表示されることを確認する
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    /**
     * 休憩戻ボタンが正しく機能する
     */
    public function test_rest_end_button_works_correctly()
    {
        // 1. ステータスが出勤中であるユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::now()->subHour(),
            'status' => 1,
        ]);

        // 2. 休憩入の処理を行う
        $this->post('/attendance/rest-start');
        
        // 休憩戻ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        // 3. 休憩戻の処理を行う
        $this->post('/attendance/rest-end');

        // 処理後にステータスが「出勤中」に変更されることを確認
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * 休憩戻は一日に何回でもできる
     */
    public function test_can_rest_end_multiple_times_a_day()
    {
        // 1. ステータスが出勤中であるユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'start_time' => Carbon::now()->subHours(3),
            'status' => 1,
        ]);

        // 2. 休憩入と休憩戻の処理を行い、再度休憩入の処理を行う
        $this->post('/attendance/rest-start'); // 1回目入
        $this->post('/attendance/rest-end');   // 1回目戻
        $this->post('/attendance/rest-start'); // 2回目入

        // 3. 「休憩戻」ボタンが表示されることを確認する
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /**
     * 休憩時間が勤怠一覧画面で確認できる（一般ユーザー・管理者両方）
     */
    public function test_rest_times_are_visible_on_list_pages_for_both_roles()
    {
        // 1. 一般ユーザーと勤怠データの準備
        // email_verified_atをセットして、ルートの'verified'ミドルウェアを通過させます
        $user = \App\Models\User::factory()->create([
            'email_verified_at' => now(),
        ]);
        
        $today = \Carbon\Carbon::create(2026, 1, 9); // 今日付で固定
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => $today->toDateString(),
            'start_time' => $today->copy()->setTime(9, 0),
            'end_time' => $today->copy()->setTime(18, 0),
            'status' => 0,
        ]);

        \App\Models\RestTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        // --- パターンA: 一般ユーザーとしてログインして一覧を確認 ---
        $this->actingAs($user, 'web'); 
        $userResponse = $this->get('/attendance/list?month=' . $today->format('Y-m'));
        
        $userResponse->assertStatus(200);
        // ここで落ちる場合は、Viewに {{ $attendance->total_rest_duration }} があるか確認！
        $userResponse->assertSee('01:00'); 

        // --- パターンB: 管理者(Master)としてログインして一覧を確認 ---
        // roleカラムを使わず、mastersテーブル用のモデルを作成します
        // Factoryがない場合は \App\Models\Master::create([...]) に書き換えてください
        $master = \App\Models\Master::factory()->create(); 
        
        // ルート定義の auth:admin に合わせガード 'admin' を指定
        $this->actingAs($master, 'admin'); 
        
        // 管理者側の勤怠一覧URL
        $adminResponse = $this->get('/admin/attendance/list?date=' . $today->toDateString());
        
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee($user->name); // 管理者画面なのでスタッフ名が出ているか
        $adminResponse->assertSee('01:00');     // 休憩合計
    }
}