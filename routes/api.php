<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login',[UserController::class,'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profile', function (Request $request) {
        $user = auth()->user();
        return $request->user();
    });

    Route::get('/logout',[UserController::class,'logout']);

});