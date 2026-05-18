<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    //
    public function createAssignment(Request $request){
        try{
            $rules = [
                'course_id' => 'required',
                'title' => 'required',
                'due_date' => 'required'
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }
            $user = $request->user();

            if(!Course::where('id','=',$request->course_id)->where('teacher_id','=',$user->id)->first()){
                return response()->json(['success' => false,'message' => 'Not Found','data' => []],404);
            }

            $data = Assignment::create($request->only('course_id','title','description','due_date'));

            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $data
            ],201);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
                'data' => []
            ],500);
        }
    }

    public function updateAssignment(Request $request, $id){
        try{
            $rules = [
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }
            $user = $request->user();

            $data = Assignment::leftJoin('courses','courses.id','=','assignments.course_id')->where('assignments.id','=',$id)->where('courses.teacher_id','=',$user->id)->select(['assignments.id','assignments.course_id','assignments.title','assignments.description','assignments.due_date'])->first();

            if(!$data){
                return response()->json(['success' => false,'message' => 'Not Found','data' => []],404);
            }

            $data->update($request->only('title','description','due_date'));

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

    public function deleteAssignment(Request $request, $id){
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

    public function getCourseAssignments(Request $request, $course_id){
        try{
            $data = Assignment::withCount('submission','courses.students')->where('course_id','=',$course_id)->select(['id','course_id','title'])->latest('created_at')->get();

            if(!$data){
                return response()->json(['success' => false,'message' => 'Not Found','data' => []],404);
            }

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

    public function getStudentCourseAssignments(Request $request, $course_id){
        try{
            $data = Assignment::where('course_id','=',$course_id)->select(['id','course_id','title'])->latest('created_at')->get();

            if(!$data){
                return response()->json(['success' => false,'message' => 'Not Found','data' => []],404);
            }

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
