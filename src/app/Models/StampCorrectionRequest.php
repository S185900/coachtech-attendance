<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'corrected_start_time',
        'corrected_end_time',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'corrected_rest_times' => 'array',
        'reason',
        'status',
        'master_id',
        'master_comment',
    ];

    protected $dates = ['corrected_start_time', 'corrected_end_time'];

    // 申請したユーザー
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 対象の勤怠データ
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // 承認した管理者
    public function master()
    {
        return $this->belongsTo(Master::class, 'master_id');
    }
}
