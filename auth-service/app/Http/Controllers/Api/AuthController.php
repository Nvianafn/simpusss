<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private function ok(string $message, mixed $data = null, int $code = 200): JsonResponse
    {
        return response()->json(['status' => 'success', 'message' => $message, 'data' => $data], $code);
    }

    private function fail(string $message, mixed $data = null, int $code = 422): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message, 'data' => $data], $code);
    }


    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        $user = User::with(['role.permissions', 'role.menus'])->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password) || ! $user->is_active) {
            return $this->fail('Email, password, atau status akun tidak valid.', null, 401);
        }

        $token = $user->createToken('simpus-api')->plainTextToken;

        return $this->ok('Login berhasil', ['token' => $token, 'user' => $user]);
    }

    public function me(Request $request): JsonResponse
    {
        return $this->ok('User aktif', $request->user()->load(['role.permissions', 'role.menus']));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        return $this->ok('Logout berhasil');
    }
}
