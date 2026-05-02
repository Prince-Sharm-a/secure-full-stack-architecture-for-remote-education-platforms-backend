<?php

namespace App\Http\Controllers;

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
}
