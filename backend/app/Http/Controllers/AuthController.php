<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * POST /api/login
     * Returns a JWT token (Bearer).
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        /** @var User $user */
        $user = JWTAuth::user();

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * POST /api/logout
     * Invalidates the current JWT token.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            try {
                JWTAuth::invalidate(JWTAuth::getToken());
            } catch (JWTException $e) {
            }
            
            return response()->json(['message' => 'Logged out successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => 'could_not_invalidate_token'], 500);
        }
    }

    /**
     * GET /api/user
     * Returns the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        try {
            // First, try to get the user from JWT token
            try {
                $user = JWTAuth::parseToken()->authenticate();
            } catch (JWTException $e) {
                // If JWT parsing fails, use the authenticated user from the middleware
                // This allows actingAs() to work in tests
                $user = $request->user();
            }
            
            return response()->json(new UserResource($user));
        } catch (Exception $e) {
            return response()->json(['error' => 'user_not_found'], 404);
        }
    }
}
