<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    //
    public function gerCourseModulesTeacher(Request $request, $course_id){
        try{
            $user = $request->user();
            $data = Module::leftJoin('courses','courses.id','=','modules.course_id')->where('modules.course_id','=',$course_id)->where('courses.teacher_id','=',$user->id)->select(['modules.id','modules.course_id','modules.title'])->get();

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

    public function createModulesTeacher(Request $request){
        try{
            $rules = [
                'course_id' => 'required'
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }
            $user = $request->user();

            if(!Course::where('id','=',$request->course_id)->where('teacher_id','=',$user->id)->first()){
                return response()->json(['success' => false,'message' => 'Not Found','data' => []],404);
            }

            $data = Module::create($request->only('course_id','title'));

            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $data->only('id','course_id','title')
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

    public function updateModulesTeacher(Request $request, $id){
        try{
            $rules = [
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }
            $user = $request->user();

            $data = Module::leftJoin('courses','courses.id','=','modules.course_id')->where('modules.id','=',$id)->where('courses.teacher_id','=',$user->id)->select(['modules.id','modules.course_id','modules.title'])->first();

            if(!$data){
                return response()->json(['success' => false,'message' => 'Not Found','data' => []],404);
            }

            $data->update($request->only('title'));

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

    public function deleteModulesTeacher(Request $request, $id){
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
