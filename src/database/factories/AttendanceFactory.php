<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attendance.class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // テスト用の日付（今日）を基準に時間を設定
        $date = Carbon::today();
        $startTime = Carbon::create($date->year, $date->month, $date->day, 9, 0, 0); // 09:00
        $endTime = Carbon::create($date->year, $date->month, $date->day, 18, 0, 0);  // 18:00

        return [
            // UserFactoryと連携して自動的にユーザーを作成し、そのIDをセット
            'user_id' => User::factory(), 
            'date' => $date->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            // 0:勤務外, 1:勤務中, 2:休憩中, 3:退勤済 (マイグレーションの定義に準拠)
            'status' => 3, 
            'is_corrected' => false,
        ];
    }

    /**
     * 状態指定：勤務中のデータを作成したい場合
     */
    public function working()
    {
        return $this->state(function (array $attributes) {
            return [
                'end_time' => null,
                'status' => 1,
            ];
        });
    }
}
