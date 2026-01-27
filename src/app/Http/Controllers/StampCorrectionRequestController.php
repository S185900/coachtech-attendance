<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    /**
     * 申請一覧画面
     */
    public function index(Request $request)
    {
        $user = Auth::user();

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

    /**
     * 申請一覧画面（管理者）
     */
    public function adminIndex(Request $request)
    {
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

    /**
     * 修正申請承認画面（管理者）
     */
    public function showApprove($attendance_correct_request_id)
    {
        $request = StampCorrectionRequest::with(['attendance', 'user'])->findOrFail($attendance_correct_request_id);

        return view('admin.stamp_correction.approve', compact('request'));
    }

    /**
     * 修正申請承認処理（管理者）
     */
    public function approve(Request $request, $id)
    {
        $correctionRequest = StampCorrectionRequest::findOrFail($id);
        $attendance = $correctionRequest->attendance;

        \DB::transaction(function () use ($correctionRequest, $attendance) {

            $attendance->update([
                'start_time' => $correctionRequest->corrected_start_time, 
                'end_time'   => $correctionRequest->corrected_end_time,
            ]);

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

            $correctionRequest->update([
                'status' => StampCorrectionRequest::STATUS_APPROVED 
            ]);
        });

        return redirect()->route('admin.stamp_correction.list', ['tab' => 'approved'])
                        ->with('success', '申請を承認しました');
    }
}
