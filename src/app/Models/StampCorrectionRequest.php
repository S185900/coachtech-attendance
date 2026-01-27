<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'corrected_rest_times',
        'corrected_start_time',
        'corrected_end_time',
        'reason',
        'status',
        'master_id',
        'master_comment',
    ];

    protected $casts = [
        'corrected_rest_times' => 'array',
        'corrected_start_time' => 'datetime',
        'corrected_end_time'   => 'datetime',
        'status'               => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function master()
    {
        return $this->belongsTo(Master::class, 'master_id');
    }
}
