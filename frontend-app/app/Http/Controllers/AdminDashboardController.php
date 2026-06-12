<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SimpusApiClient;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function global(SimpusApiClient $client): View
    {
        return view('admin.dashboard', $client->statusBundle());
    }

    public function mahasiswa(SimpusApiClient $client): View
    {
        return view('admin.dashboards.mahasiswa', [
            'mahasiswa' => $client->mahasiswa(['per_page' => 10]),
        ]);
    }

    public function klinik(SimpusApiClient $client): View
    {
        return view('admin.dashboards.klinik', [
            'mahasiswa' => $client->mahasiswa(['per_page' => 10]),
        ]);
    }

    public function bank(SimpusApiClient $client): View
    {
        return view('admin.dashboards.bank', [
            'pembayaran' => $client->pembayaran(['per_page' => 10]),
        ]);
    }

    public function ppl(SimpusApiClient $client): View
    {
        return view('admin.dashboards.ppl', [
            'ppl' => $client->ppl(['per_page' => 10]),
        ]);
    }
}
