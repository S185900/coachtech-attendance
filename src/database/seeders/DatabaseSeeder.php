<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;   // 一般ユーザー用モデル
use App\Models\Master; // 管理者ユーザー用モデル（作成している場合）
use App\Models\Attendance;
use App\Models\RestTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // --- 1. 管理者ユーザーの作成 ---
        Master::create([
            'name' => '管理者 太郎',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        // --- 2. 一般ユーザーの作成 ---
        // ここで $user 変数に代入していることを確認してください
        $user = User::create([
            'name' => 'テスト スタッフ',
            'email' => 'user@gmail.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // --- 3. 勤怠・休憩データの作成（昨日から遡って5日分） ---
        // $i = 1 にすることで、「今日」を飛ばして「昨日」から作成します
        for ($i = 1; $i <= 5; $i++) {
            $date = Carbon::today()->subDays($i);

            // 勤怠レコード作成
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $date->format('Y-m-d'),
                'start_time' => $date->copy()->setTime(9, 0, 0), 
                'end_time' => $date->copy()->setTime(18, 0, 0),  
                'status' => 3, // 3: 退勤済
                'is_corrected' => false,
            ]);

            // 休憩レコード作成
            RestTime::create([
                'attendance_id' => $attendance->id,
                'start_time' => $date->copy()->setTime(12, 0, 0), 
                'end_time' => $date->copy()->setTime(13, 0, 0),   
            ]);
        }
    }
}
