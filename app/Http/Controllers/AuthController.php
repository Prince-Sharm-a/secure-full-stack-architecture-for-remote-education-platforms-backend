<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
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
            if($user->status != 'active'){
                return response()->json([
                    'success' => false,
                    'message' => 'Your Account is '.$user->status
                ],422);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => '',
                'data' => [
                    'token' => $token,
                    'user' => $user->only('id','name','dob','gender','role','email','phone','profile_image')
                ]
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

            $emailCheck = User::where('email','=',$request->email)->first();
            if($emailCheck){
                return response()->json([
                    'success' => false,
                    'message' => 'Email is Already in Use',
                ],409);
            }
            $pass = $request->password;

            $request->password = Hash::make($request->password);
            $request->merge(['role'=>'student']);
            $user = User::create($request->only('name','dob','role','gender','email','password','phone','profile_image','email_verified_at'));
            
            $token = $this->createAuthToken($user->email,$pass);

            return response()->json([
                'success' => true,
                'message' => 'Account Created',
                'data' => [
                    'token' => $token,
                    'user' => $user->only('id','name','dob','gender','role','email','phone','profile_image')
                ]
            ],201);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ],500);
        }
    }

    private function createAuthToken($email,$password){
        Auth::attempt(['email'=>$email,'password'=>$password]);
        $user = Auth::user();
        $token  = $user->createToken('api-token')->plainTextToken;

        return $token;
    }

    public function forgotPassword(Request $request){
        try{
            $rules = [
                'email' => 'required | email'
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }

            $user = User::where('email','=',$request->email)->first();
            if(!$user){
                return response()->json(['success'=>false,'message'=>'User Not Found with this email'],404);
            }

            $token = Password::createToken($user);
            $resetLink = url(env('PASSWORD_RESET_LINK').'/reset-password'."?token=$token&email=$user->email");

            $emailMessage = "
            Hello,\n
            \tYour requested Password Link is here.\n\n\n

            
            \tClick the link given below to reset your Password.\n\n

            \t\t$resetLink
            ";
            $feature = new FeatureController();
            $feature->sendTextMail($user->email,'Email Verification',$emailMessage);
            
            return response()->json(['success'=>true,'message'=>'Check Your Mail'],201);
        } catch (\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function resetPassword(Request $request){
        try{
            $rules = [
                'token'=>'required | string',
                'email'=>'required | email ',
                'password'=>'required | min:8'
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }
            
            $record = PasswordResetToken::where('email','=',$request->token)->first();

            if(!Hash::check($request->token, $record->token)){
                return response()->json(['success'=>false,'message'=>'Your Link is Expired or Not Valid'],422);
            }

            $user = User::where('email','=',$request->email)->first();
            $user->update($request->only('password'));

            return response()->json([
                'success' => true,
                'message' => 'Password Changed',
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

    public function emailVerify($token){
        try{

        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function resendVerification(Request $request){
        try{

        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ],500);
        }
    }

    public function twoFaEnable(Request $request){
        try{

        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ],500);
        }
    }

    public function twoFaVerify(Request $request){
        try{

        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ],500);
        }
    }
    public function twoFaDisable(Request $request){
        try{

        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ],500);
        }
    }
}
