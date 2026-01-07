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

        // 今日の最新の勤怠レコードを取得
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
            ->whereDate('date', $today)
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

    // PG04: 勤怠一覧画面
    public function list(Request $request)
    {
        // ログイン中のユーザーの勤怠データを取得
        // $attendances = Attendance::where('user_id', Auth::id())
        //     ->orderBy('date', 'desc');

        // 整理したディレクトリ構造に合わせて指定
        // return view('user.attendance.list', compact('attendances'));

        // 現在の表示月を取得（デフォルトは今月）
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentDate = Carbon::parse($month);

        $startDate = $currentDate->copy()->startOfMonth();
        $endDate = $currentDate->copy()->endOfMonth();

        // 1ヶ月分のデータを一括取得（Eager LoadingでrestTimesも取得）
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
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    // PG05: 勤怠詳細画面
    public function show($attendance_id)
    {
        $attendance = Attendance::with('restTimes')->findOrFail($attendance_id);

        // 承認待ちの申請データを取得する
        $pendingRequest = StampCorrectionRequest::where('attendance_id', $attendance_id)
            ->where('status', 0)
            ->first();

        // 申請データが存在すれば isPending は true になる
        $isPending = !is_null($pendingRequest);

        return view('user.attendance.detail', compact('attendance', 'isPending', 'pendingRequest'));
    }

    /**
     * 詳細画面からの修正申請処理
     */
    public function correctionRequest(AttendanceCorrectionRequest $request, $attendance_id)
    {
        $attendance = Attendance::findOrFail($attendance_id);
        $dateStr = Carbon::parse($attendance->date)->format('Y-m-d');

        DB::transaction(function () use ($attendance, $request, $dateStr) {
            // 休憩時間の入力値をJSON用の配列に整形
            $restTimesData = [];
            if ($request->has('rests')) {
                foreach ($request->rests as $restId => $times) {
                    $restTimesData[] = [
                        'rest_id' => $restId,
                        'start'   => $times['start'], // キーは 'start'
                        'end'     => $times['end'],   // キーは 'end'
                    ];
                }
            }

            // 修正申請レコードの作成
            StampCorrectionRequest::create([
                'user_id'              => auth()->id(),
                'attendance_id'        => $attendance->id,
                'corrected_start_time' => Carbon::parse($dateStr . ' ' . $request->start_time),
                'corrected_end_time'   => Carbon::parse($dateStr . ' ' . $request->end_time),
                'corrected_rest_times' => $restTimesData, // 整形した配列をJSONとして保存
                'reason'               => $request->reason,
                'status'               => 0, // 承認待ち
            ]);
        });

        // 勤怠一覧画面へリダイレクトし、成功メッセージを表示
        return redirect()->route('attendance.list')
            ->with('success', '修正申請を出しました。承認されるまで修正はできません。');
    }
}
