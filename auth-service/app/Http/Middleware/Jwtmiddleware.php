<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired.',
            ], 401);
        } catch (TokenInvalidException) {
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid.',
            ], 401);
        } catch (JWTException) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization token not found.',
            ], 401);
        }

        return $next($request);
    }
}
