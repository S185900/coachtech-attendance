<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
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

    public function downloadCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $monthParam = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($monthParam);

        // 当該月の勤怠データを取得
        $attendances = Attendance::where('user_id', $id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->orderBy('date', 'asc')
            ->get();

        // CSV生成
        $response = new StreamedResponse(function () use ($attendances) {
            $handle = fopen('php://output', 'w');

            // 文字化け対策（Excelで開く場合）
            fwrite($handle, "\xEF\xBB\xBF");

            // ヘッダー行
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            // データ行
            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    \Carbon\Carbon::parse($attendance->date)->isoFormat('MM/DD(ddd)'),
                    $attendance->start_time->format('H:i'),
                    $attendance->end_time ? $attendance->end_time->format('H:i') : '',
                    $attendance->total_rest_duration, // total_rest_time から修正
                    $attendance->work_time
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $user->name . 'さんの勤怠_' . $currentMonth->format('Ym') . '.csv"',
        ]);

        return $response;
    }
}
