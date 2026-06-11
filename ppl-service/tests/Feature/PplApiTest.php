<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\EligibilityClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
