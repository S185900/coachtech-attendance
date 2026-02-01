<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Master;
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
        Master::create([
            'name' => '管理者 太郎',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        $user = User::create([
            'name' => 'テスト スタッフ',
            'email' => 'user@gmail.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        for ($i = 1; $i <= 5; $i++) {
            $date = Carbon::today()->subDays($i);

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $date->format('Y-m-d'),
                'start_time' => $date->copy()->setTime(9, 0, 0),
                'end_time' => $date->copy()->setTime(18, 0, 0),
                'status' => Attendance::STATUS_FINISHED,
                'is_corrected' => false,
            ]);

            RestTime::create([
                'attendance_id' => $attendance->id,
                'start_time' => $date->copy()->setTime(12, 0, 0),
                'end_time' => $date->copy()->setTime(13, 0, 0),
            ]);
        }
    }
}
