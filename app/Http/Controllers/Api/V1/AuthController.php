<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->firstOrFail();

        if (!Hash::check($request->password, $user->password)) {
            Log::warning('Failed login attempt', ['email' => $request->email, 'device' => $request->device_name]);

            return response()->json(
                ['error' => 'The provided credentials are incorrect'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        try {
            $user->tokens()->where('name', $request->device_name)->delete();
            $token = $user->createToken($request->device_name);
        } catch (Throwable $e) {
            Log::error('Failed to login user: ' . $e->getMessage());

            return response()->json(
                ['error' => 'Unable to process the token request'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token->plainTextToken,
                'user' => $user->only(['id', 'email', 'name'])
            ]
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['status' => true, 'message' => 'Logged out successfully']);
    }
}
