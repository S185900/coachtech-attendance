<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Master;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;

    public function getStatusLabelAttribute()
    {
        return $this->status === self::STATUS_PENDING ? '承認待ち' : '承認済み';
    }

    public function getIsPendingAttribute()
    {
        return $this->status === self::STATUS_PENDING;
    }

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
        'corrected_end_time' => 'datetime',
        'status' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function master(): BelongsTo
    {
        return $this->belongsTo(Master::class, 'master_id');
    }
}
