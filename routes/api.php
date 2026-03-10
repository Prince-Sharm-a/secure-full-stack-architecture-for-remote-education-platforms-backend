<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['delay.response'])->prefix('/v1')->group(function (){
    
    Route::middleware([])->prefix('/auth')->group(function (){

        Route::post('/login',[AuthController::class,'login'])->name('login');
        Route::post('/register',[AuthController::class,'register'])->name('register');
        
        Route::middleware('auth:sanctum')->group(function () {
            
            // Route::get('/profile', function (Request $request) {
            //     $user = auth()->user();
            //     // return $request->user();
            //     return $user->only('name','dob','gender','role','email','password','phone','profile_image');
            // });
                
            Route::get('/logout',[AuthController::class,'logout']);
                
        });

        Route::post('/forgot-password',[AuthController::class,'forgotPassword']);
        Route::post('/reset-password',[AuthController::class,'resetPassword']);

        // Todo :
        Route::get('/email-verify/{token}',[AuthController::class,'emailVerify']);
        Route::post('/resend-verification',[AuthController::class,'resendVerification']);

        //
        Route::prefix('/2fa')->group(function (){
            Route::post('/enable',[AuthController::class,'twoFaEnable']);
            Route::post('/verify',[AuthController::class,'twoFaVerify']);
            Route::post('/disable',[AuthController::class,'twoFaDisable']);
        });

    });

    Route::middleware(['auth:sanctum'])->prefix('/user')->group(function (){
        //
        Route::get('/profile',[UserController::class,'getProfile']);
        Route::put('/profile',[UserController::class,'profileUpdate']);
        Route::put('/change-password',[UserController::class,'changePassword']);
        Route::get('/active-devices',[UserController::class,'activeDevices']);
        Route::delete('/logout-devices/{id}',[UserController::class,'logoutDevices']);
        Route::delete('/delete-account',[UserController::class,'deleteAccount']);
    });

    Route::middleware([])->prefix('/courses')->group(function (){
        // Todo
        Route::get('',[CoursesController::class,'getCourses']);
        Route::get('/{id}',[CoursesController::class,'getCoursesById']);
        Route::get('/search',[CoursesController::class,'searchCourses']);
        Route::get('/category/{slug}',[CoursesController::class,'getCoursesByCategory']);
    });

    Route::middleware([])->prefix('/teacher')->group(function (){
        // todo
        Route::middleware([])->prefix('/courses')->group(function (){
            Route::post('',[CoursesController::class,'createCourseTeacher']);
            Route::put('/{id}',[CoursesController::class,'updateCourseTeacher']);
            Route::delete('/{id}',[CoursesController::class,'deleteCourseTeacher']);
            Route::get('',[CoursesController::class,'getCoursesTeacher']);
        });
    });
});