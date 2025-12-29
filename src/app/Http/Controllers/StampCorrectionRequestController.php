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

        // クエリパラメータ 'tab' で表示を切り替え（デフォルトは承認待ち）
        $status = $request->query('tab') === 'approved' ? 1 : 0;

        // ログインユーザーの申請一覧を、勤怠データ(attendance)と一緒に取得
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
}
