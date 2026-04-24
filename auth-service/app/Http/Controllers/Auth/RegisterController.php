<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name'   => $request->name,
                'email'  => $request->email,
                'password' => $request->password, // cast 'hashed' handles bcrypt
                'phone'  => $request->phone,
                'status' => true,
            ]);

            // Assign the default role for new self-registered users.
            // Admins can elevate roles later via a separate endpoint.
            $user->assignRole('participant');

            DB::commit();

            $token = auth('api')->login($user);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful.',
                'data'    => [
                    'access_token' => $token,
                    'token_type'   => 'bearer',
                    'expires_in'   => auth('api')->factory()->getTTL() * 60,
                    'user'         => new UserResource($user->load('roles', 'permissions')),
                ],
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error'   => app()->isLocal() ? $e->getMessage() : null,
            ], 500);
        }
    }
}
