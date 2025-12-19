<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginMasterController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;


// 一般ユーザー（Fortifyが自動生成するルート以外で必要なもの）
Route::middleware(['auth:web'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::post('/attendance', [AttendanceController::class, 'store']);
    Route::get('/attendance/list', [AttendanceController::class, 'list']);
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show']);
});

// 管理者ログイン
Route::get('/admin/login', [LoginMasterController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [LoginMasterController::class, 'store']);

// 管理者専用ルート
Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    // 管理者の認証後ページをここに記述
});

// 1. 管理者としてログインしている場合
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'adminIndex']);
});

// 2. 一般ユーザーとしてログインしている場合
Route::middleware(['auth:web'])->group(function () {
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index']);
});


// Route::get('/register', function () {
//     return view('auth.admin.login');
// });

// Route::get('/login', function () {
//     return view('user.auth.login');
// });

// Route::get('/attendance', function () {
//     return view('user.auth.login');
// });

// Route::get('/admin/login', function () {
//     return view('admin.auth.login');
// });

// Route::get('/admin/attendance/list', function () {
//     return view('auth.register');
// });


