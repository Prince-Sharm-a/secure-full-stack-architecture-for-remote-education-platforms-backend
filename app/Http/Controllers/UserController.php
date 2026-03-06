<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    public function login(Request $request)
	{
        try{
            $rules = [
                'email' => 'required | email',
                'password' => 'required | min:8 | string'
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }
            
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user = Auth::user();

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $user
            ]);
        } catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ],500);
        }
	}

    public function logout(Request $request)
	{
        try{
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out'
            ]);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ],500);
        }
	}

    public function register(Request $request){
        try{
            $rules = [
                'name' => "required",
                'dob' => "required",
                'role' => "string",
                'status' => "string",
                'gender' => "string | max:10",
                'email' => "required | email",
                'password' => "required | min:8",
                'phone' => "required | min:10 | max:10",
                'profile_image' => "string",
                'email_verified_at' => ""
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }

            $request->password = Hash::make($request->password);
            $request->merge(['role'=>'student']);
            $user = User::create($request->only('name','dob','role','status','gender','email','password','phone','profile_image','email_verified_at'));

            return response()->json([
                'success' => true,
                'message' => 'Account Created',
                'data' => $user->only('name','email','phone','dob','role','status')
            ],201);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ],500);
        }
    }
}
