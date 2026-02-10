<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    const STATUS_OUT_OF_WORK = 0;
    const STATUS_WORKING = 1;
    const STATUS_RESTING = 2;
    const STATUS_FINISHED = 3;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'is_corrected',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_corrected' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restTimes(): HasMany
    {
        return $this->hasMany(RestTime::class);
    }

    public function stampCorrectionRequests(): HasMany
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    public function getTotalRestTimeAttribute()
    {
        $totalMinutes = 0;
        foreach ($this->restTimes as $restTime) {
            if ($restTime->start_time && $restTime->end_time) {
                $start = Carbon::parse($restTime->start_time);
                $end = Carbon::parse($restTime->end_time);
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
        foreach ($this->restTimes as $restTime) {
            if ($restTime->start_time && $restTime->end_time) {
                $restMinutes += Carbon::parse($restTime->start_time)->diffInMinutes(Carbon::parse($restTime->end_time));
            }
        }

        $workMinutes = $totalDurationMinutes - $restMinutes;

        if ($workMinutes < 0) $workMinutes = 0;

        return sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);
    }
}
