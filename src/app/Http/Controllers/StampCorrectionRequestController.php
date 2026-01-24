<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 三項演算子の数字を書き換え
        $status = $request->query('tab') === 'approved' 
            ? StampCorrectionRequest::STATUS_APPROVED 
            : StampCorrectionRequest::STATUS_PENDING;

        $requests = StampCorrectionRequest::with('attendance')
            ->where('user_id', $user->id)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.stamp_correction_request.list', [
            'requests' => $requests,
            'tab' => $request->query('tab', 'pending')
        ]);
    }

    public function adminIndex(Request $request)
    {
        // 同様に数字を書き換え
        $status = $request->query('tab') === 'approved' 
            ? StampCorrectionRequest::STATUS_APPROVED 
            : StampCorrectionRequest::STATUS_PENDING;

        $requests = StampCorrectionRequest::with(['attendance', 'user'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.stamp_correction.list', [
            'requests' => $requests,
            'tab' => $request->query('tab', 'pending')
        ]);
    }

    // 承認画面の表示用
    public function showApprove($attendance_correct_request_id)
    {
        $request = StampCorrectionRequest::with(['attendance', 'user'])->findOrFail($attendance_correct_request_id);

        return view('admin.stamp_correction.approve', compact('request'));
    }

    public function approve(Request $request, $id)
    {
        $correctionRequest = StampCorrectionRequest::findOrFail($id);
        $attendance = $correctionRequest->attendance;

        // トランザクション開始（すべての更新が成功するか、失敗したら全部戻す）
        \DB::transaction(function () use ($correctionRequest, $attendance) {
            
            // 1. 勤怠レコード（出勤・退勤）を更新
            $attendance->update([
                'start_time' => $correctionRequest->corrected_start_time, 
                'end_time'   => $correctionRequest->corrected_end_time,
            ]);

            // 2. 休憩時間の更新（ここが重要！）
            // 現在の休憩データを一度消して、申請内容を登録し直す
            $attendance->restTimes()->delete();

            $restTimes = is_string($correctionRequest->corrected_rest_times) 
                ? json_decode($correctionRequest->corrected_rest_times, true) 
                : $correctionRequest->corrected_rest_times;

            if (!empty($restTimes)) {
                foreach ($restTimes as $rest) {
                    $attendance->restTimes()->create([
                        'start_time' => $rest['start'],
                        'end_time'   => $rest['end'],
                    ]);
                }
            }

            // 3. 申請ステータスを承認済み(1)に変更
            // ...トランザクション内
            $correctionRequest->update([
                // 'status' => 1 を書き換え
                'status' => StampCorrectionRequest::STATUS_APPROVED 
            ]);
        });

        return redirect()->route('admin.stamp_correction.list', ['tab' => 'approved'])
                        ->with('success', '申請を承認しました');
    }
}
