<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\RestTime;
use Carbon\Carbon;
use App\Models\StampCorrectionRequest;
use App\Http\Requests\AttendanceCorrectionRequest;

class AttendanceController extends Controller
{
    /**
     * 打刻画面（メイン画面）の表示
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        return view('user.attendance.index', compact('attendance'));
    }

    /**
     * 出勤処理
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $exists = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->exists();

        if ($exists) return redirect()->back();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => Carbon::now(),
            'status' => Attendance::STATUS_WORKING,
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
            ->whereDate('date', $today)
            ->where('status', Attendance::STATUS_WORKING)
            ->first();

        if (!$attendance) return redirect()->back();

        $attendance->update([
            'end_time' => Carbon::now(),
            'status' => Attendance::STATUS_FINISHED,
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

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->where('status', Attendance::STATUS_WORKING)
            ->first();

        if (!$attendance) return redirect()->back();

        RestTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now(),
        ]);

        $attendance->update(['status' => Attendance::STATUS_RESTING]);

        return redirect()->back();
    }

    /**
     * 休憩終了処理
     */
    public function restEnd(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->where('status', Attendance::STATUS_RESTING)
            ->first();

        if (!$attendance) return redirect()->back();

        $restTime = RestTime::where('attendance_id', $attendance->id)
            ->whereNull('end_time')
            ->latest()
            ->first();

        if ($restTime) {
            $restTime->update(['end_time' => Carbon::now()]);
        }

        $attendance->update(['status' => Attendance::STATUS_WORKING]);

        return redirect()->back();
    }

    /**
     * 勤怠一覧画面
     */
    public function list(Request $request)
    {
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentDate = Carbon::parse($month);

        $startDate = $currentDate->copy()->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        $attendances = Attendance::with('restTimes')
            ->where('user_id', auth()->id())
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->keyBy(function($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        return view('user.attendance.list', [
            'attendances' => $attendances,
            'displayMonth' => $currentDate->format('Y/m'),
            'prevMonth' => $currentDate->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $currentDate->copy()->addMonth()->format('Y-m'),
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * 勤怠詳細画面
     */
    public function show($id)
    {
        $attendance = Attendance::with('restTimes')->findOrFail($id);

        $pendingRequest = StampCorrectionRequest::where('attendance_id', $id)
            ->where('status', StampCorrectionRequest::STATUS_PENDING)
            ->first();

        $isPending = !is_null($pendingRequest);
        $displayRestTimes = [];

        if ($isPending && !empty($pendingRequest->corrected_rest_times)) {

            $correctedRests = is_string($pendingRequest->corrected_rest_times)
                ? json_decode($pendingRequest->corrected_rest_times, true)
                : ($pendingRequest->corrected_rest_times ?? []);

            foreach ($correctedRests as $rest) {
                if (!empty($rest['start'])) {
                    $displayRestTimes[] = [
                        'key'   => $rest['rest_id'] ?? 'new',
                        'start' => $rest['start'],
                        'end'   => $rest['end'] ?? ''
                    ];
                }
            }
            $displayReason = $pendingRequest->reason;

        } else {

            foreach($attendance->restTimes as $restTime) {
                $displayRestTimes[] = [
                    'key'   => $restTime->id,
                    'start' => $restTime->start_time->format('H:i'),
                    'end'   => $restTime->end_time ? $restTime->end_time->format('H:i') : ''
                ];
            }
            $displayReason = $attendance->reason;
        }

        return view('user.attendance.detail', compact(
            'attendance',
            'isPending',
            'pendingRequest',
            'displayRestTimes',
            'displayReason'
        ));
    }

    /**
     * 勤怠修正申請処理
     */
    public function correctionRequest(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $dateString = Carbon::parse($attendance->date)->format('Y-m-d');

        DB::transaction(function () use ($attendance, $request, $dateString) {

            $restTimesData = [];
            if ($request->has('rests')) {
                foreach ($request->rests as $restId => $times) {
                    $restTimesData[] = [
                        'rest_id' => $restId,
                        'start' => $times['start'],
                        'end' => $times['end'],
                    ];
                }
            }

            StampCorrectionRequest::create([
                'user_id' => auth()->id(),
                'attendance_id' => $attendance->id,
                'corrected_start_time' => Carbon::parse($dateString . ' ' . $request->start_time),
                'corrected_end_time' => Carbon::parse($dateString . ' ' . $request->end_time),
                'corrected_rest_times' => $restTimesData,
                'reason' => $request->reason,
                'status' => StampCorrectionRequest::STATUS_PENDING,
            ]);
        });

        return redirect()->route('attendance.list')
            ->with('success', '修正申請を出しました。承認されるまで修正はできません。');
    }
}
