<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;




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

    // 管理者ログイン画面の表示
    Route::get('/admin/login', function () {
        return view('admin.auth.login');
    })->name('admin.login');

    // 【追加！】管理者ログインの実行（POST送信）を受け付ける設定
    // これにより、MethodNotAllowedHttpException が解消されます
    // 結論から言うと、一般ユーザー用の POST 設定は Fortifyが裏側で自動的に登録してくれているので、web.php に書かなくても動くようになっている。
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| 2. 一般ユーザー専用ルート (auth:web)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web'])->group(function () {
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
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
    ->name('stamp_correction_request.list');


    // auth:web,admin のように両方のガードを許可するように変更
    // Route::get('/stamp_correction/list', [StampCorrectionRequestController::class, 'index'])
    //     ->middleware('auth:web,admin')
    //     ->name('stamp_correction.list');
});

/*
|--------------------------------------------------------------------------
| 3. 管理者専用ルート (auth:admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {

    // PG08: 管理者勤怠一覧
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('attendance.list');

    // 勤怠詳細（PG10）
    // URLは /admin/attendance/detail/{id} になります
    Route::get('/attendance/detail/{id}', [AdminAttendanceController::class, 'showDetail'])
        ->name('admin.attendance.detail');

    // 修正申請一覧（管理者）
    // Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'adminIndex']);

    Route::get('/stamp_correction_request/approve/{id}', [StampCorrectionRequestController::class, 'showApprove']);

    // 勤怠詳細の更新・承認処理
    Route::post('/attendance/approve/{id}', [AdminAttendanceController::class, 'approve'])
        ->name('admin.attendance.approve');

    // PG09: スタッフ一覧画面
    Route::get('/staff/list', [AdminStaffController::class, 'index'])
        ->name('staff.list');

    // PG12: スタッフ別勤怠一覧
    Route::get('/staff/attendance/{id}', [AdminStaffController::class, 'staffAttendance'])
        ->name('attendance.staff');
    
    Route::get('/attendance/detail/{id}', [AdminAttendanceController::class, 'showDetail'])
        ->name('attendance.detail');

    // 管理者ログアウト
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});