<?php
declare(strict_types=1);
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Mahasiswa extends Model { protected $table='mahasiswa'; protected $fillable=['nim','nama','email','prodi','fakultas','semester','angkatan','status']; protected $casts=['semester'=>'integer','angkatan'=>'integer']; }
