<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    const STATUS_RETIRED = 0;
    const STATUS_WORKING = 1;
    const STATUS_RESTING = 2;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'is_corrected',
    ];

    protected $dates = ['date', 'start_time', 'end_time'];

    public function user()
    {
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

    public function getTotalRestTimeAttribute()
    {
        $totalMinutes = 0;
        foreach ($this->restTimes as $rest) {
            if ($rest->start_time && $rest->end_time) {
                $start = Carbon::parse($rest->start_time);
                $end = Carbon::parse($rest->end_time);
                $totalMinutes += $start->diffInMinutes($end);
            }
        }

        return sprintf('%02d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);
    }

    public function getWorkTimeAttribute()
    {
        if (!$this->start_time || !$this->end_time) {
            return '';
        }

        $totalDurationMinutes = $this->start_time->diffInMinutes($this->end_time);

        $restMinutes = 0;
        foreach ($this->restTimes as $rest) {
            if ($rest->start_time && $rest->end_time) {
                $restMinutes += Carbon::parse($rest->start_time)->diffInMinutes(Carbon::parse($rest->end_time));
            }
        }

        $workMinutes = $totalDurationMinutes - $restMinutes;

        if ($workMinutes < 0) $workMinutes = 0;

        return sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);
    }
}
