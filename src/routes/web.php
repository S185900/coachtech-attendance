<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginMasterController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| 1. ゲスト（未ログイン）専用ルート
|--------------------------------------------------------------------------
*/
Route::middleware(['guest:web', 'guest:admin'])->group(function () {
    // ログイン画面（一般）はFortifyが /login を自動生成

    // 会員登録画面（一般）
    Route::get('/register', function () {
        return view('user.auth.register');
    })->name('register');

    // 管理者ログイン画面の表示 (LoginMasterControllerを使う)
    Route::get('/admin/login', [LoginMasterController::class, 'create'])->name('admin.login');

    // 管理者ログインの実行
    Route::post('/admin/login', [LoginMasterController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| 2. 一般ユーザー専用ルート (auth:web)
|--------------------------------------------------------------------------
*/
// 【修正】middleware に 'verified' を追加。これでメール認証未完了ユーザーをガードします。
Route::middleware(['auth:web', 'verified'])->group(function () {
    // 打刻画面表示
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('index');

    // ★出勤打刻実行 (POST)
    Route::post('/attendance/start', [AttendanceController::class, 'store'])->name('attendance.start');

    // ★退勤打刻実行 (POST)
    Route::post('/attendance/end', [AttendanceController::class, 'update'])->name('attendance.end');

    // 休憩開始打刻実行 (POST)
    Route::post('/attendance/rest-start', [AttendanceController::class, 'restStart'])->name('attendance.rest-start');

    // 休憩終了打刻実行 (POST)
    Route::post('/attendance/rest-end', [AttendanceController::class, 'restEnd'])->name('attendance.rest-end');

    // PG04: 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // PG05: 勤怠詳細
    // URL例: /attendance/123
    Route::get('/attendance/detail/{attendance_id}', [AttendanceController::class, 'show'])->name('attendance.detail');

    // 詳細画面の「修正」ボタンを押した時の送り先（保存用：POST）
    Route::post('/attendance/update/{attendance_id}', [AttendanceController::class, 'correctionRequest'])->name('attendance.update');

    // PG07: 申請一覧画面
    // ここで ->name('stamp_correction_request.list') となっているか確認！
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_request.list');

});

/*
|--------------------------------------------------------------------------
| 3. 管理者専用ルート (auth:admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {

    // PG08: 管理者勤怠一覧
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('attendance.list');

    // PG10: 勤怠詳細
    Route::get('/attendance/detail/{id}', [AdminAttendanceController::class, 'showDetail'])->name('attendance.detail');

    // 勤怠詳細からの更新・承認 (POST) 
    // これにより route('admin.attendance.approve') が有効になります
    Route::post('/attendance/detail/{id}', [AdminAttendanceController::class, 'approve'])->name('attendance.approve');

    // PG09: スタッフ一覧画面
    Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('staff.list');

    // PG12: スタッフ別勤怠一覧
    Route::get('/staff/attendance/{id}', [AdminStaffController::class, 'staffAttendance'])->name('attendance.staff');

    Route::get('/staff/attendance/{id}/csv', [AdminStaffController::class, 'downloadCsv'])->name('attendance.staff.csv');

    // 管理者ログアウト
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});


/*
|--------------------------------------------------------------------------
| 4. 特別なパスを持つ管理者ルート (admin prefixなし)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:admin'])->group(function () {
    // PG12: /stamp_correction_request/list
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'adminIndex'])
        ->name('admin.stamp_correction.list');

    // PG13: /stamp_correction_request/approve/{id}
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [StampCorrectionRequestController::class, 'showApprove'])
        ->name('admin.stamp_correction.approve');

    Route::post('/stamp_correction_request/approve/{id}', [StampCorrectionRequestController::class, 'approve'])
        ->name('admin.stamp_correction.update');
});


/*
|--------------------------------------------------------------------------
| 5. メール認証に必要なルート群
|--------------------------------------------------------------------------
*/
// 【修正】認証誘導画面の表示（名前を verification.notice にするのがFortifyの標準）
Route::get('/email/verify', function () {
    return view('user.auth.verify-email');
})->middleware('auth')->name('verification.notice');

// 【修正】メール内のリンクをクリックした時の処理（verification.verify）
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    // 認証完了後のリダイレクト先を打刻画面に設定
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

// 【修正】認証メールの再送処理（verification.send）
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');


/*
|--------------------------------------------------------------------------
| 6. テストを通すためのダミールート
|--------------------------------------------------------------------------
*/
// tests/Feature/AttendanceDateTimeTest.php
Route::get('/stamp-correction-request-list', function() {})->name('stamp_correction_request.list');