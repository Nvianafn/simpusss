<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    private function ok(string $message, mixed $data = null, int $code = 200): JsonResponse
    {
        return response()->json(['status' => 'success', 'message' => $message, 'data' => $data], $code);
    }

    private function fail(string $message, mixed $data = null, int $code = 422): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message, 'data' => $data], $code);
    }

    public function index(): JsonResponse { return $this->ok('Daftar role', Role::with(['permissions', 'menus'])->get()); }
    public function store(Request $request): JsonResponse { $data = $request->validate(['name' => 'required|string', 'slug' => 'required|string|unique:roles,slug']); return $this->ok('Role dibuat', Role::create($data), 201); }
    public function update(Request $request, int $id): JsonResponse { $role = Role::findOrFail($id); $data = $request->validate(['name' => 'sometimes|string', 'slug' => ['sometimes', 'string', Rule::unique('roles', 'slug')->ignore($role->id)]]); $role->update($data); return $this->ok('Role diperbarui', $role); }
    public function destroy(int $id): JsonResponse { Role::findOrFail($id)->delete(); return $this->ok('Role dihapus'); }
    public function syncPermissions(Request $request, int $id): JsonResponse { $data = $request->validate(['permission_ids' => 'array', 'permission_ids.*' => 'exists:permissions,id']); $role = Role::findOrFail($id); $role->permissions()->sync($data['permission_ids'] ?? []); return $this->ok('Permission role diperbarui', $role->load('permissions')); }
    public function syncMenus(Request $request, int $id): JsonResponse { $data = $request->validate(['menu_ids' => 'array', 'menu_ids.*' => 'exists:menus,id']); $role = Role::findOrFail($id); $role->menus()->sync($data['menu_ids'] ?? []); return $this->ok('Menu role diperbarui', $role->load('menus')); }
}
