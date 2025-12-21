<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RestTime extends Model
{
    use HasFactory;

    // テーブル名がデフォルト（rest_times）と異なる場合は指定
    protected $table = 'rest_times';

    protected $fillable = ['attendance_id', 'start_time', 'end_time'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}