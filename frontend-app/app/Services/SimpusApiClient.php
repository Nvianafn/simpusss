<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SimpusApiClient
{
    public function login(string $email, string $password): array
    {
        return $this->post('auth', '/api/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);
    }

    public function me(?string $token): array
    {
        return $this->get('auth', '/api/auth/me', [], $token);
    }

    public function mahasiswa(array $query = []): array
    {
        return $this->getInternal('mahasiswa', '/api/mahasiswa', $query);
    }

    public function mahasiswaDetail(string $nim): array
    {
        $encodedNim = rawurlencode($nim);

        return $this->getInternal('mahasiswa', "/api/mahasiswa/{$encodedNim}");
    }

    public function mahasiswaStatus(string $nim): array
    {
        $encodedNim = rawurlencode($nim);

        return $this->getInternal('mahasiswa', "/api/mahasiswa/{$encodedNim}/status");
    }

    public function createMahasiswa(array $payload): array
    {
        return $this->postInternal('mahasiswa', '/api/mahasiswa', $payload);
    }

    public function updateMahasiswa(string $nim, array $payload): array
    {
        $encodedNim = rawurlencode($nim);

        return $this->putInternal('mahasiswa', "/api/mahasiswa/{$encodedNim}", $payload);
    }

    public function deleteMahasiswa(string $nim): array
    {
        $encodedNim = rawurlencode($nim);

        return $this->deleteInternal('mahasiswa', "/api/mahasiswa/{$encodedNim}");
    }

    public function ppl(array $query = []): array
    {
        return $this->getInternal('ppl', '/api/ppl/pendaftaran', $query);
    }

    public function pplStatus(string $nim): array
    {
        $encodedNim = rawurlencode($nim);

        return $this->getInternal('ppl', "/api/ppl/status/{$encodedNim}");
    }

    public function daftarPpl(string $nim, string $lokasiPpl, string $tahunAjaran): array
    {
        return $this->postInternal('ppl', '/api/ppl/daftar', [
            'nim' => $nim,
            'lokasi_ppl' => $lokasiPpl,
            'tahun_ajaran' => $tahunAjaran,
        ]);
    }

    public function approvePpl(int $id): array
    {
        return $this->putInternal('ppl', "/api/ppl/pendaftaran/{$id}/approve");
    }

    public function rejectPpl(int $id, string $catatan): array
    {
        return $this->putInternal('ppl', "/api/ppl/pendaftaran/{$id}/reject", [
            'catatan' => $catatan,
        ]);
    }

    public function pembayaran(array $query = []): array
    {
        return $this->getInternal('bank', '/api/pembayaran', $query);
    }

    public function createPembayaran(array $payload): array
    {
        return $this->postInternal('bank', '/api/pembayaran', $payload);
    }

    public function pembayaranByNim(string $nim): array
    {
        $encodedNim = rawurlencode($nim);

        return $this->getInternal('bank', "/api/pembayaran/{$encodedNim}");
    }

    public function pembayaranStatus(string $nim): array
    {
        $encodedNim = rawurlencode($nim);

        return $this->getInternal('bank', "/api/pembayaran/{$encodedNim}/status");
    }

    public function confirmPembayaran(int $id, string $metodePembayaran, string $tanggalBayar): array
    {
        return $this->putInternal('bank', "/api/pembayaran/{$id}/konfirmasi", [
            'metode_pembayaran' => $metodePembayaran,
            'tanggal_bayar' => $tanggalBayar,
        ]);
    }

    public function kesehatanLatest(string $nim): array
    {
        $encodedNim = rawurlencode($nim);

        return $this->getInternal('klinik', "/api/kesehatan/{$encodedNim}");
    }

    public function kesehatanHistory(string $nim): array
    {
        $encodedNim = rawurlencode($nim);

        return $this->getInternal('klinik', "/api/kesehatan/{$encodedNim}/riwayat");
    }

    public function createKesehatan(array $payload): array
    {
        return $this->postInternal('klinik', '/api/kesehatan', $payload);
    }

    public function updateKesehatan(int $id, array $payload): array
    {
        return $this->putInternal('klinik', "/api/kesehatan/{$id}", $payload);
    }

    public function statusBundle(?string $nim = null): array
    {
        $mahasiswa = $this->mahasiswa(['per_page' => 10]);
        $ppl = $this->ppl(['per_page' => 10]);
        $pembayaran = $this->pembayaran(['per_page' => 10]);

        $selectedNim = $nim ?: data_get($mahasiswa, 'data.data.0.nim');
        $kesehatan = $selectedNim ? $this->kesehatanLatest((string) $selectedNim) : $this->emptyResponse('Tidak ada NIM untuk dicek');

        return compact('mahasiswa', 'ppl', 'pembayaran', 'kesehatan', 'selectedNim');
    }

    public function portalBundle(string $nim): array
    {
        $mahasiswa = $this->mahasiswaDetail($nim);
        $mahasiswaStatus = $this->mahasiswaStatus($nim);
        $kesehatan = $this->kesehatanLatest($nim);
        $kesehatanHistory = $this->kesehatanHistory($nim);
        $pembayaran = $this->pembayaranByNim($nim);
        $pembayaranStatus = $this->pembayaranStatus($nim);
        $pplStatus = $this->pplStatus($nim);

        $requirements = [
            'mahasiswa_active' => (bool) data_get($mahasiswaStatus, 'data.is_active', false),
            'health_eligible' => (bool) data_get($kesehatan, 'data.is_eligible', false),
            'payment_paid' => (bool) data_get($pembayaranStatus, 'data.is_paid', false),
        ];

        $canRegisterPpl = ! in_array(false, $requirements, true);

        return compact(
            'nim',
            'mahasiswa',
            'mahasiswaStatus',
            'kesehatan',
            'kesehatanHistory',
            'pembayaran',
            'pembayaranStatus',
            'pplStatus',
            'requirements',
            'canRegisterPpl'
        );
    }

    private function get(string $service, string $path, array $query = [], ?string $token = null): array
    {
        return $this->send('GET', $service, $path, $query, $token);
    }

    private function getInternal(string $service, string $path, array $query = []): array
    {
        return $this->sendInternal('GET', $service, $path, $query);
    }

    private function post(string $service, string $path, array $payload = [], ?string $token = null): array
    {
        return $this->send('POST', $service, $path, $payload, $token);
    }

    private function postInternal(string $service, string $path, array $payload = []): array
    {
        return $this->sendInternal('POST', $service, $path, $payload);
    }

    private function putInternal(string $service, string $path, array $payload = []): array
    {
        return $this->sendInternal('PUT', $service, $path, $payload);
    }

    private function deleteInternal(string $service, string $path, array $payload = []): array
    {
        return $this->sendInternal('DELETE', $service, $path, $payload);
    }

    private function sendInternal(string $method, string $service, string $path, array $payload = []): array
    {
        $internalToken = config('services.simpus.internal_token');

        if (! is_string($internalToken) || $internalToken === '') {
            return $this->emptyResponse('Internal API token frontend belum dikonfigurasi.', false, 500);
        }

        return $this->send($method, $service, $path, $payload, null, [
            'X-Internal-Token' => $internalToken,
        ]);
    }

    private function send(string $method, string $service, string $path, array $payload = [], ?string $token = null, array $headers = []): array
    {
        $baseUrl = rtrim((string) config("services.simpus.{$service}_url"), '/');

        if ($baseUrl === '') {
            return $this->emptyResponse("Base URL service {$service} belum dikonfigurasi", false);
        }

        try {
            $request = $this->request($token);
            if ($headers !== []) {
                $request = $request->withHeaders($headers);
            }

            $response = match ($method) {
                'POST' => $request->post($baseUrl.$path, $payload),
                'PUT' => $request->put($baseUrl.$path, $payload),
                'DELETE' => $request->delete($baseUrl.$path, $payload),
                default => $request->get($baseUrl.$path, $payload),
            };
        } catch (ConnectionException $exception) {
            return $this->emptyResponse("Service {$service} tidak bisa dihubungi: {$exception->getMessage()}", false);
        }

        $json = $response->json();

        if (! is_array($json)) {
            return $this->emptyResponse("Service {$service} mengirim response tidak valid", false, $response->status());
        }

        $json['_meta'] = [
            'service' => $service,
            'url' => $baseUrl.$path,
            'ok' => $response->successful(),
            'status_code' => $response->status(),
        ];

        return $json;
    }

    private function request(?string $token = null): PendingRequest
    {
        $request = Http::acceptJson()
            ->timeout(8)
            ->connectTimeout(3)
            ->retry(1, 200);

        return $token ? $request->withToken($token) : $request;
    }

    private function emptyResponse(string $message, bool $success = true, int $statusCode = 0): array
    {
        return [
            'status' => $success ? 'success' : 'error',
            'message' => $message,
            'data' => ['data' => [], 'total' => 0],
            '_meta' => [
                'ok' => $success,
                'status_code' => $statusCode,
            ],
        ];
    }
}
