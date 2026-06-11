<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
