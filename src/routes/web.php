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

    Route::get('/register', function () {
        return view('user.auth.register');
    })->name('register');

    Route::get('/admin/login', [LoginMasterController::class, 'create'])->name('admin.login');

    Route::post('/admin/login', [LoginMasterController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| 2. 一般ユーザー専用ルート
|--------------------------------------------------------------------------
*/
    Route::middleware(['auth:web', 'verified'])->group(function () {

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('index');

    Route::post('/attendance/start', [AttendanceController::class, 'store'])->name('attendance.start');

    Route::post('/attendance/end', [AttendanceController::class, 'update'])->name('attendance.end');

    Route::post('/attendance/rest-start', [AttendanceController::class, 'restStart'])->name('attendance.rest-start');

    Route::post('/attendance/rest-end', [AttendanceController::class, 'restEnd'])->name('attendance.rest-end');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    Route::get('/attendance/detail/{attendance_id}', [AttendanceController::class, 'show'])->name('attendance.detail');

    Route::post('/attendance/update/{attendance_id}', [AttendanceController::class, 'correctionRequest'])->name('attendance.update');

    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_request.list');
});

/*
|--------------------------------------------------------------------------
| 3. 管理者専用ルート
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('attendance.list');

    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'showDetail'])->name('attendance.detail');

    Route::post('/attendance/{id}', [AdminAttendanceController::class, 'approve'])->name('attendance.approve');

    Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('staff.list');

    Route::get('/attendance/staff/{id}', [AdminStaffController::class, 'staffAttendance'])->name('attendance.staff');

    Route::get('/attendance/staff/{id}/csv', [AdminStaffController::class, 'downloadCsv'])->name('attendance.staff.csv');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| 4. 特別なパスを持つ管理者ルート
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:admin'])->group(function () {

    Route::get('/admin/stamp_correction_request/list', [StampCorrectionRequestController::class, 'adminIndex'])
        ->name('admin.stamp_correction.list');

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
Route::get('/email/verify', function () {
    return view('user.auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
