<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // ─── Login ───────────────────────────────────────────────────────────────

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $user = auth('api')->user();

        if (! $user->status) {
            auth('api')->logout();

            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Contact support.',
            ], 403);
        }

        return $this->tokenResponse($token);
    }

    // ─── Logout ──────────────────────────────────────────────────────────────

    public function logout(): JsonResponse
    {
        try {
            auth('api')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not invalidate token.',
            ], 500);
        }
    }

    // ─── Refresh ─────────────────────────────────────────────────────────────

    public function refresh(): JsonResponse
    {
        try {
            $token = auth('api')->refresh();

            return $this->tokenResponse($token);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token cannot be refreshed. Please log in again.',
            ], 401);
        }
    }

    // ─── Current user ────────────────────────────────────────────────────────

    public function me(): JsonResponse
    {
        $user = auth('api')->user()->load('roles', 'permissions');

        return response()->json([
            'success' => true,
            'data'    => new UserResource($user),
        ]);
    }

    // ─── Helper ──────────────────────────────────────────────────────────────

    private function tokenResponse(string $token): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'access_token' => $token,
                'token_type'   => 'bearer',
                'expires_in'   => auth('api')->factory()->getTTL() * 60, // seconds
                'user'         => new UserResource($user->load('roles', 'permissions')),
            ],
        ]);
    }
}
