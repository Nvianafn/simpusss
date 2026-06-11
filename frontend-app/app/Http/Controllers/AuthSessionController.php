<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SimpusApiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, SimpusApiClient $client): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $response = $client->login($credentials['email'], $credentials['password']);

        if (data_get($response, 'status') !== 'success' || ! data_get($response, 'data.token')) {
            return back()
                ->withErrors(['email' => data_get($response, 'message', 'Login gagal.')])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->session()->put('simpus_token', data_get($response, 'data.token'));
        $request->session()->put('simpus_user', data_get($response, 'data.user'));

        return redirect()->route($this->redirectRouteForUser((array) data_get($response, 'data.user', [])))
            ->with('success', 'Login berhasil.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget(['simpus_token', 'simpus_user']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logout berhasil.');
    }

    private function redirectRouteForUser(array $user): string
    {
        return match ($this->normalizedRole($user)) {
            'mahasiswa', 'student' => 'portal',
            'adminppl' => 'admin.ppl',
            'adminbank' => 'admin.pembayaran',
            'superadmin', 'admin', 'operator' => 'dashboard',
            default => 'dashboard',
        };
    }

    private function normalizedRole(array $user): string
    {
        $role = (string) (data_get($user, 'role.slug') ?: data_get($user, 'role') ?: '');

        return str_replace(['-', '_', ' '], '', strtolower(trim($role)));
    }
}
