<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $result['user'],
            'access_token' => $result['access_token'],
            'refresh_token' => $result['refresh_token']
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        if (!$result['success']) {
            return ApiResponse::error('Invalid credentials', null, 401);
        }

        return ApiResponse::success('Login success', [
            'username' => $result['user']->username,
            'email' => $result['user']->email,
            'access_token' => $result['access_token'],
            'refresh_token' => $result['refresh_token']
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $refreshToken = $request->input('refresh_token');

        $user = User::where('refresh_token', $refreshToken)->first();

        if (!$user) {
            return ApiResponse::error('Invalid refresh token', null, 401);
        }

        $newAccessToken = JWTAuth::fromUser($user);
        $newRefreshToken = Str::random(60);

        $user->update(['refresh_token' => $newRefreshToken]);

        return ApiResponse::success('Token refreshed', [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken
        ]);
    }


    public function me(): JsonResponse
    {
        return response()->json(auth()->guard('api')->user());
    }
}
