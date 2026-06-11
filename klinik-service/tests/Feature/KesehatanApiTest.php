<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KesehatanApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_latest_health_status_returns_eligibility(): void
    {
        $this->postJson('/api/kesehatan', ['nim'=>'234110601088','tanggal_cek'=>'2026-06-11','tinggi_badan'=>170,'berat_badan'=>65,'tekanan_darah'=>'120/80','hasil_pemeriksaan'=>'Sehat','status_kesehatan'=>'layak'])->assertCreated();
        $this->getJson('/api/kesehatan/234110601088')->assertOk()->assertJsonPath('data.is_eligible', true);
    }
}
