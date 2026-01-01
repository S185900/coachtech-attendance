<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffController extends Controller
{
    public function index()
    {
        // 一般ユーザー（管理者以外）の一覧を取得
        // ※もしUserモデルに管理者フラグ等があれば、ここでフィルタリングします
        $users = User::all();

        return view('admin.staff.list', compact('users'));
    }

    // スタッフ別勤怠一覧
    // ★ ここを修正： (Request $request, $id) に書き換えます
    public function staffAttendance(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // これで $request が使えるようになります
        $monthParam = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($monthParam)->startOfMonth();

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::where('user_id', $id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->orderBy('date', 'asc')
            ->get();

        return view('admin.staff.attendance', compact(
            'user', 
            'attendances', 
            'currentMonth', 
            'prevMonth', 
            'nextMonth'
        ));
    }
}
