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
    public function index(Request $request)
    {
        $dateString = $request->query('date', Carbon::today()->toDateString());
        $currentDate = Carbon::parse($dateString);

        $prevDate = $currentDate->copy()->subDay()->toDateString();
        $nextDate = $currentDate->copy()->addDay()->toDateString();

        $attendances = Attendance::with(['user', 'restTimes'])
            ->whereDate('date', $currentDate->toDateString())
            ->get()
            ->map(function ($attendance) {
                $attendance->display_total_rest_time = preg_replace('/^0/', '', $attendance->total_rest_time);
                $attendance->display_work_time = preg_replace('/^0/', '', $attendance->work_time);
                return $attendance;
            });

        return view('admin.attendance.list', compact('attendances', 'currentDate', 'prevDate', 'nextDate'));
    }

    public function showAttendanceDetail($id)
    {
        $attendance = Attendance::with(['user', 'restTimes'])->findOrFail($id);

        $pendingRequest = $attendance->stampCorrectionRequests()
            ->where('status', StampCorrectionRequest::STATUS_PENDING)
            ->first();

        $isPending = !is_null($pendingRequest);

        $displayRestTimes = [];
        if ($isPending && !empty($pendingRequest->corrected_rest_times)) {

            $decodedRestTimes = is_string($pendingRequest->corrected_rest_times)
                ? json_decode($pendingRequest->corrected_rest_times, true)
                : $pendingRequest->corrected_rest_times;

            $displayRestTimes = array_values(array_filter($decodedRestTimes ?: [], function($rest) {
                return !empty($rest['start']) || !empty($rest['end']);
            }));

        } else {
            foreach($attendance->restTimes as $restTime) {
                $displayRestTimes[] = [
                    'rest_id' => $restTime->id,
                    'start'   => $restTime->start_time->format('H:i'),
                    'end'     => $restTime->end_time ? $restTime->end_time->format('H:i') : ''
                ];
            }
        }

        if ($isPending) {
            $displayReason = $pendingRequest->reason;
        } else {
            $displayReason = '';
        }

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
        $attendance = Attendance::findOrFail($id);
        $pendingRequest = $attendance->stampCorrectionRequests()
            ->where('status', StampCorrectionRequest::STATUS_PENDING)
            ->first();

        DB::transaction(function () use ($request, $attendance, $pendingRequest) {
            $date = Carbon::parse($attendance->date)->format('Y-m-d');

            if ($pendingRequest) {
                $startTime = $pendingRequest->corrected_start_time;
                $endTime = $pendingRequest->corrected_end_time;
                $reason = $pendingRequest->reason;
                $restTimes = is_string($pendingRequest->corrected_rest_times) 
                    ? json_decode($pendingRequest->corrected_rest_times, true) 
                    : $pendingRequest->corrected_rest_times;
            } else {
                $startTime = $date . ' ' . $request->start_time;
                $endTime = $request->end_time ? $date . ' ' . $request->end_time : null;
                $reason = $request->reason;
                $restTimes = $request->rests;
            }

            $attendance->update([
                'start_time' => $startTime,
                'end_time' => $endTime,
                'reason' => $reason,
                'is_corrected' => true,
            ]);

            $attendance->restTimes()->delete();
            if (!empty($restTimes)) {
                foreach ($restTimes as $rest) {
                    if (!empty($rest['start'])) {
                        $attendance->restTimes()->create([
                            'start_time' => $pendingRequest ? $date . ' ' . $rest['start'] : $date . ' ' . $rest['start'],
                            'end_time'   => !empty($rest['end']) ? $date . ' ' . $rest['end'] : null,
                        ]);
                    }
                }
            }

            if ($pendingRequest) {
                $pendingRequest->update([
                    'status' => StampCorrectionRequest::STATUS_APPROVED,
                    'master_id' => auth('admin')->id(),
                ]);
            }
        });

        return redirect()->route('admin.attendance.list', ['date' => $attendance->date->toDateString()])
            ->with('success', '勤怠情報を承認しました。');
    }
}
