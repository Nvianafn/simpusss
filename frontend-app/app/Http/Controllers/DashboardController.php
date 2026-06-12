<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SimpusApiClient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, SimpusApiClient $client): View|RedirectResponse
    {
        $user = $request->session()->get('simpus_user', []);
        $role = str_replace(['-', '_', ' '], '', strtolower((string) (data_get($user, 'role.slug') ?: data_get($user, 'role') ?: '')));

        if ($role === 'superadmin') {
            return redirect()->route('admin.dashboard');
        }

        if ($role === 'adminmahasiswa') {
            return redirect()->route('admin.mahasiswa.dashboard');
        }

        if ($role === 'adminklinik') {
            return redirect()->route('admin.klinik.dashboard');
        }

        if ($role === 'adminbank') {
            return redirect()->route('admin.bank.dashboard');
        }

        if ($role === 'adminppl') {
            return redirect()->route('admin.ppl.dashboard');
        }

        if ($role === 'mahasiswa') {
            $nim = (string) data_get($user, 'nim', '');
            $bundle = $nim !== '' ? $client->portalBundle($nim) : $client->statusBundle($request->query('nim'));
            $bundle['user'] = $user;

            return view('dashboard.mahasiswa', $bundle);
        }

        $bundle = $client->statusBundle($request->query('nim'));

        return view('dashboard.public', $bundle);
    }
}
