<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

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
