<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
