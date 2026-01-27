<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffController extends Controller
{
    /**
     * スタッフ一覧画面（管理者）の表示
     */
    public function index()
    {
        $users = User::all();

        return view('admin.staff.list', compact('users'));
    }

    /**
     * スタッフ別勤怠一覧画面（管理者）の表示
     */
    public function staffAttendance(Request $request, $id)
    {
        $user = User::findOrFail($id);

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

    /**
     * スタッフ別勤怠一覧CSVダウンロード処理（管理者）
     */
    public function downloadCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $monthParam = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($monthParam);

        $attendances = Attendance::where('user_id', $id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->orderBy('date', 'asc')
            ->get();

        $response = new StreamedResponse(function () use ($attendances) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    \Carbon\Carbon::parse($attendance->date)->isoFormat('MM/DD(ddd)'),
                    $attendance->start_time->format('H:i'),
                    $attendance->end_time ? $attendance->end_time->format('H:i') : '',
                    $attendance->total_rest_duration,
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
