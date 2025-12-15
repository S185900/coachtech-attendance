<?php

use Illuminate\Support\Facades\Route;

Route::get('admin/login', function () {
    return view('auth.admin.login');
});

Route::get('/', function () {
    return view('auth.register');
});

Route::get('/', function () {
    return view('');
});
