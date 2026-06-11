<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('mahasiswa', function(Blueprint $table): void { $table->id(); $table->string('nim')->unique(); $table->string('nama'); $table->string('email')->unique(); $table->string('prodi')->index(); $table->string('fakultas')->index(); $table->unsignedTinyInteger('semester')->index(); $table->year('angkatan')->index(); $table->enum('status',['aktif','non_aktif','cuti','lulus'])->default('aktif')->index(); $table->timestamps(); }); } public function down(): void { Schema::dropIfExists('mahasiswa'); } };
