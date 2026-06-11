<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EligibilityClient
{
    public function check(string $nim): array
    {
        $encodedNim = rawurlencode($nim);

        $mahasiswa = $this->getJson(
            'mahasiswa',
            rtrim(config('services.mahasiswa.url'), '/') . "/api/mahasiswa/{$encodedNim}/status"
        );

        $klinik = $this->getJson(
            'klinik',
            rtrim(config('services.klinik.url'), '/') . "/api/kesehatan/{$encodedNim}"
        );

        $bank = $this->getJson(
            'bank',
            rtrim(config('services.bank.url'), '/') . "/api/pembayaran/{$encodedNim}/status?jenis_pembayaran=biaya_ppl"
        );

        return [
            'mahasiswa_active' => (bool) data_get($mahasiswa, 'data.is_active', false),
            'health_eligible' => (bool) data_get($klinik, 'data.is_eligible', false),
            'payment_paid' => (bool) data_get($bank, 'data.is_paid', false),
            'raw' => ['mahasiswa' => $mahasiswa, 'klinik' => $klinik, 'bank' => $bank],
        ];
    }

    private function getJson(string $service, string $url): array
    {
        $internalToken = config('internal.api_token');

        if (! is_string($internalToken) || $internalToken === '') {
            Log::warning('SIMPUS internal API token is not configured for eligibility dependency request.', [
                'service' => $service,
                'url' => $url,
            ]);

            return ['status' => 'error', 'message' => 'Internal API token belum dikonfigurasi.'];
        }

        try {
            $response = Http::timeout(3)
                ->acceptJson()
                ->withHeaders(['X-Internal-Token' => $internalToken])
                ->get($url);

            if ($response->failed()) {
                Log::warning('SIMPUS eligibility dependency returned non-success response.', [
                    'service' => $service,
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $response->json() ?? ['status' => 'error', 'message' => 'Response kosong'];
        } catch (\Throwable $e) {
            Log::warning('SIMPUS eligibility dependency request failed.', [
                'service' => $service,
                'url' => $url,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return ['status' => 'error', 'message' => 'Service dependency tidak tersedia'];
        }
    }
}
