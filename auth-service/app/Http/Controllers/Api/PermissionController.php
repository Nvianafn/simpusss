<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Models\Permission; use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;
class PermissionController extends Controller {
    private function ok(string $message, mixed $data = null, int $code = 200): JsonResponse
    {
        return response()->json(['status' => 'success', 'message' => $message, 'data' => $data], $code);
    }

    private function fail(string $message, mixed $data = null, int $code = 422): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message, 'data' => $data], $code);
    }
 public function index(): JsonResponse { return $this->ok('Daftar permission', Permission::all()); } public function store(Request $request): JsonResponse { return $this->ok('Permission dibuat', Permission::create($request->validate(['name'=>'required|string','slug'=>'required|string|unique:permissions,slug'])), 201); } }
