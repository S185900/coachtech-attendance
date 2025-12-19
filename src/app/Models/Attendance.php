<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    // 日付と時間は自動的にCarbonインスタンスとして扱う
    protected $dates = ['date', 'start_time', 'end_time'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 休憩時間とのリレーション（1対多）
     */
    public function breaks()
    {
        return $this->hasMany(BreakTime::class); // Breakは予約語のためBreakTime等にするのが一般的
    }
}
