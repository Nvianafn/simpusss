<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SimpusApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PplRegistrationController extends Controller
{
    public function store(Request $request, SimpusApiClient $client): RedirectResponse
    {
        $user = $request->session()->get('simpus_user', []);

        $data = $request->validate([
            'nim' => ['required', 'string'],
            'lokasi_ppl' => ['required', 'string', 'max:255'],
            'tahun_ajaran' => ['required', 'string', 'max:32'],
        ]);

        $userRole = str_replace(['-', '_', ' '], '', strtolower((string) (data_get($user, 'role.slug') ?: data_get($user, 'role') ?: '')));
        $sessionNim = (string) data_get($user, 'nim', '');

        if ($userRole === 'mahasiswa') {
            if ($sessionNim === '') {
                return back()->withErrors(['ppl' => 'Akun mahasiswa ini belum punya NIM di sesi login.']);
            }

            $data['nim'] = $sessionNim;
        }

        $response = $client->daftarPpl($data['nim'], $data['lokasi_ppl'], $data['tahun_ajaran']);
        $statusCode = (int) data_get($response, '_meta.status_code', 0);

        if (data_get($response, 'status') !== 'success') {
            $missing = data_get($response, 'data.missing', []);
            $message = data_get($response, 'message', 'Pendaftaran PPL gagal.');

            if (is_array($missing) && count($missing) > 0) {
                $message .= ' '.implode('; ', $missing).'.';
            }

            return back()
                ->withErrors(['ppl' => $message])
                ->withInput();
        }

        return redirect()
            ->route('portal', ['nim' => $data['nim']])
            ->with('success', "Pendaftaran PPL berhasil dibuat ({$statusCode}). Status awal: pending.");
    }
}
