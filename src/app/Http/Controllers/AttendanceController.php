<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\RestTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * 打刻画面（メイン画面）の表示
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // 今日の最新の勤怠レコードを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        return view('user.index', compact('attendance'));
    }

    /**
     * 出勤処理
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $exists = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->exists();

        if ($exists) return redirect()->back();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => Carbon::now(),
            'status' => 1, // 出勤中
        ]);

        return redirect()->back();
    }

    /**
     * 退勤処理
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->where('status', 1) // 出勤中のみ退勤可能
            ->first();

        if (!$attendance) return redirect()->back();

        $attendance->update([
            'end_time' => Carbon::now(),
            'status' => 0, // 退勤済
        ]);

        return redirect()->back();
    }

    /**
     * 休憩開始処理
     */
    public function restStart(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // 今日の「勤務中」のレコードを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->where('status', 1)
            ->first();

        if (!$attendance) return redirect()->back();

        // 1. 休憩テーブルに開始時間を記録
        RestTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now(),
        ]);

        // 2. 勤務状態を「休憩中(2)」に更新
        $attendance->update(['status' => 2]);

        return redirect()->back();
    }

    /**
     * 休憩終了処理
     */
    public function restEnd(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // 今日の「休憩中」のレコードを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->where('status', 2)
            ->first();

        if (!$attendance) return redirect()->back();

        // 1. 休憩テーブルの最新レコード（end_timeが空のもの）を更新
        $restTime = RestTime::where('attendance_id', $attendance->id)
            ->whereNull('end_time')
            ->latest()
            ->first();

        if ($restTime) {
            $restTime->update(['end_time' => Carbon::now()]);
        }

        // 2. 勤務状態を「勤務中(1)」に戻す
        $attendance->update(['status' => 1]);

        return redirect()->back();
    }
}
