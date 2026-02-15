<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (Auth::guard('admin')->check()) {
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

    public function showApprove($attendanceCorrectRequestId)
    {
        $request = StampCorrectionRequest::with(['attendance', 'user'])
            ->findOrFail($attendanceCorrectRequestId);

        $isPending = ($request->status === StampCorrectionRequest::STATUS_PENDING);

        $rawRestTimes = is_string($request->corrected_rest_times)
            ? json_decode($request->corrected_rest_times, true)
            : $request->corrected_rest_times;

        $request->active_rest_times = array_values(array_filter($rawRestTimes ?? [], function($rest) {
            return !empty($rest['start']);
        }));

        return view('admin.stamp_correction.approve', compact('request', 'isPending'));
    }

    public function approve(Request $request, $attendanceCorrectRequestId)
    {
        $correctionRequest = StampCorrectionRequest::findOrFail($attendanceCorrectRequestId);
        $attendance = $correctionRequest->attendance;

        \DB::transaction(function () use ($correctionRequest, $attendance) {

            $attendance->update([
                'start_time' => $correctionRequest->corrected_start_time,
                'end_time' => $correctionRequest->corrected_end_time,
            ]);

            $attendance->restTimes()->delete();

            $restTimes = is_string($correctionRequest->corrected_rest_times)
                ? json_decode($correctionRequest->corrected_rest_times, true)
                : $correctionRequest->corrected_rest_times;

            if (!empty($restTimes)) {
                foreach ($restTimes as $restTime) {

                    if (empty($restTime['start'])) {
                        continue;
                    }

                    $attendance->restTimes()->create([
                        'start_time' => $restTime['start'],
                        'end_time' => $restTime['end'] ?? null,
                    ]);
                }
            }

            $correctionRequest->update([
                'status' => StampCorrectionRequest::STATUS_APPROVED
            ]);
        });

        return redirect()->route('stamp_correction_request.list', ['tab' => 'approved'])
            ->with('success', '申請を承認しました');
    }
}
