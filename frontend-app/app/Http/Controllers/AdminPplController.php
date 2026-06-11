<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SimpusApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPplController extends Controller
{
    public function index(Request $request, SimpusApiClient $client): View|RedirectResponse
    {
        $user = $request->session()->get('simpus_user', []);

        $status = (string) $request->query('status_pendaftaran', 'pending');
        $query = ['per_page' => 20];

        if ($status !== 'semua') {
            $query['status_pendaftaran'] = $status;
        }

        $pendaftaran = $client->ppl($query);

        return view('admin.ppl.index', [
            'user' => $user,
            'pendaftaran' => $pendaftaran,
            'status' => $status,
        ]);
    }

    public function approve(Request $request, SimpusApiClient $client, int $id): RedirectResponse
    {
        $response = $client->approvePpl($id);

        if (data_get($response, '_meta.ok') !== true) {
            return back()->withErrors(['ppl' => data_get($response, 'message', 'Gagal approve pendaftaran PPL.')]);
        }

        return back()->with('success', data_get($response, 'message', 'Pendaftaran PPL disetujui.'));
    }

    public function reject(Request $request, SimpusApiClient $client, int $id): RedirectResponse
    {
        $data = $request->validate([
            'catatan' => ['required', 'string', 'max:500'],
        ]);

        $response = $client->rejectPpl($id, $data['catatan']);

        if (data_get($response, '_meta.ok') !== true) {
            return back()
                ->withErrors(['ppl' => data_get($response, 'message', 'Gagal reject pendaftaran PPL.')])
                ->withInput();
        }

        return back()->with('success', data_get($response, 'message', 'Pendaftaran PPL ditolak.'));
    }
}
