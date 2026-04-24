<?php

namespace App\Http\Controllers\Token;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * TokenController
 *
 * These endpoints are NOT for end users — they are called by other
 * microservices (Event, Kiosk, Tournament) to validate tokens and
 * retrieve the public key for local RS256 validation.
 *
 * All routes here are protected by the InternalServiceMiddleware,
 * which checks the X-Service-Secret header.
 */
class TokenController extends Controller
{
    // ─── Validate ────────────────────────────────────────────────────────────

    /**
     * Validate a user's JWT token.
     *
     * Other services send:
     *   Authorization: Bearer <user-jwt>
     *   X-Service-Secret: <internal-secret>
     *
     * Returns the decoded user + roles + permissions so the calling
     * service doesn't need its own users table.
     */
    public function validate(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?? $request->input('token');

        if (! $token) {
            return response()->json([
                'success' => false,
                'valid'   => false,
                'message' => 'No token provided.',
            ], 400);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $user    = JWTAuth::setToken($token)->authenticate();

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'valid'   => false,
                    'message' => 'User not found.',
                ], 401);
            }

            if (! $user->status) {
                return response()->json([
                    'success' => false,
                    'valid'   => false,
                    'message' => 'User account is inactive.',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'valid'   => true,
                'data'    => [
                    'user'    => new UserResource($user->load('roles', 'permissions')),
                    'payload' => [
                        'sub'         => $payload->get('sub'),
                        'iat'         => $payload->get('iat'),
                        'exp'         => $payload->get('exp'),
                        'roles'       => $payload->get('roles'),
                        'permissions' => $payload->get('permissions'),
                    ],
                ],
            ]);

        } catch (TokenExpiredException) {
            return response()->json([
                'success' => false,
                'valid'   => false,
                'message' => 'Token has expired.',
            ], 401);

        } catch (TokenInvalidException) {
            return response()->json([
                'success' => false,
                'valid'   => false,
                'message' => 'Token is invalid.',
            ], 401);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'valid'   => false,
                'message' => 'Token could not be parsed.',
            ], 401);
        }
    }

    // ─── Public key ──────────────────────────────────────────────────────────

    /**
     * Return the RS256 public key.
     *
     * Other services can call this once on startup, cache the key,
     * and then validate tokens locally — zero network calls per request.
     */
    public function publicKey(): JsonResponse
    {
        $publicKey = config('jwt.public_key');

        if (! $publicKey) {
            return response()->json([
                'success' => false,
                'message' => 'Public key is not configured.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'public_key' => $publicKey,
                'algorithm'  => config('jwt.algo', 'RS256'),
            ],
        ]);
    }
}
