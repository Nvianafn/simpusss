<?php
declare(strict_types=1);
namespace Database\Seeders; use App\Models\Pembayaran; use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder { public function run(): void { Pembayaran::firstOrCreate(['kode_tagihan'=>'PPL-234110601088-2026'], ['nim'=>'234110601088','jenis_pembayaran'=>'biaya_ppl','nominal'=>750000,'jatuh_tempo'=>'2026-07-01','tanggal_bayar'=>'2026-06-11','status_pembayaran'=>'lunas','metode_pembayaran'=>'transfer']); } }
