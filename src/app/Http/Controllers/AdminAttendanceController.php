<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AdminApproveRequest;
use App\Models\Attendance;
use App\Models\RestTime;
use App\Models\StampCorrectionRequest;
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
        $attendance = Attendance::with(['user', 'restTimes'])->findOrFail($id);
        

        // 承認待ち(status: 0)の申請を1件取得
        $pendingRequest = $attendance->stampCorrectionRequests()
            ->where('status', 0)
            ->first();

        // 承認待ちが存在するかどうかのフラグ
        $isPending = !is_null($pendingRequest);

        return view('admin.attendance.detail', compact('attendance', 'isPending', 'pendingRequest'));
    }

    public function approve(AdminApproveRequest $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $attendance = Attendance::findOrFail($id);
            $date = Carbon::parse($attendance->date)->format('Y-m-d');

            // 1. 勤怠本体の更新
            $attendance->update([
                'start_time' => $date . ' ' . $request->start_time,
                'end_time'   => $request->end_time ? $date . ' ' . $request->end_time : null,
                'reason'     => $request->reason,
                'is_corrected' => true, // 修正済みフラグを立てる
            ]);

            // 2. 休憩時間の更新（※もし申請データ側にJSONで休憩があるならそちらを優先するロジックが必要ですが、
            // 現状のフォームから送られてくる rests を優先して更新します）
            if ($request->has('rests')) {
                foreach ($request->rests as $restId => $times) {
                    if (!empty($times['start'])) {
                        $restStartTime = $date . ' ' . $times['start'];
                        $restEndTime   = !empty($times['end']) ? ($date . ' ' . $times['end']) : null;

                        RestTime::where('id', $restId)->update([
                            'start_time' => $restStartTime,
                            'end_time'   => $restEndTime,
                        ]);
                    }
                }
            }

            // 3. 修正申請を承認済みに
            StampCorrectionRequest::where('attendance_id', $id)
                ->where('status', 0)
                ->update([
                    'status' => 1,
                    'master_id' => auth('admin')->id(), // 管理者IDをセット
                ]);
        });

        return redirect()->route('admin.attendance.list', ['date' => Attendance::find($id)->date->toDateString()])
            ->with('success', '勤怠情報を承認しました。');
    }
}
