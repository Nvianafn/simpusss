<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSimpusRole
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response|RedirectResponse
    {
        $token = $request->session()->get('simpus_token');
        $user = $request->session()->get('simpus_user');

        if (! $token || ! is_array($user)) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan login dulu.']);
        }

        if ($roles !== [] && ! $this->matchesAnyRole($user, $roles)) {
            return redirect()->route('dashboard')->withErrors([
                'authorization' => 'Akses ditolak. Role akun ini tidak punya izin untuk halaman tersebut.',
            ]);
        }

        return $next($request);
    }

    private function matchesAnyRole(array $user, array $allowedRoles): bool
    {
        $role = $this->normalizeRole((string) (data_get($user, 'role.slug') ?: data_get($user, 'role') ?: ''));
        $aliases = $this->roleAliases($role);

        foreach ($allowedRoles as $allowedRole) {
            $allowed = $this->normalizeRole($allowedRole);

            if ($role === $allowed || in_array($allowed, $aliases, true)) {
                return true;
            }
        }

        return false;
    }

    private function roleAliases(string $role): array
    {
        return match ($role) {
            'admin', 'operator', 'superadmin' => ['admin'],
            'mahasiswa', 'student' => ['mahasiswa'],
            default => [],
        };
    }

    private function normalizeRole(string $role): string
    {
        return str_replace(['-', '_', ' '], '', strtolower(trim($role)));
    }
}
