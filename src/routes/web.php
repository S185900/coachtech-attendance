<?php

use Illuminate\Support\Facades\Route;



// Route::get('/register', fn () => view('auth.register'))->name('register');
// Route::post('/register', [RegisteredUserController::class, 'store']);

// Route::get('/login', fn () => view('auth.login'))->name('login');
// Route::post('/login', [LoginUserController::class, 'store']);


Route::get('/register', function () {
    return view('auth.admin.login');
});

Route::get('/login', function () {
    return view('user.auth.login');
});

Route::get('/attendance', function () {
    return view('user.auth.login');
});

Route::get('/admin/login', function () {
    return view('admin.auth.login');
});

Route::get('/admin/attendance/list', function () {
    return view('auth.register');
});

Route::get('/', function () {
    return view('');
});
