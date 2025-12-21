<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
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
    // 出勤登録画面（メイン）
    Route::get('/attendance', function () {
        return view('user.index');
    })->name('index');

    // 修正申請一覧（一般）
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index']);

    // その他、一般ユーザー用のルートをここに追加
});

/*
|--------------------------------------------------------------------------
| 3. 管理者専用ルート (auth:admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    // 勤怠一覧画面
    Route::get('/attendance/list', function () {
        return view('admin.list');
    })->name('admin.attendance.list');

    // 修正申請一覧（管理者）
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'adminIndex']);

    // 管理者ログアウト
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
});