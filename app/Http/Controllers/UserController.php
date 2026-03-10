<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use function Laravel\Prompts\select;

class UserController extends Controller
{
    //
    public function getProfile(Request $request){
        try{
            $user = auth()->user();
            // return $request->user();
            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $user->only('id','name','dob','gender','role','email','phone','profile_image')
            ]);
        } catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function profileUpdate(Request $request){
        try{
            $user = $request->user();
            $user->update($request->only('name','dob','gender','role','email','phone','profile_image'));
            
            return $user->only('id','name','dob','gender','role','email','phone','profile_image');
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function changePassword(Request $request){
        try{
            $rules = [
                'current_password' => 'required | min:8',
                'password' => 'required | min:8'
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }

            $user = User::findOrFail($request->user()->id);
            if(!Hash::check($request->current_password,$user->password)){
                return response()->json(['success'=>false, 'message' =>'Wrong Current Password'],401);
            }

            $user->update($request->only('password'));

            return response()->json([
                'success' => true,
                'message' => 'Password Changed',
                'data' => [
                    'password' => $request->password
                ]
            ]);
            
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function activeDevices(Request $request){
        try{

            $data = DB::table('personal_access_tokens')->where('tokenable_id','=',$request->user()->id)->select(['id','name','last_used_at','created_at'])->get();
            
            return response()->json([
                'success' => true,
                'message' => '',
                'data' => $data
            ]);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function logoutDevices(Request $request,$id){
        try{
            $token = $request->user()->tokens()->where('id','=',$id)->first();
            
            if(!$token){
                return response()->json([
                    'success' => false,
                    'message' => 'Device Not Found',
                ],404);
            }

            $token->delete();

            return response()->json([
                'success' => true,
                'message' => 'Device logged out successfully',
            ]);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function deleteAccount(Request $request){
        try{
            $rules = [
                'password' => 'required | min:8'
            ];
            $user = User::findOrFail($request->user()->id);
            if(!Hash::check())
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => "Your Account is Deleted",
            ]);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ],500);
        }
    }
}
