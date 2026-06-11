<?php
declare(strict_types=1);
namespace Database\Seeders; use App\Models\HasilKesehatan; use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder { public function run(): void { HasilKesehatan::firstOrCreate(['nim'=>'234110601088','tanggal_cek'=>'2026-06-11'], ['tinggi_badan'=>170,'berat_badan'=>65,'tekanan_darah'=>'120/80','hasil_pemeriksaan'=>'Sehat dan layak mengikuti PPL.','status_kesehatan'=>'layak']); } }
