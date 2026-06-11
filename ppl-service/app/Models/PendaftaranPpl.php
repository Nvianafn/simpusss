<?php
declare(strict_types=1);
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PendaftaranPpl extends Model { protected $table='pendaftaran_ppl'; protected $fillable=['nim','lokasi_ppl','tahun_ajaran','tanggal_daftar','status_pendaftaran','catatan']; protected $casts=['tanggal_daftar'=>'date']; }
