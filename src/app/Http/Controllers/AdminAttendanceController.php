<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // クエリパラメータ 'date' があればそれを使用、なければ今日の日付
        $dateString = $request->query('date', Carbon::today()->toDateString());
        $currentDate = Carbon::parse($dateString);

        // 前日・翌日の日付を取得
        $prevDate = $currentDate->copy()->subDay()->toDateString();
        $nextDate = $currentDate->copy()->addDay()->toDateString();

        // 指定した日付の全ユーザーの勤怠データを取得（User, RestTimeをEager Load）
        $attendances = Attendance::with(['user', 'restTimes'])
            ->whereDate('date', $currentDate->toDateString())
            ->get();

        return view('admin.attendance.list', compact('attendances', 'currentDate', 'prevDate', 'nextDate'));
    }

    public function showDetail($id)
    {
        // 指定されたIDの勤怠データを取得
        $attendance = Attendance::with(['user', 'restTimes'])->findOrFail($id);

        // 承認待ちの申請があるか確認（必要に応じて）
        $isPending = $attendance->stampCorrectionRequests()->where('status', 0)->exists();

        return view('admin.attendance.detail', compact('attendance', 'isPending'));
    }
}
