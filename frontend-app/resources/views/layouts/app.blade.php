<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'SIMPUS Frontend' }}</title>
    <style>
        :root { font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color-scheme: light; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f6fb; color: #14213d; }
        a { color: inherit; }
        .shell { max-width: 1180px; margin: 0 auto; padding: 28px 18px 56px; }
        .topbar { display: flex; justify-content: space-between; gap: 14px; align-items: center; margin-bottom: 18px; }
        .brand { display: flex; gap: 10px; align-items: center; font-weight: 950; letter-spacing: -.03em; }
        .brand-mark { width: 36px; height: 36px; border-radius: 13px; display: grid; place-items: center; background: #1d4ed8; color: white; box-shadow: 0 12px 24px rgba(29, 78, 216, .25); }
        .nav { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: flex-end; }
        .nav a, .nav button, .btn { border: 0; border-radius: 999px; padding: 10px 14px; font-weight: 900; background: #e2e8f0; color: #0f172a; cursor: pointer; text-decoration: none; display: inline-flex; justify-content: center; align-items: center; gap: 8px; }
        .nav a.active, .btn.primary, button.primary { background: #facc15; color: #111827; }
        .btn.danger, button.danger { background: #fee2e2; color: #991b1b; }
        .hero { background: linear-gradient(135deg, #0f172a, #1d4ed8); color: white; border-radius: 28px; padding: 28px; box-shadow: 0 22px 55px rgba(15, 23, 42, .22); }
        .hero.purple { background: linear-gradient(135deg, #111827, #7c3aed); }
        .hero.green { background: linear-gradient(135deg, #064e3b, #059669); }
        .hero.orange { background: linear-gradient(135deg, #7c2d12, #ea580c); }
        .hero h1 { margin: 0 0 8px; font-size: clamp(30px, 5vw, 44px); letter-spacing: -0.04em; }
        .hero p { margin: 0; color: rgba(255,255,255,.82); line-height: 1.7; max-width: 850px; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 20px; align-items: center; }
        .grid { display: grid; gap: 18px; }
        .stats { grid-template-columns: repeat(4, minmax(0, 1fr)); margin: 22px 0; }
        .sections { grid-template-columns: 1.1fr .9fr; align-items: start; }
        .card { background: white; border: 1px solid #e5e7eb; border-radius: 22px; padding: 20px; box-shadow: 0 10px 28px rgba(15, 23, 42, .06); margin-top: 18px; }
        .card h2, .card h3 { margin-top: 0; letter-spacing: -.03em; }
        .alert-ok { background:#dcfce7; color:#166534; font-weight:900; }
        .alert-error { background:#fee2e2; color:#991b1b; font-weight:900; }
        .muted { color: #64748b; font-size: 14px; line-height: 1.5; }
        .empty { padding: 16px; border-radius: 16px; background: #f8fafc; color: #64748b; }
        .badge { display: inline-flex; border-radius: 999px; padding: 5px 10px; font-size: 12px; font-weight: 900; background: #e0f2fe; color: #075985; }
        .badge.green { background: #dcfce7; color: #166534; }
        .badge.yellow { background: #fef9c3; color: #854d0e; }
        .badge.red { background: #fee2e2; color: #991b1b; }
        .badge.gray { background: #e5e7eb; color: #374151; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: .07em; border-bottom: 1px solid #e5e7eb; padding: 10px 8px; }
        td { border-bottom: 1px solid #eef2f7; padding: 12px 8px; vertical-align: top; }
        input, select, textarea { width: 100%; border: 1px solid #cbd5e1; border-radius: 14px; padding: 10px 12px; font: inherit; background: white; color: #0f172a; }
        textarea { min-height: 92px; resize: vertical; }
        .hero input, .hero select { border-color: rgba(255,255,255,.28); background: rgba(255,255,255,.14); color: white; }
        .hero input::placeholder { color: rgba(255,255,255,.72); }
        .hero select option { color: #111827; }
        button { border: 0; border-radius: 999px; padding: 11px 15px; font-weight: 900; background: #facc15; color: #111827; cursor: pointer; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .form-grid .full { grid-column: 1 / -1; }
        .actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .inline-form { display: inline-flex; gap: 8px; align-items: center; }
        .footer { margin-top: 18px; color: #64748b; font-size: 13px; }
        @media (max-width: 900px) { .topbar { align-items: flex-start; flex-direction: column; } .stats, .sections, .form-grid { grid-template-columns: 1fr; } .form-grid .full { grid-column: auto; } table, thead, tbody, tr, td, th { display: block; } thead { display: none; } tr { border-bottom: 1px solid #e5e7eb; padding: 12px 0; } td { border: 0; padding: 7px 0; } }
    </style>
    @stack('styles')
</head>
<body>
    <main class="shell">
        <header class="topbar">
            <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">S</span><span>SIMPUS</span></a>
            @php
                $sessionUser = session('simpus_user', []);
                $currentRole = str_replace(['-', '_', ' '], '', strtolower((string) data_get($sessionUser, 'role.slug', data_get($sessionUser, 'role', ''))));
                $canAccess = static fn (array $roles): bool => in_array('superadmin', [$currentRole], true) || in_array($currentRole, array_map(static fn (string $role): string => str_replace(['-', '_', ' '], '', strtolower($role)), $roles), true);
            @endphp
            <nav class="nav">
                <a @class(['active' => request()->routeIs('dashboard')]) href="{{ route('dashboard') }}">Dashboard</a>
                @if (session('simpus_token'))
                    @if ($canAccess(['mahasiswa']))
                        <a @class(['active' => request()->routeIs('portal')]) href="{{ route('portal') }}">Portal</a>
                    @endif
                    @if ($canAccess(['admin_mahasiswa']))
                        <a @class(['active' => request()->routeIs('admin.mahasiswa')]) href="{{ route('admin.mahasiswa') }}">Mahasiswa</a>
                    @endif
                    @if ($canAccess(['admin_klinik']))
                        <a @class(['active' => request()->routeIs('admin.klinik')]) href="{{ route('admin.klinik') }}">Klinik</a>
                    @endif
                    @if ($canAccess(['admin_bank']))
                        <a @class(['active' => request()->routeIs('admin.pembayaran')]) href="{{ route('admin.pembayaran') }}">Pembayaran</a>
                    @endif
                    @if ($canAccess(['admin_ppl']))
                        <a @class(['active' => request()->routeIs('admin.ppl')]) href="{{ route('admin.ppl') }}">PPL</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">@csrf <button type="submit">Logout</button></form>
                @else
                    <a class="active" href="{{ route('login') }}">Login</a>
                @endif
            </nav>
        </header>

        @if (session('simpus_user'))
            <div class="muted" style="margin-bottom:12px">Login: <strong>{{ data_get(session('simpus_user'), 'name', '-') }}</strong> · {{ data_get(session('simpus_user'), 'role.slug', data_get(session('simpus_user'), 'role', '-')) }}</div>
        @endif

        @if (session('success'))
            <section class="card alert-ok">{{ session('success') }}</section>
        @endif
        @if ($errors->any())
            <section class="card alert-error">{{ $errors->first() }}</section>
        @endif

        @yield('content')
    </main>
</body>
</html>
