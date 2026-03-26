<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
    //
    public function getCourses(Request $request){
        try{
            $data = Course::leftJoin('users','users.id','=','courses.teacher_id')
            ->where('courses.status','=','published')
            ->select([
                'courses.id',
                'courses.title',
                'courses.price',
                'courses.level',
                'courses.status',
                'courses.category',
                'users.id as teacher_id',
                'users.name',
                'users.profile_image'
            ])
            ->paginate(env('PAGINATE',10));
            if(empty($data->data)){
                return response()->json(['success' => false, 'message' => 'No Data'],404);
            }

            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $data
            ]);

        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ],500);
        }
    }

    public function getCoursesById(Request $request, $id){
        try{
            $data = Course::leftJoin('users','users.id','=','courses.teacher_id')
            ->with(['module:id,title','module.lesson:id,title'])
            ->where('courses.id','=',$id)
            ->select([
                'courses.id',
                'courses.title',
                'courses.price',
                'courses.level',
                'courses.status',
                'courses.category',
                'courses.description',
                'users.id as teacher_id',
                'users.name',
                'users.profile_image'
            ])->first();

            if(!$data){
                return response()->json(['success' => false,'message' => 'Not Found'],404);
            }

            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $data
            ]);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ],500);
        }
    }

    public function searchCourses(Request $request){
        try{

        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ],500);
        }
    }

    public function getCoursesByCategory(Request $request,$slug){
        try{
            $data = Course::leftJoin('users','users.id','=','courses.teacher_id')
            ->where('courses.category','like','%'.$slug.'%')
            ->where('courses.status','=','published')
            ->select([
                'courses.id',
                'courses.title',
                'courses.price',
                'courses.level',
                'courses.status',
                'courses.category',
                'users.id as teacher_id',
                'users.name',
                'users.profile_image'
            ])
            ->paginate(env('PAGINATE',10));

            
            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $data
            ]);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ],500);
        }   
    }

    public function createCourseTeacher(Request $request){
        try{
            $rules = [
                'title' => 'required | max:255 | string',
                'description' => 'nullable | string',
                'price' => 'required | numeric | min:0',
                'level' => 'required | string',
                'status' => 'required | string',
                'category' => 'required | string'
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }
<<<<<<< HEAD
            $data = Course::create(array_merge($request->only('title','description','price','level','status','category'),['teacher_id'=>$request->user()->id]));

            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $data->only('id','teacher_id','title','description','price','level','status','category')
            ],201);
=======
            
            $data = Course::create();
>>>>>>> cdb4f0a (initial commit)
            
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ],500);
        }
    }

    public function updateCourseTeacher(Request $request){
        try{
            $rules = [
                'course_id' => 'required | integer'
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }
            $user = $request->user();
            $data = Course::where('id','=',$request->course_id)->where('teacher_id','=',$user->id)->update($request->only('title','description','price','level','status','category'));
            
            if(!$data){
                return response()->json(['success'=>false,'message'=>'Not Found']);
            }

            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $data->only('id','title','description','price','level','status','category')
            ]);

        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ],500);
        }
    }

    public function deleteCourseTeacher(Request $request,$id){
        try{
            $user = $request->user();
            $course = Course::where('teacher_id','=',$user->id)->where('id','=',$id)->first();
            if(!$course){
                return response()->json(['success'=>false,'message'=>'Not Found'],404);
            }
            $modules = Module::where('course_id','=',$id)->pluck('id');
            Module::where('course_id','=',$id)->delete();
            $lessons = Lesson::whereIn('module_id',$modules)->delete();
            $course->delete();

            return response()->json([
                'success' => true,
                'message' => 'Deleted Successfully'
            ]);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ],500);
        }
    }

    public function getCoursesTeacher(Request $request){
        try{
            $user = $request->user();
            $data = Course::leftJoin('users','users.id','=','courses.teacher_id')
            ->with(['module:id,title','module.lesson:id,title,video_url,duration'])
            ->where('teacher_id','=',$user->id)
            ->select([
                'courses.id',
                'courses.title',
                'courses.price',
                'courses.level',
                'courses.status',
                'courses.category',
                'users.id as teacher_id',
                'users.name',
                'users.profile_image'
            ])
            ->paginate(env('PAGINATE',10));

            if(empty($data->data)){
                return response()->json(['success'=>false,'message'=>'Not Found'],404);
            }

            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $data
            ]);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ],500);
        }
    }
}
