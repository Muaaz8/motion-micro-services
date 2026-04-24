<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'No token provided'], 401);
        }

        // Call auth-service to validate the token
        $authResponse = Http::withToken($token)
            ->post(config('services.auth.url') . '/api/token/validate');

        if (!$authResponse->ok()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Attach the authenticated user data to the request
        // so downstream services can receive it as a header
        $request->merge(['auth_user' => $authResponse->json()]);
        $request->headers->set('X-Auth-User', json_encode($authResponse->json()));

        return $next($request);
    }
}
