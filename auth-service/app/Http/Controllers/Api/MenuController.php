<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Models\Menu; use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;
class MenuController extends Controller {
    private function ok(string $message, mixed $data = null, int $code = 200): JsonResponse
    {
        return response()->json(['status' => 'success', 'message' => $message, 'data' => $data], $code);
    }

    private function fail(string $message, mixed $data = null, int $code = 422): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message, 'data' => $data], $code);
    }
 public function index(): JsonResponse { return $this->ok('Daftar menu', Menu::orderBy('order')->get()); } public function store(Request $request): JsonResponse { return $this->ok('Menu dibuat', Menu::create($request->validate(['label'=>'required|string','route'=>'required|string','icon'=>'nullable|string','parent_id'=>'nullable|exists:menus,id','order'=>'sometimes|integer|min:0'])), 201); } }
