<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SimpusApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function __invoke(Request $request, SimpusApiClient $client): View|RedirectResponse
    {
        $user = $request->session()->get('simpus_user', []);

        $userRole = str_replace(['-', '_', ' '], '', strtolower((string) (data_get($user, 'role.slug') ?: data_get($user, 'role') ?: '')));
        $sessionNim = (string) data_get($user, 'nim', '');

        $nim = $userRole === 'mahasiswa'
            ? $sessionNim
            : (string) ($request->query('nim') ?: $sessionNim ?: '234110601088');

        if ($nim === '') {
            return redirect()->route('dashboard')->withErrors([
                'authorization' => 'Akun mahasiswa ini belum punya NIM di sesi login.',
            ]);
        }
        $bundle = $client->portalBundle($nim);
        $bundle['user'] = $user;

        return view('portal', $bundle);
    }
}
