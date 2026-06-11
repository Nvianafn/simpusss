from pathlib import Path

def w(p,t): Path(p).parent.mkdir(parents=True,exist_ok=True); Path(p).write_text(t)
for svc in ['auth-service','mahasiswa-service','klinik-service','bank-service','ppl-service']:
    w(f'{svc}/phpunit.xml', '''<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php" colors="true">
  <testsuites>
    <testsuite name="Feature"><directory>tests/Feature</directory></testsuite>
    <testsuite name="Unit"><directory>tests/Unit</directory></testsuite>
  </testsuites>
  <php><env name="APP_ENV" value="testing"/><env name="DB_CONNECTION" value="sqlite"/><env name="DB_DATABASE" value=":memory:"/></php>
</phpunit>
''')
    Path(f'{svc}/tests/Feature').mkdir(parents=True, exist_ok=True)
    Path(f'{svc}/tests/Unit').mkdir(parents=True, exist_ok=True)

w('mahasiswa-service/tests/Feature/MahasiswaApiTest.php', '''<?php

declare(strict_types=1);

namespace Tests\\Feature;

use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;

class MahasiswaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_read_mahasiswa(): void
    {
        $payload = ['nim'=>'234110601088','nama'=>'Budi Santoso','email'=>'budi@simpus.test','prodi'=>'Informatika','fakultas'=>'Saintek','semester'=>6,'angkatan'=>2023,'status'=>'aktif'];
        $this->postJson('/api/mahasiswa', $payload)->assertCreated()->assertJsonPath('status', 'success');
        $this->getJson('/api/mahasiswa/234110601088/status')->assertOk()->assertJsonPath('data.is_active', true);
    }
}
''')
w('klinik-service/tests/Feature/KesehatanApiTest.php', '''<?php

declare(strict_types=1);

namespace Tests\\Feature;

use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;

class KesehatanApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_latest_health_status_returns_eligibility(): void
    {
        $this->postJson('/api/kesehatan', ['nim'=>'234110601088','tanggal_cek'=>'2026-06-11','tinggi_badan'=>170,'berat_badan'=>65,'tekanan_darah'=>'120/80','hasil_pemeriksaan'=>'Sehat','status_kesehatan'=>'layak'])->assertCreated();
        $this->getJson('/api/kesehatan/234110601088')->assertOk()->assertJsonPath('data.is_eligible', true);
    }
}
''')
w('bank-service/tests/Feature/PembayaranApiTest.php', '''<?php

declare(strict_types=1);

namespace Tests\\Feature;

use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;

class PembayaranApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_can_be_confirmed(): void
    {
        $id = $this->postJson('/api/pembayaran', ['nim'=>'234110601088','kode_tagihan'=>'PPL-1','jenis_pembayaran'=>'biaya_ppl','nominal'=>750000])->assertCreated()->json('data.id');
        $this->putJson("/api/pembayaran/{$id}/konfirmasi", ['metode_pembayaran'=>'transfer','tanggal_bayar'=>'2026-06-11'])->assertOk()->assertJsonPath('data.status_pembayaran', 'lunas');
        $this->getJson('/api/pembayaran/234110601088/status?jenis_pembayaran=biaya_ppl')->assertOk()->assertJsonPath('data.is_paid', true);
    }
}
''')
w('ppl-service/tests/Feature/PplApiTest.php', '''<?php

declare(strict_types=1);

namespace Tests\\Feature;

use App\\Services\\EligibilityClient;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;

class PplApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_is_rejected_when_requirement_missing(): void
    {
        $this->mock(EligibilityClient::class, fn ($mock) => $mock->shouldReceive('check')->andReturn(['mahasiswa_active'=>true,'health_eligible'=>false,'payment_paid'=>true,'raw'=>[]]));
        $this->postJson('/api/ppl/daftar', ['nim'=>'234110601088','lokasi_ppl'=>'RS Kampus','tahun_ajaran'=>'2026/2027'])->assertUnprocessable()->assertJsonPath('status', 'error');
    }

    public function test_registration_succeeds_when_all_requirements_pass(): void
    {
        $this->mock(EligibilityClient::class, fn ($mock) => $mock->shouldReceive('check')->andReturn(['mahasiswa_active'=>true,'health_eligible'=>true,'payment_paid'=>true,'raw'=>[]]));
        $this->postJson('/api/ppl/daftar', ['nim'=>'234110601088','lokasi_ppl'=>'RS Kampus','tahun_ajaran'=>'2026/2027'])->assertCreated()->assertJsonPath('data.status_pendaftaran', 'pending');
    }
}
''')
w('auth-service/tests/Feature/AuthApiTest.php', '''<?php

declare(strict_types=1);

namespace Tests\\Feature;

use App\\Models\\Role;
use App\\Models\\User;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_with_sanctum_token(): void
    {
        $role = Role::create(['name' => 'Super Admin', 'slug' => 'super_admin']);
        User::create(['name'=>'Admin','email'=>'admin@simpus.test','password'=>'password123','role_id'=>$role->id,'is_active'=>true]);
        $this->postJson('/api/auth/login', ['email'=>'admin@simpus.test','password'=>'password123'])->assertOk()->assertJsonPath('status', 'success')->assertJsonStructure(['data'=>['token','user']]);
    }
}
''')
print('tests added')
