<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    //
    public function enrollCourse(Request $request, $course_id){
        try{

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
                'data' => []
            ],500);
        }
    }

    public function getEnrollments(Request $request){
        try{

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
                'data' => []
            ],500);
        }
    }

    public function getEnrollmentsByCourse(Request $request, $course_id){
        try{

        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
                'data' => []
            ],500);
        }
    }
    public function getEnrolledStudent(Request $request, $course_id){
        try{
            $data = Enrollment::where('course_id','=',$course_id)->select(['user_id','progress','enrolled_at'])->with(['students:id,name,email,phone'])->get();

            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $data
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
                'data' => []
            ],500);
        }
    }
}
