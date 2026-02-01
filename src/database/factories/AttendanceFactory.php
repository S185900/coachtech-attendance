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
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = Carbon::today();
        $startTime = Carbon::create($date->year, $date->month, $date->day, 9, 0, 0);
        $endTime = Carbon::create($date->year, $date->month, $date->day, 18, 0, 0);

        return [
            'user_id' => User::factory(), 
            'date' => $date->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => Attendance::STATUS_FINISHED,
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
                'status' => Attendance::STATUS_WORKING,
            ];
        });
    }
}
