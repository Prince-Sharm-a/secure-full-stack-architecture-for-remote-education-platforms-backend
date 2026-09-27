<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/google',[GoogleAuthController::class,'redirect'])->name('auth.google');

Route::get('/auth/google/callback',[GoogleAuthController::class,'callback'])->name('auth.google.callback');