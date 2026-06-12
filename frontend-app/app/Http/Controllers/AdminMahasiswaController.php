<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SimpusApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class AdminMahasiswaController extends Controller
{
    public function index(Request $request, SimpusApiClient $client): View
    {
        $query = ['per_page' => 20];
        foreach (['q', 'prodi', 'fakultas', 'angkatan', 'semester', 'status'] as $key) {
            $value = trim((string) $request->query($key, ''));
            if ($value !== '') {
                $query[$key] = $value;
            }
        }

        return view('admin.mahasiswa.index', [
            'mahasiswa' => $client->mahasiswa($query),
            'filters' => $query,
        ]);
    }

    public function store(Request $request, SimpusApiClient $client): RedirectResponse
    {
        $data = $this->validatedMahasiswa($request);
        $response = $client->createMahasiswa($data);

        if (data_get($response, '_meta.ok') !== true) {
            return back()->withErrors(['mahasiswa' => data_get($response, 'message', 'Gagal membuat mahasiswa.')])->withInput();
        }

        return back()->with('success', data_get($response, 'message', 'Mahasiswa dibuat.'));
    }

    public function update(Request $request, SimpusApiClient $client, string $nim): RedirectResponse
    {
        $data = $this->validatedMahasiswa($request, partial: true);
        $response = $client->updateMahasiswa($nim, $data);

        if (data_get($response, '_meta.ok') !== true) {
            return back()->withErrors(['mahasiswa' => data_get($response, 'message', 'Gagal memperbarui mahasiswa.')])->withInput();
        }

        return back()->with('success', data_get($response, 'message', 'Mahasiswa diperbarui.'));
    }

    public function destroy(SimpusApiClient $client, string $nim): RedirectResponse
    {
        $response = $client->deleteMahasiswa($nim);

        if (data_get($response, '_meta.ok') !== true) {
            return back()->withErrors(['mahasiswa' => data_get($response, 'message', 'Gagal menghapus mahasiswa.')]);
        }

        return back()->with('success', data_get($response, 'message', 'Mahasiswa dihapus.'));
    }

    private function validatedMahasiswa(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'nim' => [$required, 'string', 'max:32'],
            'nama' => [$required, 'string', 'max:255'],
            'email' => [$required, 'email', 'max:255'],
            'prodi' => [$required, 'string', 'max:255'],
            'fakultas' => [$required, 'string', 'max:255'],
            'semester' => [$required, 'integer', 'min:1', 'max:14'],
            'angkatan' => [$required, 'integer', 'min:2000', 'max:2100'],
            'status' => [$required, Rule::in(['aktif', 'non_aktif', 'cuti', 'lulus'])],
        ]);
    }
}
