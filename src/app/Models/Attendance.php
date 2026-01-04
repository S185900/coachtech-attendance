<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StampCorrectionRequest;
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

    public function stampCorrectionRequests()
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    /**
     * アクセサ：合計休憩時間（HH:mm 形式）
     * 画面で $attendance->total_rest_duration として呼び出す
     */
    public function getTotalRestDurationAttribute()
    {
        $totalMinutes = 0;
        foreach ($this->restTimes as $rest) {
            // 開始と終了が両方揃っている場合のみ計算
            if ($rest->start_time && $rest->end_time) {
                $start = Carbon::parse($rest->start_time);
                $end = Carbon::parse($rest->end_time);
                $totalMinutes += $start->diffInMinutes($end);
            }
        }

        // 時間と分に分解して、0埋めした文字列（01:05など）を返す
        return sprintf('%02d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);
    }

    /**
     * アクセサ：合計勤務時間（HH:mm 形式）
     * 拘束時間（出勤〜退勤）から、休憩時間の合計を引いて算出
     * 画面で $attendance->work_time として呼び出す
     */
    public function getWorkTimeAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return '';
        }

        // 1. 拘束時間（分）を計算
        $totalDurationMinutes = $this->start_time->diffInMinutes($this->end_time);

        // 2. 休憩時間（分）を計算
        $restMinutes = 0;
        foreach ($this->restTimes as $rest) {
            if ($rest->start_time && $rest->end_time) {
                $restMinutes += Carbon::parse($rest->start_time)->diffInMinutes(Carbon::parse($rest->end_time));
            }
        }

        // 3. 勤務時間 = 拘束時間 - 休憩時間
        $workMinutes = $totalDurationMinutes - $restMinutes;

        // マイナスにならないように調整（念のため）
        if ($workMinutes < 0) $workMinutes = 0;

        return sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);
    }
}
