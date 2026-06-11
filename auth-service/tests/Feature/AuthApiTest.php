<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_with_sanctum_token(): void
    {
        $role = Role::create(['name' => 'Super Admin', 'slug' => 'super_admin']);
        User::create(['name'=>'Admin','email'=>'admin@simpus.test','password'=>'password123','role_id'=>$role->id,'is_active'=>true]);
        $this->postJson('/api/auth/login', ['email'=>'admin@simpus.test','password'=>'password123'])->assertOk()->assertJsonPath('status', 'success')->assertJsonStructure(['data'=>['token','user']]);
    }
}
