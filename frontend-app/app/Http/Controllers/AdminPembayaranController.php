<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SimpusApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPembayaranController extends Controller
{
    public function index(Request $request, SimpusApiClient $client): View|RedirectResponse
    {
        $user = $request->session()->get('simpus_user', []);

        $status = (string) $request->query('status_pembayaran', 'belum_bayar');
        $nim = trim((string) $request->query('nim', ''));

        $query = ['per_page' => 20];
        if ($status !== 'semua') {
            $query['status_pembayaran'] = $status;
        }
        if ($nim !== '') {
            $query['nim'] = $nim;
        }

        $pembayaran = $client->pembayaran($query);

        return view('admin.pembayaran.index', [
            'user' => $user,
            'pembayaran' => $pembayaran,
            'status' => $status,
            'nim' => $nim,
        ]);
    }

    public function store(Request $request, SimpusApiClient $client): RedirectResponse
    {
        $data = $request->validate([
            'nim' => ['required', 'string', 'max:32'],
            'kode_tagihan' => ['required', 'string', 'max:255'],
            'jenis_pembayaran' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'jatuh_tempo' => ['nullable', 'date'],
        ]);

        $response = $client->createPembayaran(array_filter($data, fn ($value) => $value !== null && $value !== ''));

        if (data_get($response, '_meta.ok') !== true) {
            return back()
                ->withErrors(['pembayaran' => data_get($response, 'message', 'Gagal membuat tagihan.')])
                ->withInput();
        }

        return redirect()
            ->route('admin.pembayaran', ['nim' => $data['nim'], 'status_pembayaran' => 'belum_bayar'])
            ->with('success', data_get($response, 'message', 'Tagihan dibuat.'));
    }

    public function confirm(Request $request, SimpusApiClient $client, int $id): RedirectResponse
    {
        $data = $request->validate([
            'metode_pembayaran' => ['required', 'string', 'max:255'],
            'tanggal_bayar' => ['required', 'date'],
        ]);

        $response = $client->confirmPembayaran($id, $data['metode_pembayaran'], $data['tanggal_bayar']);

        if (data_get($response, '_meta.ok') !== true) {
            return back()
                ->withErrors(['pembayaran' => data_get($response, 'message', 'Gagal konfirmasi pembayaran.')])
                ->withInput();
        }

        return back()->with('success', data_get($response, 'message', 'Pembayaran dikonfirmasi.'));
    }
}
