<?php
declare(strict_types=1);
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class Pembayaran extends Model { protected $table='pembayaran'; protected $fillable=['nim','kode_tagihan','jenis_pembayaran','nominal','jatuh_tempo','tanggal_bayar','status_pembayaran','metode_pembayaran']; protected $casts=['nominal'=>'decimal:2','jatuh_tempo'=>'date','tanggal_bayar'=>'date']; }
