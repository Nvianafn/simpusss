from pathlib import Path

HELPERS = """
    private function ok(string $message, mixed $data = null, int $code = 200): JsonResponse
    {
        return response()->json(['status' => 'success', 'message' => $message, 'data' => $data], $code);
    }

    private function fail(string $message, mixed $data = null, int $code = 422): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message, 'data' => $data], $code);
    }
"""

def w(path: str, text: str):
    p = Path(path)
    p.parent.mkdir(parents=True, exist_ok=True)
    p.write_text(text)

# Auth service
w('auth-service/app/Models/Role.php', '''<?php

declare(strict_types=1);

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name', 'slug'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'role_menus');
    }
}
''')
w('auth-service/app/Models/Permission.php', '''<?php

declare(strict_types=1);

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'slug'];
}
''')
w('auth-service/app/Models/Menu.php', '''<?php

declare(strict_types=1);

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class Menu extends Model
{
    protected $fillable = ['label', 'route', 'icon', 'parent_id', 'order'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
''')
w('auth-service/app/Models/User.php', '''<?php

declare(strict_types=1);

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Foundation\\Auth\\User as Authenticatable;
use Illuminate\\Notifications\\Notifiable;
use Laravel\\Sanctum\\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role_id', 'nim', 'is_active'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['is_active' => 'boolean', 'password' => 'hashed'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
''')
w('auth-service/database/migrations/2026_06_11_000001_create_auth_tables.php', '''<?php

declare(strict_types=1);

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table): void {
            $table->id();
            $table->string('label');
            $table->string('route');
            $table->string('icon')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('menus')->nullOnDelete();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->string('nim')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('role_menus', function (Blueprint $table): void {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->primary(['role_id', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_menus');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
''')
w('auth-service/app/Http/Controllers/Api/AuthController.php', f'''<?php

declare(strict_types=1);

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use App\\Models\\User;
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Hash;

class AuthController extends Controller
{{{HELPERS}

    public function login(Request $request): JsonResponse
    {{
        $credentials = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        $user = User::with(['role.permissions', 'role.menus'])->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password) || ! $user->is_active) {{
            return $this->fail('Email, password, atau status akun tidak valid.', null, 401);
        }}

        $token = $user->createToken('simpus-api')->plainTextToken;

        return $this->ok('Login berhasil', ['token' => $token, 'user' => $user]);
    }}

    public function me(Request $request): JsonResponse
    {{
        return $this->ok('User aktif', $request->user()->load(['role.permissions', 'role.menus']));
    }}

    public function logout(Request $request): JsonResponse
    {{
        $request->user()->currentAccessToken()?->delete();
        return $this->ok('Logout berhasil');
    }}
}}
''')
w('auth-service/app/Http/Controllers/Api/UserController.php', f'''<?php

declare(strict_types=1);

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use App\\Models\\User;
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Validation\\Rule;

class UserController extends Controller
{{{HELPERS}

    public function index(Request $request): JsonResponse
    {{
        return $this->ok('Daftar user', User::with('role')->latest()->paginate($request->integer('per_page', 15)));
    }}

    public function store(Request $request): JsonResponse
    {{
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:users,email', 'password' => 'required|string|min:8', 'role_id' => 'required|exists:roles,id', 'nim' => 'nullable|string', 'is_active' => 'sometimes|boolean']);
        return $this->ok('User dibuat', User::create($data)->load('role'), 201);
    }}

    public function show(int $id): JsonResponse
    {{
        return $this->ok('Detail user', User::with('role')->findOrFail($id));
    }}

    public function update(Request $request, int $id): JsonResponse
    {{
        $user = User::findOrFail($id);
        $data = $request->validate(['name' => 'sometimes|string|max:255', 'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)], 'password' => 'sometimes|string|min:8', 'role_id' => 'sometimes|exists:roles,id', 'nim' => 'nullable|string', 'is_active' => 'sometimes|boolean']);
        $user->update($data);
        return $this->ok('User diperbarui', $user->fresh('role'));
    }}

    public function destroy(int $id): JsonResponse
    {{
        User::findOrFail($id)->delete();
        return $this->ok('User dihapus');
    }}
}}
''')
w('auth-service/app/Http/Controllers/Api/RoleController.php', f'''<?php

declare(strict_types=1);

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use App\\Models\\Role;
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Validation\\Rule;

class RoleController extends Controller
{{{HELPERS}
    public function index(): JsonResponse {{ return $this->ok('Daftar role', Role::with(['permissions', 'menus'])->get()); }}
    public function store(Request $request): JsonResponse {{ $data = $request->validate(['name' => 'required|string', 'slug' => 'required|string|unique:roles,slug']); return $this->ok('Role dibuat', Role::create($data), 201); }}
    public function update(Request $request, int $id): JsonResponse {{ $role = Role::findOrFail($id); $data = $request->validate(['name' => 'sometimes|string', 'slug' => ['sometimes', 'string', Rule::unique('roles', 'slug')->ignore($role->id)]]); $role->update($data); return $this->ok('Role diperbarui', $role); }}
    public function destroy(int $id): JsonResponse {{ Role::findOrFail($id)->delete(); return $this->ok('Role dihapus'); }}
    public function syncPermissions(Request $request, int $id): JsonResponse {{ $data = $request->validate(['permission_ids' => 'array', 'permission_ids.*' => 'exists:permissions,id']); $role = Role::findOrFail($id); $role->permissions()->sync($data['permission_ids'] ?? []); return $this->ok('Permission role diperbarui', $role->load('permissions')); }}
    public function syncMenus(Request $request, int $id): JsonResponse {{ $data = $request->validate(['menu_ids' => 'array', 'menu_ids.*' => 'exists:menus,id']); $role = Role::findOrFail($id); $role->menus()->sync($data['menu_ids'] ?? []); return $this->ok('Menu role diperbarui', $role->load('menus')); }}
}}
''')
w('auth-service/app/Http/Controllers/Api/PermissionController.php', f'''<?php

declare(strict_types=1);

namespace App\\Http\\Controllers\\Api;
use App\\Http\\Controllers\\Controller; use App\\Models\\Permission; use Illuminate\\Http\\JsonResponse; use Illuminate\\Http\\Request;
class PermissionController extends Controller {{{HELPERS} public function index(): JsonResponse {{ return $this->ok('Daftar permission', Permission::all()); }} public function store(Request $request): JsonResponse {{ return $this->ok('Permission dibuat', Permission::create($request->validate(['name'=>'required|string','slug'=>'required|string|unique:permissions,slug'])), 201); }} }}
''')
w('auth-service/app/Http/Controllers/Api/MenuController.php', f'''<?php

declare(strict_types=1);

namespace App\\Http\\Controllers\\Api;
use App\\Http\\Controllers\\Controller; use App\\Models\\Menu; use Illuminate\\Http\\JsonResponse; use Illuminate\\Http\\Request;
class MenuController extends Controller {{{HELPERS} public function index(): JsonResponse {{ return $this->ok('Daftar menu', Menu::orderBy('order')->get()); }} public function store(Request $request): JsonResponse {{ return $this->ok('Menu dibuat', Menu::create($request->validate(['label'=>'required|string','route'=>'required|string','icon'=>'nullable|string','parent_id'=>'nullable|exists:menus,id','order'=>'sometimes|integer|min:0'])), 201); }} }}
''')
w('auth-service/routes/api.php', '''<?php

declare(strict_types=1);

use App\\Http\\Controllers\\Api\\AuthController;
use App\\Http\\Controllers\\Api\\MenuController;
use App\\Http\\Controllers\\Api\\PermissionController;
use App\\Http\\Controllers\\Api\\RoleController;
use App\\Http\\Controllers\\Api\\UserController;
use Illuminate\\Support\\Facades\\Route;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', RoleController::class)->except(['show']);
    Route::put('/roles/{id}/permissions', [RoleController::class, 'syncPermissions']);
    Route::put('/roles/{id}/menus', [RoleController::class, 'syncMenus']);
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::get('/menus', [MenuController::class, 'index']);
    Route::post('/menus', [MenuController::class, 'store']);
});
''')
w('auth-service/database/seeders/DatabaseSeeder.php', '''<?php

declare(strict_types=1);

namespace Database\\Seeders;

use App\\Models\\Menu;
use App\\Models\\Permission;
use App\\Models\\Role;
use App\\Models\\User;
use Illuminate\\Database\\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = collect(['super_admin','admin_mahasiswa','admin_ppl','admin_klinik','admin_bank','mahasiswa'])->mapWithKeys(fn (string $slug) => [$slug => Role::firstOrCreate(['slug' => $slug], ['name' => str($slug)->replace('_', ' ')->title()->toString()])]);
        $permissions = collect(['users.manage','roles.manage','mahasiswa.manage','ppl.manage','klinik.manage','bank.manage','portal.access'])->map(fn (string $slug) => Permission::firstOrCreate(['slug' => $slug], ['name' => str($slug)->replace('.', ' ')->title()->toString()]));
        $menus = collect([['Dashboard','/dashboard','layout-dashboard'],['Mahasiswa','/mahasiswa','graduation-cap'],['PPL','/ppl','briefcase'],['Klinik','/klinik','heart-pulse'],['Bank','/bank','wallet'],['Users','/users','users']])->map(fn (array $m, int $i) => Menu::firstOrCreate(['route' => $m[1]], ['label' => $m[0], 'icon' => $m[2], 'order' => $i]));
        $roles['super_admin']->permissions()->sync($permissions->pluck('id'));
        $roles['super_admin']->menus()->sync($menus->pluck('id'));
        User::firstOrCreate(['email' => 'admin@simpus.test'], ['name' => 'Super Admin', 'password' => 'password123', 'role_id' => $roles['super_admin']->id, 'is_active' => true]);
    }
}
''')

print('auth written')
