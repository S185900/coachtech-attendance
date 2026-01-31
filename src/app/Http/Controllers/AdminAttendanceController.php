<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AdminApproveRequest;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    /**
     * 勤怠一覧画面（管理者）の表示
     */
    public function index(Request $request)
    {
        $dateString = $request->query('date', Carbon::today()->toDateString());
        $currentDate = Carbon::parse($dateString);

        $prevDate = $currentDate->copy()->subDay()->toDateString();
        $nextDate = $currentDate->copy()->addDay()->toDateString();

        $attendances = Attendance::with(['user', 'restTimes'])
            ->whereDate('date', $currentDate->toDateString())
            ->get();

        return view('admin.attendance.list', compact('attendances', 'currentDate', 'prevDate', 'nextDate'));
    }

    /**
     * 勤怠詳細画面（管理者）の表示
     */
    public function showAttendanceDetail($id)
    {
        $attendance = Attendance::with(['user', 'restTimes'])->findOrFail($id);

        $pendingRequest = $attendance->stampCorrectionRequests()
            ->where('status', StampCorrectionRequest::STATUS_PENDING) 
            ->first();

        $isPending = !is_null($pendingRequest);

        $displayRestTimes = [];
        if ($isPending && !empty($pendingRequest->corrected_rest_times)) {
            $displayRestTimes = is_string($pendingRequest->corrected_rest_times) 
                ? json_decode($pendingRequest->corrected_rest_times, true) 
                : $pendingRequest->corrected_rest_times;
        } else {
            foreach($attendance->restTimes as $restTime) {
                $displayRestTimes[] = [
                    'rest_id' => $restTime->id,
                    'start'   => $restTime->start_time->format('H:i'),
                    'end'     => $restTime->end_time ? $restTime->end_time->format('H:i') : ''
                ];
            }
        }

        $displayReason = $isPending ? $pendingRequest->reason : $attendance->reason;

        return view('admin.attendance.detail', compact(
            'attendance', 
            'isPending', 
            'pendingRequest', 
            'displayRestTimes', 
            'displayReason'
        ));
    }

    /**
     * 勤怠修正承認処理（管理者）
     */
    public function approve(AdminApproveRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        DB::transaction(function () use ($request, $attendance) {
            $date = Carbon::parse($attendance->date)->format('Y-m-d');

            $attendance->update([
                'start_time' => $date . ' ' . $request->start_time,
                'end_time'   => $request->end_time ? $date . ' ' . $request->end_time : null,
                'reason'     => $request->reason,
                'is_corrected' => true,
            ]);

            $attendance->restTimes()->delete();

            if ($request->has('rests')) {
                foreach ($request->rests as $restTimeData) {
                    if (!empty($restTimeData['start'])) {
                        $attendance->restTimes()->create([
                            'start_time' => $date . ' ' . $restTimeData['start'],
                            'end_time'   => !empty($restTimeData['end']) ? ($date . ' ' . $restTimeData['end']) : null,
                        ]);
                    }
                }
            }

            StampCorrectionRequest::where('attendance_id', $attendance->id)
                ->where('status', StampCorrectionRequest::STATUS_PENDING) 
                ->update([
                    'status' => StampCorrectionRequest::STATUS_APPROVED, 
                    'master_id' => auth('admin')->id(),
                ]);
        });

        return redirect()->route('admin.attendance.list', ['date' => $attendance->date->toDateString()])
            ->with('success', '勤怠情報を承認しました。');
    }
}
