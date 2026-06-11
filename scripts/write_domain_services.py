from pathlib import Path
H="""
    private function ok(string $message, mixed $data = null, int $code = 200): JsonResponse
    { return response()->json(['status' => 'success', 'message' => $message, 'data' => $data], $code); }
    private function fail(string $message, mixed $data = null, int $code = 422): JsonResponse
    { return response()->json(['status' => 'error', 'message' => $message, 'data' => $data], $code); }
"""
def w(p,t): Path(p).parent.mkdir(parents=True,exist_ok=True); Path(p).write_text(t)
# mahasiswa
w('mahasiswa-service/app/Models/Mahasiswa.php', '''<?php
declare(strict_types=1);
namespace App\\Models;
use Illuminate\\Database\\Eloquent\\Model;
class Mahasiswa extends Model { protected $table='mahasiswa'; protected $fillable=['nim','nama','email','prodi','fakultas','semester','angkatan','status']; protected $casts=['semester'=>'integer','angkatan'=>'integer']; }
''')
w('mahasiswa-service/database/migrations/2026_06_11_000001_create_mahasiswa_table.php', '''<?php
declare(strict_types=1);
use Illuminate\\Database\\Migrations\\Migration; use Illuminate\\Database\\Schema\\Blueprint; use Illuminate\\Support\\Facades\\Schema;
return new class extends Migration { public function up(): void { Schema::create('mahasiswa', function(Blueprint $table): void { $table->id(); $table->string('nim')->unique(); $table->string('nama'); $table->string('email')->unique(); $table->string('prodi')->index(); $table->string('fakultas')->index(); $table->unsignedTinyInteger('semester')->index(); $table->year('angkatan')->index(); $table->enum('status',['aktif','non_aktif','cuti','lulus'])->default('aktif')->index(); $table->timestamps(); }); } public function down(): void { Schema::dropIfExists('mahasiswa'); } };
''')
w('mahasiswa-service/app/Http/Controllers/Api/MahasiswaController.php', f'''<?php
declare(strict_types=1);
namespace App\\Http\\Controllers\\Api;
use App\\Http\\Controllers\\Controller; use App\\Models\\Mahasiswa; use Illuminate\\Http\\JsonResponse; use Illuminate\\Http\\Request; use Illuminate\\Validation\\Rule;
class MahasiswaController extends Controller {{{H}
 public function index(Request $request): JsonResponse {{ $query=Mahasiswa::query(); foreach(['prodi','fakultas','angkatan','semester','status'] as $field) {{ if($request->filled($field)) $query->where($field,$request->input($field)); }} if($request->filled('q')) {{ $term='%'.$request->input('q').'%'; $query->where(fn($q)=>$q->where('nim','like',$term)->orWhere('nama','like',$term)); }} return $this->ok('Daftar mahasiswa',$query->latest()->paginate($request->integer('per_page',15))); }}
 public function store(Request $request): JsonResponse {{ $data=$request->validate(['nim'=>'required|string|max:32|unique:mahasiswa,nim','nama'=>'required|string|max:255','email'=>'required|email|unique:mahasiswa,email','prodi'=>'required|string|max:255','fakultas'=>'required|string|max:255','semester'=>'required|integer|min:1|max:14','angkatan'=>'required|integer|min:2000|max:2100','status'=>['required',Rule::in(['aktif','non_aktif','cuti','lulus'])]]); return $this->ok('Mahasiswa dibuat',Mahasiswa::create($data),201); }}
 public function show(string $nim): JsonResponse {{ $m=Mahasiswa::where('nim',$nim)->first(); return $m?$this->ok('Detail mahasiswa',$m):$this->fail('Mahasiswa tidak ditemukan',null,404); }}
 public function update(Request $request,string $nim): JsonResponse {{ $m=Mahasiswa::where('nim',$nim)->firstOrFail(); $data=$request->validate(['nama'=>'sometimes|string|max:255','email'=>['sometimes','email',Rule::unique('mahasiswa','email')->ignore($m->id)],'prodi'=>'sometimes|string|max:255','fakultas'=>'sometimes|string|max:255','semester'=>'sometimes|integer|min:1|max:14','angkatan'=>'sometimes|integer|min:2000|max:2100','status'=>['sometimes',Rule::in(['aktif','non_aktif','cuti','lulus'])]]); $m->update($data); return $this->ok('Mahasiswa diperbarui',$m->fresh()); }}
 public function destroy(string $nim): JsonResponse {{ Mahasiswa::where('nim',$nim)->firstOrFail()->delete(); return $this->ok('Mahasiswa dihapus'); }}
 public function status(string $nim): JsonResponse {{ $m=Mahasiswa::where('nim',$nim)->first(); return $m?$this->ok('Status mahasiswa',['nim'=>$nim,'status'=>$m->status,'is_active'=>$m->status==='aktif']):$this->fail('Mahasiswa tidak ditemukan',['nim'=>$nim,'is_active'=>false],404); }}
}}
''')
w('mahasiswa-service/routes/api.php', '''<?php
declare(strict_types=1);
use App\\Http\\Controllers\\Api\\MahasiswaController; use Illuminate\\Support\\Facades\\Route;
Route::get('/mahasiswa/{nim}/status',[MahasiswaController::class,'status']); Route::apiResource('mahasiswa',MahasiswaController::class)->parameters(['mahasiswa'=>'nim']);
''')
w('mahasiswa-service/database/seeders/DatabaseSeeder.php', '''<?php
declare(strict_types=1);
namespace Database\\Seeders; use App\\Models\\Mahasiswa; use Illuminate\\Database\\Seeder;
class DatabaseSeeder extends Seeder { public function run(): void { Mahasiswa::firstOrCreate(['nim'=>'234110601088'], ['nama'=>'Budi Santoso','email'=>'budi@simpus.test','prodi'=>'Informatika','fakultas'=>'Saintek','semester'=>6,'angkatan'=>2023,'status'=>'aktif']); } }
''')
# Klinik
w('klinik-service/app/Models/HasilKesehatan.php', '''<?php
declare(strict_types=1);
namespace App\\Models; use Illuminate\\Database\\Eloquent\\Model;
class HasilKesehatan extends Model { protected $table='hasil_kesehatan'; protected $fillable=['nim','tanggal_cek','tinggi_badan','berat_badan','tekanan_darah','hasil_pemeriksaan','status_kesehatan']; protected $casts=['tanggal_cek'=>'date','tinggi_badan'=>'integer','berat_badan'=>'integer']; }
''')
w('klinik-service/database/migrations/2026_06_11_000001_create_hasil_kesehatan_table.php', '''<?php
declare(strict_types=1);
use Illuminate\\Database\\Migrations\\Migration; use Illuminate\\Database\\Schema\\Blueprint; use Illuminate\\Support\\Facades\\Schema; return new class extends Migration { public function up(): void { Schema::create('hasil_kesehatan', function(Blueprint $table): void { $table->id(); $table->string('nim')->index(); $table->date('tanggal_cek')->index(); $table->unsignedSmallInteger('tinggi_badan'); $table->unsignedSmallInteger('berat_badan'); $table->string('tekanan_darah'); $table->text('hasil_pemeriksaan'); $table->enum('status_kesehatan',['layak','tidak_layak'])->index(); $table->timestamps(); }); } public function down(): void { Schema::dropIfExists('hasil_kesehatan'); } };
''')
w('klinik-service/app/Http/Controllers/Api/KesehatanController.php', f'''<?php
declare(strict_types=1);
namespace App\\Http\\Controllers\\Api; use App\\Http\\Controllers\\Controller; use App\\Models\\HasilKesehatan; use Illuminate\\Http\\JsonResponse; use Illuminate\\Http\\Request; use Illuminate\\Validation\\Rule;
class KesehatanController extends Controller {{{H}
 public function latest(string $nim): JsonResponse {{ $h=HasilKesehatan::where('nim',$nim)->latest('tanggal_cek')->latest('id')->first(); return $h?$this->ok('Status kesehatan terkini',['nim'=>$nim,'is_eligible'=>$h->status_kesehatan==='layak','pemeriksaan'=>$h]):$this->fail('Belum ada pemeriksaan kesehatan',['nim'=>$nim,'is_eligible'=>false],404); }}
 public function history(string $nim): JsonResponse {{ return $this->ok('Riwayat kesehatan',HasilKesehatan::where('nim',$nim)->latest('tanggal_cek')->paginate(15)); }}
 public function store(Request $request): JsonResponse {{ $data=$request->validate(['nim'=>'required|string','tanggal_cek'=>'required|date','tinggi_badan'=>'required|integer|min:1','berat_badan'=>'required|integer|min:1','tekanan_darah'=>'required|string|max:32','hasil_pemeriksaan'=>'required|string','status_kesehatan'=>['required',Rule::in(['layak','tidak_layak'])]]); return $this->ok('Hasil pemeriksaan dibuat',HasilKesehatan::create($data),201); }}
 public function update(Request $request,int $id): JsonResponse {{ $h=HasilKesehatan::findOrFail($id); $data=$request->validate(['tanggal_cek'=>'sometimes|date','tinggi_badan'=>'sometimes|integer|min:1','berat_badan'=>'sometimes|integer|min:1','tekanan_darah'=>'sometimes|string|max:32','hasil_pemeriksaan'=>'sometimes|string','status_kesehatan'=>['sometimes',Rule::in(['layak','tidak_layak'])]]); $h->update($data); return $this->ok('Hasil pemeriksaan diperbarui',$h->fresh()); }}
}}
''')
w('klinik-service/routes/api.php', '''<?php
declare(strict_types=1);
use App\\Http\\Controllers\\Api\\KesehatanController; use Illuminate\\Support\\Facades\\Route;
Route::get('/kesehatan/{nim}',[KesehatanController::class,'latest']); Route::get('/kesehatan/{nim}/riwayat',[KesehatanController::class,'history']); Route::post('/kesehatan',[KesehatanController::class,'store']); Route::put('/kesehatan/{id}',[KesehatanController::class,'update'])->whereNumber('id');
''')
w('klinik-service/database/seeders/DatabaseSeeder.php', '''<?php
declare(strict_types=1);
namespace Database\\Seeders; use App\\Models\\HasilKesehatan; use Illuminate\\Database\\Seeder;
class DatabaseSeeder extends Seeder { public function run(): void { HasilKesehatan::firstOrCreate(['nim'=>'234110601088','tanggal_cek'=>'2026-06-11'], ['tinggi_badan'=>170,'berat_badan'=>65,'tekanan_darah'=>'120/80','hasil_pemeriksaan'=>'Sehat dan layak mengikuti PPL.','status_kesehatan'=>'layak']); } }
''')
print('domain part 1 written')
