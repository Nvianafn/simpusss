<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranPpl;
use App\Services\EligibilityClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PplController extends Controller
{
    private function ok(string $message, mixed $data = null, int $code = 200): JsonResponse
    {
        return response()->json(['status' => 'success', 'message' => $message, 'data' => $data], $code);
    }

    private function fail(string $message, mixed $data = null, int $code = 422): JsonResponse
    {
        return response()->json(['status' => 'error', 'message' => $message, 'data' => $data], $code);
    }

    public function daftar(Request $request, EligibilityClient $client): JsonResponse
    {
        $data = $request->validate([
            'nim' => 'required|string',
            'lokasi_ppl' => 'required|string|max:255',
            'tahun_ajaran' => 'required|string|max:32',
        ]);

        $existing = PendaftaranPpl::where('nim', $data['nim'])
            ->where('tahun_ajaran', $data['tahun_ajaran'])
            ->first();

        if ($existing) {
            return $this->fail('Mahasiswa sudah terdaftar PPL pada tahun ajaran ini.', [
                'nim' => $data['nim'],
                'tahun_ajaran' => $data['tahun_ajaran'],
                'pendaftaran' => $existing,
            ], 409);
        }

        $eligibility = $client->check($data['nim']);
        $missing = [];

        if (! $eligibility['mahasiswa_active']) {
            $missing[] = 'Status mahasiswa tidak aktif atau tidak ditemukan';
        }

        if (! $eligibility['health_eligible']) {
            $missing[] = 'Status kesehatan belum layak';
        }

        if (! $eligibility['payment_paid']) {
            $missing[] = 'Tagihan PPL belum lunas';
        }

        if ($missing) {
            return $this->fail('Pendaftaran PPL ditolak karena syarat belum terpenuhi.', [
                'requirements' => $eligibility,
                'missing' => $missing,
            ], 422);
        }

        $pendaftaran = PendaftaranPpl::create($data + [
            'tanggal_daftar' => now()->toDateString(),
            'status_pendaftaran' => 'pending',
        ]);

        return $this->ok('Pendaftaran PPL berhasil dibuat', $pendaftaran, 201);
    }

    public function status(string $nim): JsonResponse
    {
        return $this->ok('Status pendaftaran PPL', PendaftaranPpl::where('nim', $nim)->latest()->get());
    }

    public function index(Request $request): JsonResponse
    {
        $query = PendaftaranPpl::query();

        foreach (['nim', 'tahun_ajaran', 'status_pendaftaran'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        return $this->ok('Daftar pendaftaran PPL', $query->latest()->paginate($request->integer('per_page', 15)));
    }

    public function approve(int $id): JsonResponse
    {
        $pendaftaran = PendaftaranPpl::findOrFail($id);
        $pendaftaran->update(['status_pendaftaran' => 'disetujui', 'catatan' => null]);

        return $this->ok('Pendaftaran PPL disetujui', $pendaftaran->fresh());
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['catatan' => 'required|string']);
        $pendaftaran = PendaftaranPpl::findOrFail($id);
        $pendaftaran->update(['status_pendaftaran' => 'ditolak', 'catatan' => $data['catatan']]);

        return $this->ok('Pendaftaran PPL ditolak', $pendaftaran->fresh());
    }
}
