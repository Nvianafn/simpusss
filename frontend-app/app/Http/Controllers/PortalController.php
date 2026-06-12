<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SimpusApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('portal.ppl');
    }

    public function mahasiswa(Request $request, SimpusApiClient $client): View|RedirectResponse
    {
        $context = $this->studentContext($request);
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return view('portal.mahasiswa', $context + [
            'title' => 'Portal Mahasiswa',
            'mahasiswa' => $client->mahasiswaDetail($context['nim']),
            'mahasiswaStatus' => $client->mahasiswaStatus($context['nim']),
        ]);
    }

    public function klinik(Request $request, SimpusApiClient $client): View|RedirectResponse
    {
        $context = $this->studentContext($request);
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return view('portal.klinik', $context + [
            'title' => 'Portal Klinik',
            'kesehatan' => $client->kesehatanLatest($context['nim']),
            'kesehatanHistory' => $client->kesehatanHistory($context['nim']),
        ]);
    }

    public function bank(Request $request, SimpusApiClient $client): View|RedirectResponse
    {
        $context = $this->studentContext($request);
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        return view('portal.bank', $context + [
            'title' => 'Portal Bank',
            'pembayaran' => $client->pembayaranByNim($context['nim']),
            'pembayaranStatus' => $client->pembayaranStatus($context['nim']),
        ]);
    }

    public function ppl(Request $request, SimpusApiClient $client): View|RedirectResponse
    {
        $context = $this->studentContext($request);
        if ($context instanceof RedirectResponse) {
            return $context;
        }

        $bundle = $client->portalBundle($context['nim']);
        $bundle['user'] = $context['user'];
        $bundle['title'] = 'Portal PPL';

        return view('portal.ppl', $bundle);
    }

    /** @return array{user: array, nim: string}|RedirectResponse */
    private function studentContext(Request $request): array|RedirectResponse
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

        return compact('user', 'nim');
    }
}
