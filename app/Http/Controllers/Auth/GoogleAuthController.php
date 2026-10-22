<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Cache;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Log;
use Str;

class GoogleAuthController extends Controller
{
    //
    public function redirect(){
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback(Request $request){
        try{
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Log::info("Google User Email: " . $googleUser->getEmail());

            $user = User::where('email', $googleUser->getEmail())->first();

            // Log::info("User: $user");

            if(!$user){
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt('default_password'), // You might want to handle this differently
                ]);
            } else {
                $user->update([
                    'google_id' => $googleUser->getId()
                ]);
            }

            // Log::info("User ID: $user->id");

            $token = $user->createToken('api-token')->plainTextToken;

            $code = Str::random(64);

            Cache::put(
                "oauth_code:{$code}",
                $token,
                now()->addMinute()
            );

            return response()->view('auth.google-success',['code' => $code]);
        } catch (\Exception $e){
            Log::error("Error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage(), 'message' => 'Google Authentication failed.'], 400);
        }
    }

    public function exchange(Request $request){
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $token = Cache::pull(
            "oauth_code:{$request->code}"
        );

        if (!$token) {
            return response()->json([
                'message' => 'Invalid or expired authorization code.'
            ], 401);
        }

        return response()->json([
            'token' => $token,
        ]);
    }
}
