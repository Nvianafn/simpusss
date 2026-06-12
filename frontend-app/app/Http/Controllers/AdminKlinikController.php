<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SimpusApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class AdminKlinikController extends Controller
{
    public function index(Request $request, SimpusApiClient $client): View
    {
        $nim = trim((string) $request->query('nim', ''));
        $latest = $nim !== '' ? $client->kesehatanLatest($nim) : null;
        $history = $nim !== '' ? $client->kesehatanHistory($nim) : null;

        return view('admin.klinik.index', [
            'nim' => $nim,
            'latest' => $latest,
            'history' => $history,
        ]);
    }

    public function store(Request $request, SimpusApiClient $client): RedirectResponse
    {
        $data = $this->validatedKesehatan($request);
        $response = $client->createKesehatan($data);

        if (data_get($response, '_meta.ok') !== true) {
            return back()->withErrors(['klinik' => data_get($response, 'message', 'Gagal membuat hasil pemeriksaan.')])->withInput();
        }

        return redirect()->route('admin.klinik', ['nim' => $data['nim']])
            ->with('success', data_get($response, 'message', 'Hasil pemeriksaan dibuat.'));
    }

    public function update(Request $request, SimpusApiClient $client, int $id): RedirectResponse
    {
        $data = $this->validatedKesehatan($request, partial: true);
        $response = $client->updateKesehatan($id, $data);

        if (data_get($response, '_meta.ok') !== true) {
            return back()->withErrors(['klinik' => data_get($response, 'message', 'Gagal memperbarui hasil pemeriksaan.')])->withInput();
        }

        $nim = (string) $request->input('nim', $request->query('nim', ''));

        return redirect()->route('admin.klinik', $nim !== '' ? ['nim' => $nim] : [])
            ->with('success', data_get($response, 'message', 'Hasil pemeriksaan diperbarui.'));
    }

    private function validatedKesehatan(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'nim' => [$required, 'string', 'max:32'],
            'tanggal_cek' => [$required, 'date'],
            'tinggi_badan' => [$required, 'integer', 'min:1'],
            'berat_badan' => [$required, 'integer', 'min:1'],
            'tekanan_darah' => [$required, 'string', 'max:32'],
            'hasil_pemeriksaan' => [$required, 'string'],
            'status_kesehatan' => [$required, Rule::in(['layak', 'tidak_layak'])],
        ]);
    }
}
