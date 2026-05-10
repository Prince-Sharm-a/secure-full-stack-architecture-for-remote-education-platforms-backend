<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test',function (){
    return [
        'success' => true,
        'message' => 'Welcome'
    ];
});

Route::middleware(['delay.response'])->prefix('/v1')->group(function (){
    
    Route::middleware([])->prefix('')->group(function (){

        Route::post('/auth/login',[AuthController::class,'login'])->name('login');
        Route::post('/auth/register',[AuthController::class,'register'])->name('register');
        
        Route::middleware('auth:sanctum')->group(function () {
            
            // Route::get('/profile', function (Request $request) {
            //     $user = auth()->user();
            //     // return $request->user();
            //     return $user->only('name','dob','gender','role','email','password','phone','profile_image');
            // });
                
            Route::get('/auth/logout',[AuthController::class,'logout']);
                
        });

        Route::post('/auth/forgot-password',[AuthController::class,'forgotPassword']);
        Route::post('/auth/reset-password',[AuthController::class,'resetPassword']);

        // Todo :
        Route::get('/auth/email-verify/{token}',[AuthController::class,'emailVerify']);
        Route::post('/auth/resend-verification',[AuthController::class,'resendVerification']);

        // ? two factor authentication
        Route::prefix('')->group(function (){
            Route::post('/auth/2fa/enable',[AuthController::class,'twoFaEnable']);
            Route::post('/auth/2fa/verify',[AuthController::class,'twoFaVerify']);
            Route::post('/auth/2fa/disable',[AuthController::class,'twoFaDisable']);
        });

    });

    Route::middleware(['auth:sanctum'])->prefix('')->group(function (){
        // ? user
        Route::get('/user/profile',[UserController::class,'getProfile']);
        Route::put('/user/profile',[UserController::class,'profileUpdate']);
        Route::put('/user/change-password',[UserController::class,'changePassword']);
        Route::get('/user/active-devices',[UserController::class,'activeDevices']);
        Route::delete('/user/logout-devices/{id}',[UserController::class,'logoutDevices']);
        Route::delete('/user/delete-account',[UserController::class,'deleteAccount']);
    });

    Route::middleware([])->prefix('')->group(function (){
        // ? Courses
        Route::get('/courses',[CoursesController::class,'getCourses']);
        Route::get('/courses/{id}',[CoursesController::class,'getCoursesById'])->where('id','[0-9]+');
        Route::get('/courses/search',[CoursesController::class,'searchCourses']); // todo
        Route::get('/courses/category/{slug}',[CoursesController::class,'getCoursesByCategory']);
    });

    Route::middleware(['auth:sanctum'])->prefix('')->group(function (){
        // ? Course
        Route::middleware([])->prefix('')->group(function (){
            Route::post('/teacher/courses',[CoursesController::class,'createCourseTeacher']);
            Route::put('/teacher/courses/{id}',[CoursesController::class,'updateCourseTeacher']);
            Route::delete('/teacher/courses/{id}',[CoursesController::class,'deleteCourseTeacher']);
            Route::get('/teacher/courses',[CoursesController::class,'getCoursesTeacher']);
            Route::get('/teacher/courses/{id}',[CoursesController::class,'getCoursesTeacherById'])->where('id','[0-9]+');
        });

        Route::middleware([])->prefix('')->group(function (){
            Route::post('/teacher/modules',[ModuleController::class,'createModulesTeacher']);
            Route::put('/teacher/modules/{id}',[ModuleController::class,'updateModulesTeacher'])->where('id','[0-9]+');
            Route::delete('/teacher/modules/{id}',[ModuleController::class,'deleteModulesTeacher'])->where('id','[0-9]+');
            Route::get('/courses/{course_id}/modules',[ModuleController::class,'gerCourseModules'])->where('course_id','[0-9]+');
        });

        Route::middleware([])->prefix('')->group(function (){
            Route::post('/teacher/lessons',[LessonController::class,'createLessonTeacher']);
            Route::put('/teacher/lessons/{id}',[LessonController::class,'updateLessonTeacher'])->where('id','[0-9]+');
            Route::delete('/teacher/lessons/{id}',[LessonController::class,'deleteLessonTeacher'])->where('id','[0-9]+');
            Route::get('/modules/{module_id}/lessons',[LessonController::class,'getModulesLesson'])->where('module_id','[0-9]+');
            Route::get('/lessons/{id}',[LessonController::class,'getLessonById'])->where('id','[0-9]+');
        });

        // ? Assigment API's

        Route::middleware([])->prefix('')->group(function (){
            Route::post('/teacher/assignments',[AssignmentController::class,'createAssignment']);
            Route::put('/teacher/assignments/{id}',[AssignmentController::class,'updateAssignment'])->where('id','[0-9]+');
            Route::delete('/teacher/assignments/{id}',[AssignmentController::class,'deleteAssignment'])->where('id','[0-9]+');
            Route::get('/course/{course_id}/assignments',[AssignmentController::class,'getCourseAssignments'])->where('course_id','[0-9]+');
        });

        // ? Grading 
        Route::middleware([])->prefix('')->group(function (){
            Route::post('/teacher/grade/{submission_id}',[SubmissionController::class,'createAssignmentGrade'])->where('submission_id','[0-9]+');
            Route::get('/teacher/submissions/{assignment_id}',[SubmissionController::class,'getAssignmentSubmission'])->where('assignment_id','[0-9]+');
        });

        // ? Dashboard
        Route::middleware([])->prefix('')->group(function (){
            Route::get('/teacher/dashboard',[AnalyticsController::class,'getTeacherDashboard']);
            Route::get('/teacher/analytics/{course_id}',[AnalyticsController::class,'courseAnalytics'])->where('course_id','[0-9]+');
            Route::get('/teacher/revenue-report',[AnalyticsController::class,'getTeacherRevenueReport']);
        });
    });

    Route::middleware([])->prefix('')->group(function (){
        
        Route::middleware([])->prefix('')->group(function (){
            // ? Enrollments
            Route::post('/enroll/{course_id}',[EnrollmentController::class,'enrollCourse'])->where('course_id','[0-9]+');
            Route::get('/student/enrollments',[EnrollmentController::class,'getEnrollments']);
            Route::get('/student/enrollments/{course_id}',[EnrollmentController::class,'getEnrollmentsByCourse'])->where('course_id','[0-9]+');
            Route::put('/student/progress/{lesson_id}',[CoursesController::class,'updateStudentsLessonProgress'])->where('lesson_id','[0-9]+');

            // ? Assignment
            Route::middleware([])->prefix('')->group(function (){
                Route::get('/student/course/{course_id}/assignments',[AssignmentController::class,'getCourseAssignments']);
                Route::post('/student/submissions',[SubmissionController::class,'createAssignmentSubmission']);
                Route::get('/student/submissions/{assignment_id}',[SubmissionController::class,'getAssignmentSubmission']);
            });

            // ? Dashboard
            Route::middleware([])->prefix('')->group(function (){
                Route::get('/student/dashboard',[AnalyticsController::class,'getStudentDashboard']);
                Route::get('/student/course-progress/{course_id}',[AnalyticsController::class,'courseProgress'])->where('course_id','[0-9]+');
                Route::get('/student/upcoming-classes',[AnalyticsController::class,'getStudentUpcomingClass']);
            });
        });
    });

    // ? Payment
    Route::middleware([])->prefix('')->group(function (){
        Route::post('/payment/create-order',[PaymentController::class,'createOrder']);
        Route::post('/payment/verify',[PaymentController::class,'verifyPayment']);
        Route::get('/student/payment-history',[PaymentController::class,'getPaymentHistory']);
        Route::post('/payment/webhook',[PaymentController::class,'paymentWebhook']);
    });

    Route::middleware([])->prefix('')->group(function (){
        Route::get('/security/login-logs',[AuthController::class,'getLoginLogs']);
        Route::get('/security/access-logs',[AuthController::class,'getAccessLogs']);
        Route::get('/security/audit-logs',[AuthController::class,'getAuditLogs']);
        Route::post('/security/report-suspicious',[AuthController::class,'createSuspiciousReport']);
    });

    // ? admin
    Route::middleware([])->prefix('')->group(function (){
        Route::get('/admin/failed-logins',[UserController::class,'getFailedLogins']);
        Route::get('/admin/block-user/{id}',[UserController::class,'blockUser']);
        Route::post('/admin/unblock-user/{id}',[UserController::class,'unblockUser']);

        Route::get('/admin/dashboard',[AnalyticsController::class,'getAdminDashboard']);

        Route::get('/admin/users',[UserController::class,'getUsersList']);
        Route::put('/admin/change-role/{id}',[UserController::class,'changeRole']);
        Route::put('/admin/suspend-user/{id}',[UserController::class,'suspendUser']);

        Route::get('/admin/courses',[CoursesController::class,'getCoursesList']);
        Route::put('/admin/approve-course/{id}',[CoursesController::class,'approveCourse']);
        Route::delete('/admin/delete-course/{id}',[CoursesController::class,'deleteAdminCourse']);
    });

    // ? upload api
    Route::middleware([])->prefix('')->group(function (){
        Route::post('/upload/video',[FeatureController::class,'uploadVideo']);
        Route::post('/upload/document',[FeatureController::class,'uploadDocument']);
        Route::delete('/upload/{id}',[FeatureController::class,'deleteUploadsContent']);
    });
});