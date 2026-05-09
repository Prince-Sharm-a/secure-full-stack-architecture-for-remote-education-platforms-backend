<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    //
    public function getModulesLesson(Request $request, $module_id){
        try{
            
            $data = Lesson::where('module_id','=',$module_id)->select(['id','module_id','title'])->get();

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

    public function createLessonTeacher(Request $request){
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

    public function updateLessonTeacher(Request $request, $id){
        try{
            $user = $request->user();
            $data = Lesson::leftJoin('modules','modules.id','=','lessons.module_id')->leftJoin('courses','courses.id','=','modules.course_id')->where('courses.teacher_id','=',$user->id)->where('lessons.id','=',$id)->select(['lessons.id','lessons.module_id','lessons.title','lessons.video_url'])->first();

            if(!$data){
                return response()->json(['success' => false,'message' => 'Not Found','data' => []],404);
            }
            $data->update($request->only('title','video_url'));

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

    public function deleteLessonTeacher(Request $request, $id){
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

    public function getLessonById(Request $request, $id){
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
