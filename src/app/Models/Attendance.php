<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'is_corrected',
    ];

    // Laravel 8では$datesに登録することで自動的にCarbonインスタンス化されます
    protected $dates = ['date', 'start_time', 'end_time'];

    public function user()
    {
        // 第2引数を省略している場合、user_id カラムを探しに行きます
        return $this->belongsTo(User::class);
    }

    public function restTimes()
    {
        return $this->hasMany(RestTime::class);
    }

    /**
     * 合計休憩時間 (H:i形式)
     */
    public function getTotalRestTimeAttribute()
    {
        $totalMinutes = 0;
        foreach ($this->restTimes as $rest) {
            // start_time, end_timeがCarbonインスタンスであることを前提にdiffを実行
            if ($rest->start_time && $rest->end_time) {
                // RestTimeモデル側でも$dates設定が必要です
                $totalMinutes += Carbon::parse($rest->start_time)->diffInMinutes(Carbon::parse($rest->end_time));
            }
        }
        return sprintf('%d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);
    }

    /**
     * 合計勤務時間 (H:i形式)
     */
    public function getWorkTimeAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return '';
        }

        // 拘束時間（分）
        $totalDuration = $this->start_time->diffInMinutes($this->end_time);
        
        // 休憩時間（分）を取得（上記アクセサのロジックを再利用）
        $restMinutes = 0;
        foreach ($this->restTimes as $rest) {
            if ($rest->start_time && $rest->end_time) {
                $restMinutes += Carbon::parse($rest->start_time)->diffInMinutes(Carbon::parse($rest->end_time));
            }
        }

        $workMinutes = $totalDuration - $restMinutes;
        return sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60);
    }

    // 休憩時間の合計（例：01:00）を返すアクセサ
    public function getTotalRestDurationAttribute()
    {
        $totalMinutes = 0;
        foreach ($this->restTimes as $rest) {
            if ($rest->end_time) {
                $totalMinutes += $rest->start_time->diffInMinutes($rest->end_time);
            }
        }
        return sprintf('%d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);
    }
}
