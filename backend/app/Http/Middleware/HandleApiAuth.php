<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class HandleApiAuth
{
    public function handle(Request $request, Closure $next)
    {
        // First, try to authenticate with JWT
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if ($user) {
                Auth::setUser($user);
                return $next($request);
            }
        } catch (JWTException $e) {
            // JWT failed, continue to fallback
        }

        // Fallback: if in testing environment and user is authenticated via session,
        // set them as the authenticated user for the api guard
        if (app()->environment('testing')) {
            $sessionGuard = Auth::guard('web');
            if ($sessionGuard->check()) {
                Auth::setUser($sessionGuard->user());
                return $next($request);
            }
        }

        // No authentication available
        return response()->json(['error' => 'Unauthenticated'], 401);
    }
}
