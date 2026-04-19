<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

            if($user->two_factor_enable){
               $otp = rand(100000,999999);

               $user->otp = $otp;
               $user->otp_expires_at = now()->addMinutes(2);
               $user->save();

               $emailMessage = "
                \nHello,\n
                \tYour OTP for 2-step Verification is $otp.\n                
                \tIt is only valid for 2 minutes.\n
                \tUse this OTP to complete your 2-step Verification.\n
                \tDo not share this otp with anyone.\n\n
                
                Thanks and Regards\n
                EDDUCATOR\n
                ";
                $feature = new FeatureController();
                $feature->sendTextMail($request->email,'Two Factor Authentication',$emailMessage);

                return response()->json([
                    'success' => false,
                    'message' => 'OTP sent to mail',
                    '2fa_required' => true,
                ]);
            }

            $loginCount = DB::table('personal_access_tokens')->where('tokenable_id','=',$user->id)->count();
            if($loginCount === 2){
                return response()->json(['success'=>false,'message'=>'Already Logged in 2 Devices','device_logout'=>true]);
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
                'dob' => "",
                'role' => "string",
                'status' => "string",
                'gender' => "string | max:10",
                'email' => "required | email",
                'password' => "required | min:8",
                'phone' => " min:10 | max:10",
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
                return response()->json(['success'=>false,'message'=>'User Not Found'],404);
            }

            $token = Password::createToken($user);
            $resetLink = url(env('PASSWORD_RESET_LINK').'/reset-password'."?token=$token&email=$user->email");

            $emailMessage = "
            \nHello,\n
            \tYour Link to Reset Password is here.\n
            \tIt is only valid for 10 minutes.\n
            \tVisit this URL to change your Password.\n\n

            \tClick the link given below to reset your Password.\n\n

            \t\t$resetLink\n\n
            
            Thanks and Regards\n
            EDDUCATOR\n
            ";
            $feature = new FeatureController();
            $feature->sendTextMail($request->email,'Reset Password',$emailMessage);
            
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
            
            $record = PasswordResetToken::where('email','=',$request->email)->first();
            if(!$record){
                return response()->json([
                    'success'=>false,
                    'message'=>'Invalid reset request'
                ],404);
            }
            
            if(Carbon::parse($record->created_at)->addMinutes(10)->isPast()){
                return response()->json([
                    'success'=>false,
                    'message'=>'Token expired'
                ],422);
            }

            if(!Hash::check($request->token, $record->token)){
                return response()->json(['success'=>false,'message'=>'Invalid Token'],422);
            }

            $user = User::where('email','=',$request->email)->first();
            $user->update([
                'password' => Hash::make($request->password)
            ]);
            
            $record->delete();

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

    public function emailVerify(Request $request, $token){
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
            $user = $request->user();
            $user->update([
                'two_factor_enable' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => '2 - Factor Authentication Enabled',
            ]);
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
            $rules = [
                'email' => 'required | email',
                'otp' => 'required | min:6 | max:6'
            ];
            $validation = \Validator::make($request->all(),$rules);
            if($validation->fails()){
                return response()->json(['success'=>false,'message'=>$validation->errors()],400);
            }

            $user = User::where('email','=',$request->email)->first();

            if($user->otp !== $request->otp || $user->otp_expires_at < now()){
                return response()->json(['success'=>false,'message'=>'Invalid or expired OTP'],400);
            }

            $loginCount = DB::table('personal_access_tokens')->where('tokenable_id','=',$user->id)->count();
            if($loginCount > 2){
                return response()->json(['success'=>false,'message'=>'Already Logged in 2 Devices','device_logout'=>true]);
            }

            $user->update([
                'otp' => null,
                'otp_expires_at' => null
            ]);

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => '2FA verified',
                'data' => [
                    'token' => $token,
                    'user' => $user->only('id','name','dob','gender','role','email','phone','profile_image')
                ]
            ]);

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
            $user = $request->user();

            // todo: perform 2FA verification before disable 2FA

            $user->update([
                'two_factor_enable' => false,
                'otp' => null,
                'otp_expires_at' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Two Factor Authentication Disabled'
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
