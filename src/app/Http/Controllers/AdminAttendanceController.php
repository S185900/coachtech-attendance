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
        // ->where('status', 0) を書き換え
        ->where('status', StampCorrectionRequest::STATUS_PENDING) 
        ->first();

        $isPending = !is_null($pendingRequest);

        // --- 規約対応：Bladeからロジックをこちらへ移動 ---
        $displayRestTimes = [];
        if ($isPending && !empty($pendingRequest->corrected_rest_times)) {
            $displayRestTimes = is_string($pendingRequest->corrected_rest_times) 
                ? json_decode($pendingRequest->corrected_rest_times, true) 
                : $pendingRequest->corrected_rest_times;
        } else {
            foreach($attendance->restTimes as $rest) {
                $displayRestTimes[] = [
                    'rest_id' => $rest->id,
                    'start'   => $rest->start_time->format('H:i'),
                    'end'     => $rest->end_time ? $rest->end_time->format('H:i') : ''
                ];
            }
        }

        // 備考の表示内容も変数化
        $displayReason = $isPending ? $pendingRequest->reason : $attendance->reason;

        return view('admin.attendance.detail', compact(
            'attendance', 
            'isPending', 
            'pendingRequest', 
            'displayRestTimes', 
            'displayReason'
        ));
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

            // 既存の休憩を一旦削除
            $attendance->restTimes()->delete();

            if ($request->has('rests')) {
                foreach ($request->rests as $times) {
                    // 開始時間が入力されている場合のみ保存
                    if (!empty($times['start'])) {
                        $attendance->restTimes()->create([
                            'start_time' => $date . ' ' . $times['start'],
                            'end_time'   => !empty($times['end']) ? ($date . ' ' . $times['end']) : null,
                        ]);
                    }
                }
            }

            // approve メソッド内（最後の更新処理）
            StampCorrectionRequest::where('attendance_id', $id)
                // ->where('status', 0) を書き換え
                ->where('status', StampCorrectionRequest::STATUS_PENDING) 
                ->update([
                    // 'status' => 1 を書き換え
                    'status' => StampCorrectionRequest::STATUS_APPROVED, 
                    'master_id' => auth('admin')->id(),
                ]);
        });

        return redirect()->route('admin.attendance.list', ['date' => Attendance::find($id)->date->toDateString()])
            ->with('success', '勤怠情報を承認しました。');
    }
}
