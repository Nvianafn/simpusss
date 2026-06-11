from pathlib import Path
H="""
    private function ok(string $message, mixed $data = null, int $code = 200): JsonResponse
    { return response()->json(['status' => 'success', 'message' => $message, 'data' => $data], $code); }
    private function fail(string $message, mixed $data = null, int $code = 422): JsonResponse
    { return response()->json(['status' => 'error', 'message' => $message, 'data' => $data], $code); }
"""
def w(p,t): Path(p).parent.mkdir(parents=True,exist_ok=True); Path(p).write_text(t)
# Bank
w('bank-service/app/Models/Pembayaran.php', '''<?php
declare(strict_types=1);
namespace App\\Models; use Illuminate\\Database\\Eloquent\\Model;
class Pembayaran extends Model { protected $table='pembayaran'; protected $fillable=['nim','kode_tagihan','jenis_pembayaran','nominal','jatuh_tempo','tanggal_bayar','status_pembayaran','metode_pembayaran']; protected $casts=['nominal'=>'decimal:2','jatuh_tempo'=>'date','tanggal_bayar'=>'date']; }
''')
w('bank-service/database/migrations/2026_06_11_000001_create_pembayaran_table.php', '''<?php
declare(strict_types=1);
use Illuminate\\Database\\Migrations\\Migration; use Illuminate\\Database\\Schema\\Blueprint; use Illuminate\\Support\\Facades\\Schema; return new class extends Migration { public function up(): void { Schema::create('pembayaran', function(Blueprint $table): void { $table->id(); $table->string('nim')->index(); $table->string('kode_tagihan')->unique(); $table->string('jenis_pembayaran')->index(); $table->decimal('nominal',12,2); $table->date('jatuh_tempo')->nullable()->index(); $table->date('tanggal_bayar')->nullable(); $table->enum('status_pembayaran',['belum_bayar','lunas'])->default('belum_bayar')->index(); $table->string('metode_pembayaran')->nullable(); $table->timestamps(); }); } public function down(): void { Schema::dropIfExists('pembayaran'); } };
''')
w('bank-service/app/Http/Controllers/Api/PembayaranController.php', f'''<?php
declare(strict_types=1);
namespace App\\Http\\Controllers\\Api; use App\\Http\\Controllers\\Controller; use App\\Models\\Pembayaran; use Illuminate\\Http\\JsonResponse; use Illuminate\\Http\\Request;
class PembayaranController extends Controller {{{H}
 public function index(Request $request): JsonResponse {{ $query=Pembayaran::query(); foreach(['nim','jenis_pembayaran','status_pembayaran'] as $field) {{ if($request->filled($field)) $query->where($field,$request->input($field)); }} return $this->ok('Daftar pembayaran',$query->latest()->paginate($request->integer('per_page',15))); }}
 public function byNim(string $nim): JsonResponse {{ return $this->ok('Tagihan mahasiswa',Pembayaran::where('nim',$nim)->latest()->get()); }}
 public function store(Request $request): JsonResponse {{ $data=$request->validate(['nim'=>'required|string','kode_tagihan'=>'required|string|unique:pembayaran,kode_tagihan','jenis_pembayaran'=>'required|string','nominal'=>'required|numeric|min:0','jatuh_tempo'=>'nullable|date']); $data['status_pembayaran']='belum_bayar'; return $this->ok('Tagihan dibuat',Pembayaran::create($data),201); }}
 public function confirm(Request $request,int $id): JsonResponse {{ $p=Pembayaran::findOrFail($id); $data=$request->validate(['metode_pembayaran'=>'required|string|max:255','tanggal_bayar'=>'required|date']); $p->update($data + ['status_pembayaran'=>'lunas']); return $this->ok('Pembayaran dikonfirmasi',$p->fresh()); }}
 public function status(Request $request,string $nim): JsonResponse {{ $jenis=(string)$request->query('jenis_pembayaran','biaya_ppl'); $paid=Pembayaran::where('nim',$nim)->where('jenis_pembayaran',$jenis)->where('status_pembayaran','lunas')->exists(); return $this->ok('Status pembayaran',['nim'=>$nim,'jenis_pembayaran'=>$jenis,'is_paid'=>$paid]); }}
}}
''')
w('bank-service/routes/api.php', '''<?php
declare(strict_types=1);
use App\\Http\\Controllers\\Api\\PembayaranController; use Illuminate\\Support\\Facades\\Route;
Route::get('/pembayaran',[PembayaranController::class,'index']); Route::post('/pembayaran',[PembayaranController::class,'store']); Route::get('/pembayaran/{nim}/status',[PembayaranController::class,'status']); Route::get('/pembayaran/{nim}',[PembayaranController::class,'byNim']); Route::put('/pembayaran/{id}/konfirmasi',[PembayaranController::class,'confirm'])->whereNumber('id');
''')
w('bank-service/database/seeders/DatabaseSeeder.php', '''<?php
declare(strict_types=1);
namespace Database\\Seeders; use App\\Models\\Pembayaran; use Illuminate\\Database\\Seeder;
class DatabaseSeeder extends Seeder { public function run(): void { Pembayaran::firstOrCreate(['kode_tagihan'=>'PPL-234110601088-2026'], ['nim'=>'234110601088','jenis_pembayaran'=>'biaya_ppl','nominal'=>750000,'jatuh_tempo'=>'2026-07-01','tanggal_bayar'=>'2026-06-11','status_pembayaran'=>'lunas','metode_pembayaran'=>'transfer']); } }
''')
# PPL
w('ppl-service/app/Models/PendaftaranPpl.php', '''<?php
declare(strict_types=1);
namespace App\\Models; use Illuminate\\Database\\Eloquent\\Model;
class PendaftaranPpl extends Model { protected $table='pendaftaran_ppl'; protected $fillable=['nim','lokasi_ppl','tahun_ajaran','tanggal_daftar','status_pendaftaran','catatan']; protected $casts=['tanggal_daftar'=>'date']; }
''')
w('ppl-service/database/migrations/2026_06_11_000001_create_pendaftaran_ppl_table.php', '''<?php
declare(strict_types=1);
use Illuminate\\Database\\Migrations\\Migration; use Illuminate\\Database\\Schema\\Blueprint; use Illuminate\\Support\\Facades\\Schema; return new class extends Migration { public function up(): void { Schema::create('pendaftaran_ppl', function(Blueprint $table): void { $table->id(); $table->string('nim')->index(); $table->string('lokasi_ppl'); $table->string('tahun_ajaran')->index(); $table->date('tanggal_daftar')->index(); $table->enum('status_pendaftaran',['pending','disetujui','ditolak'])->default('pending')->index(); $table->text('catatan')->nullable(); $table->timestamps(); $table->unique(['nim','tahun_ajaran']); }); } public function down(): void { Schema::dropIfExists('pendaftaran_ppl'); } };
''')
w('ppl-service/app/Services/EligibilityClient.php', '''<?php
declare(strict_types=1);
namespace App\\Services;
use Illuminate\\Support\\Facades\\Http;
class EligibilityClient
{
    public function check(string $nim): array
    {
        $mahasiswa = $this->getJson(rtrim(config('services.mahasiswa.url'), '/')."/api/mahasiswa/{$nim}/status");
        $klinik = $this->getJson(rtrim(config('services.klinik.url'), '/')."/api/kesehatan/{$nim}");
        $bank = $this->getJson(rtrim(config('services.bank.url'), '/')."/api/pembayaran/{$nim}/status?jenis_pembayaran=biaya_ppl");
        return [
            'mahasiswa_active' => (bool) data_get($mahasiswa, 'data.is_active', false),
            'health_eligible' => (bool) data_get($klinik, 'data.is_eligible', false),
            'payment_paid' => (bool) data_get($bank, 'data.is_paid', false),
            'raw' => ['mahasiswa' => $mahasiswa, 'klinik' => $klinik, 'bank' => $bank],
        ];
    }
    private function getJson(string $url): array
    {
        try { return Http::timeout(3)->acceptJson()->get($url)->json() ?? ['status'=>'error','message'=>'Response kosong']; }
        catch (\\Throwable $e) { return ['status'=>'error','message'=>'Service dependency tidak tersedia']; }
    }
}
''')
w('ppl-service/config/services.php', '''<?php
return [
    'mahasiswa' => ['url' => env('MAHASISWA_SERVICE_URL', 'http://localhost:8002')],
    'klinik' => ['url' => env('KLINIK_SERVICE_URL', 'http://localhost:8003')],
    'bank' => ['url' => env('BANK_SERVICE_URL', 'http://localhost:8004')],
];
''')
w('ppl-service/app/Http/Controllers/Api/PplController.php', f'''<?php
declare(strict_types=1);
namespace App\\Http\\Controllers\\Api; use App\\Http\\Controllers\\Controller; use App\\Models\\PendaftaranPpl; use App\\Services\\EligibilityClient; use Illuminate\\Http\\JsonResponse; use Illuminate\\Http\\Request; use Illuminate\\Validation\\Rule;
class PplController extends Controller {{{H}
 public function daftar(Request $request, EligibilityClient $client): JsonResponse {{ $data=$request->validate(['nim'=>'required|string','lokasi_ppl'=>'required|string|max:255','tahun_ajaran'=>'required|string|max:32']); $eligibility=$client->check($data['nim']); $missing=[]; if(! $eligibility['mahasiswa_active']) $missing[]='Status mahasiswa tidak aktif atau tidak ditemukan'; if(! $eligibility['health_eligible']) $missing[]='Status kesehatan belum layak'; if(! $eligibility['payment_paid']) $missing[]='Tagihan PPL belum lunas'; if($missing) return $this->fail('Pendaftaran PPL ditolak karena syarat belum terpenuhi.',['requirements'=>$eligibility,'missing'=>$missing],422); $pendaftaran=PendaftaranPpl::create($data + ['tanggal_daftar'=>now()->toDateString(),'status_pendaftaran'=>'pending']); return $this->ok('Pendaftaran PPL berhasil dibuat',$pendaftaran,201); }}
 public function status(string $nim): JsonResponse {{ return $this->ok('Status pendaftaran PPL',PendaftaranPpl::where('nim',$nim)->latest()->get()); }}
 public function index(Request $request): JsonResponse {{ $query=PendaftaranPpl::query(); foreach(['nim','tahun_ajaran','status_pendaftaran'] as $field) {{ if($request->filled($field)) $query->where($field,$request->input($field)); }} return $this->ok('Daftar pendaftaran PPL',$query->latest()->paginate($request->integer('per_page',15))); }}
 public function approve(int $id): JsonResponse {{ $p=PendaftaranPpl::findOrFail($id); $p->update(['status_pendaftaran'=>'disetujui','catatan'=>null]); return $this->ok('Pendaftaran PPL disetujui',$p->fresh()); }}
 public function reject(Request $request,int $id): JsonResponse {{ $data=$request->validate(['catatan'=>'required|string']); $p=PendaftaranPpl::findOrFail($id); $p->update(['status_pendaftaran'=>'ditolak','catatan'=>$data['catatan']]); return $this->ok('Pendaftaran PPL ditolak',$p->fresh()); }}
}}
''')
w('ppl-service/routes/api.php', '''<?php
declare(strict_types=1);
use App\\Http\\Controllers\\Api\\PplController; use Illuminate\\Support\\Facades\\Route;
Route::post('/ppl/daftar',[PplController::class,'daftar']); Route::get('/ppl/status/{nim}',[PplController::class,'status']); Route::get('/ppl/pendaftaran',[PplController::class,'index']); Route::put('/ppl/pendaftaran/{id}/approve',[PplController::class,'approve'])->whereNumber('id'); Route::put('/ppl/pendaftaran/{id}/reject',[PplController::class,'reject'])->whereNumber('id');
''')
w('ppl-service/database/seeders/DatabaseSeeder.php', '''<?php
declare(strict_types=1);
namespace Database\\Seeders; use Illuminate\\Database\\Seeder;
class DatabaseSeeder extends Seeder { public function run(): void { } }
''')
print('bank ppl written')
