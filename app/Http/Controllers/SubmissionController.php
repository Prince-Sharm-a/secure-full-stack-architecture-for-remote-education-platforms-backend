<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    //
    public function createAssignmentGrade(Request $request, $submission_id){
        try{
            $rules = [
                'grade' => 'required',
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }
            $user = $request->user();

            $data = Submission::leftJoin('assignments','assignments.id','=','submissions.assignment_id')->leftJoin('courses','courses.id','=','assignments.course_id')->where('courses.teacher_id','=',$user->id)->select(['submissions.id','submissions.course_id','submissions.title','assignments.description','assignments.due_date'])->first();
            $data->update($request->only('grade'));

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

    public function getAssignmentSubmission(Request $request, $assignment_id){
        try{
            $data = Submission::with('student')->where('assignment_id','=',$assignment_id)->select(['assignment_id','student_id','file_url','grade'])->get();
            
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

    public function createAssignmentSubmission(Request $request){
        try{
            $rules = [
                'assignment_id' => 'required',
                'file_url' => 'required',
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }
            $user = $request->user();

            $value = $request->only('assignment_id','file_url');
            $value['student_id'] = $user->id ;

            $data = Submission::create($value);

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
}
