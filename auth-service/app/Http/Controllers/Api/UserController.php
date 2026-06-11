<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private function ok(string $message, mixed $data = null, int $code = 200): JsonResponse
    {
        return response()->json(['status' => 'success', 'message' => $message, 'data' => $data], $code);
    }

    private function fail(string $message, mixed $data = null, int $code = 422): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message, 'data' => $data], $code);
    }


    public function index(Request $request): JsonResponse
    {
        return $this->ok('Daftar user', User::with('role')->latest()->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email', 'password' => 'required|string|min:8', 'role_id' => 'required|exists:roles,id', 'nim' => 'nullable|string', 'is_active' => 'sometimes|boolean']);
        return $this->ok('User dibuat', User::create($data)->load('role'), 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->ok('Detail user', User::with('role')->findOrFail($id));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $data = $request->validate(['name' => 'sometimes|string|max:255', 'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)], 'password' => 'sometimes|string|min:8', 'role_id' => 'sometimes|exists:roles,id', 'nim' => 'nullable|string', 'is_active' => 'sometimes|boolean']);
        $user->update($data);
        return $this->ok('User diperbarui', $user->fresh('role'));
    }

    public function destroy(int $id): JsonResponse
    {
        User::findOrFail($id)->delete();
        return $this->ok('User dihapus');
    }
}
