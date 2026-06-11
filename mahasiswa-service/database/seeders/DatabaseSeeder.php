<?php
declare(strict_types=1);
namespace Database\Seeders; use App\Models\Mahasiswa; use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder { public function run(): void { Mahasiswa::firstOrCreate(['nim'=>'234110601088'], ['nama'=>'Budi Santoso','email'=>'budi@simpus.test','prodi'=>'Informatika','fakultas'=>'Saintek','semester'=>6,'angkatan'=>2023,'status'=>'aktif']); } }
