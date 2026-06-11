<?php
declare(strict_types=1);
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class HasilKesehatan extends Model { protected $table='hasil_kesehatan'; protected $fillable=['nim','tanggal_cek','tinggi_badan','berat_badan','tekanan_darah','hasil_pemeriksaan','status_kesehatan']; protected $casts=['tanggal_cek'=>'date','tinggi_badan'=>'integer','berat_badan'=>'integer']; }
