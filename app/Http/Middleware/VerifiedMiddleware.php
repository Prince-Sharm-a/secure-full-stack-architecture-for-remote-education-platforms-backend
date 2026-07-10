<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifiedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if(!$user){
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ],401);
        }

        if(!$user->email_verified_at){
            return response()->json([
                'success' => false,
                'message' => 'Your Account is not Verified.'
            ],403);
        }
        
        return $next($request);
    }
}
