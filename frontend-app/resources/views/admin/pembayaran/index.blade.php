@php
    $rows = data_get($pembayaran, 'data.data', []);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Pembayaran SIMPUS</title>
    <style>
        :root { font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color-scheme: light; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f6fb; color: #14213d; }
        .shell { max-width: 1180px; margin: 0 auto; padding: 28px 18px 54px; }
        .top { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 18px; }
        .hero { background: linear-gradient(135deg, #0f172a, #059669); color: white; border-radius: 28px; padding: 28px; box-shadow: 0 22px 55px rgba(15, 23, 42, .22); }
        .hero h1 { margin: 0 0 8px; font-size: clamp(30px, 5vw, 44px); letter-spacing: -0.04em; }
        .hero p { margin: 0; color: #d1fae5; line-height: 1.7; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 20px; align-items: center; }
        select, .hero input { border: 1px solid rgba(255,255,255,.28); background: rgba(255,255,255,.14); color: white; border-radius: 999px; padding: 12px 16px; outline: none; font-weight: 800; }
        select option { color: #111827; }
        .hero input::placeholder { color: #bbf7d0; }
        button, .pill { border: 0; border-radius: 999px; padding: 12px 16px; font-weight: 900; background: #facc15; color: #111827; cursor: pointer; text-decoration: none; display: inline-flex; justify-content: center; }
        .pill.secondary, .top button { background: #e2e8f0; color: #0f172a; }
        .card { background: white; border: 1px solid #e5e7eb; border-radius: 22px; padding: 20px; box-shadow: 0 10px 28px rgba(15, 23, 42, .06); margin-top: 18px; }
        .alert-ok { background:#dcfce7; color:#166534; font-weight:900; }
        .alert-error { background:#fee2e2; color:#991b1b; font-weight:900; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: .07em; border-bottom: 1px solid #e5e7eb; padding: 10px 8px; }
        td { border-bottom: 1px solid #eef2f7; padding: 12px 8px; vertical-align: top; }
        .badge { display: inline-flex; border-radius: 999px; padding: 5px 10px; font-size: 12px; font-weight: 900; background: #fee2e2; color: #991b1b; }
        .badge.green { background: #dcfce7; color: #166534; }
        .actions { display: grid; gap: 8px; min-width: 250px; }
        .actions input { width: 100%; border: 1px solid #cbd5e1; border-radius: 14px; padding: 10px 12px; }
        .muted { color: #64748b; font-size: 14px; line-height: 1.5; }
        @media (max-width: 900px) { .top { align-items: flex-start; flex-direction: column; } table, thead, tbody, tr, td, th { display: block; } thead { display: none; } tr { border-bottom: 1px solid #e5e7eb; padding: 12px 0; } td { border: 0; padding: 7px 0; } }
    </style>
</head>
<body>
    <main class="shell">
        <div class="top">
            <div><strong>Admin:</strong> {{ data_get($user, 'name', '-') }} <span class="muted">({{ data_get($user, 'role.slug', '-') }})</span></div>
            <form method="POST" action="{{ route('logout') }}">@csrf <button type="submit">Logout</button></form>
        </div>

        @if (session('success'))
            <section class="card alert-ok">{{ session('success') }}</section>
        @endif

        @if ($errors->any())
            <section class="card alert-error">{{ $errors->first() }}</section>
        @endif

        <section class="hero">
            <h1>Admin Pembayaran</h1>
            <p>Halaman ini buat konfirmasi tagihan Bank Service. Setelah tagihan PPL dilunasi, Portal Mahasiswa dan PPL Service bisa membaca syarat pembayaran sebagai terpenuhi.</p>
            <form class="toolbar" method="GET" action="{{ route('admin.pembayaran') }}">
                <select name="status_pembayaran">
                    @foreach (['belum_bayar' => 'Belum Bayar', 'lunas' => 'Lunas', 'semua' => 'Semua'] as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input name="nim" value="{{ $nim }}" placeholder="Filter NIM opsional">
                <button type="submit">Filter</button>
                <a class="pill secondary" href="{{ route('admin.ppl') }}">Admin PPL</a>
                <a class="pill secondary" href="{{ route('portal') }}">Portal</a>
            </form>
        </section>

        <section class="card">
            <h2>Daftar Tagihan</h2>
            @if (is_array($rows) && count($rows) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NIM</th>
                            <th>Kode</th>
                            <th>Jenis</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Metode/Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($rows as $row)
                        @php $isPaid = ($row['status_pembayaran'] ?? '') === 'lunas'; @endphp
                        <tr>
                            <td>#{{ $row['id'] ?? '-' }}</td>
                            <td>{{ $row['nim'] ?? '-' }}</td>
                            <td>{{ $row['kode_tagihan'] ?? '-' }}</td>
                            <td>{{ $row['jenis_pembayaran'] ?? '-' }}</td>
                            <td>Rp {{ number_format((float) ($row['nominal'] ?? 0), 0, ',', '.') }}</td>
                            <td><span class="badge {{ $isPaid ? 'green' : '' }}">{{ $row['status_pembayaran'] ?? '-' }}</span></td>
                            <td>{{ $row['metode_pembayaran'] ?? '-' }}<br><span class="muted">{{ $row['tanggal_bayar'] ?? '-' }}</span></td>
                            <td>
                                @if (($row['id'] ?? null) && ! $isPaid)
                                    <form class="actions" method="POST" action="{{ route('admin.pembayaran.konfirmasi', $row['id']) }}">
                                        @csrf
                                        <input name="metode_pembayaran" value="{{ old('metode_pembayaran', 'Transfer Bank') }}" placeholder="Metode pembayaran" required>
                                        <input name="tanggal_bayar" type="date" value="{{ old('tanggal_bayar', now()->toDateString()) }}" required>
                                        <button type="submit">Konfirmasi Lunas</button>
                                    </form>
                                @else
                                    <span class="muted">Tidak ada aksi</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <p class="muted">Belum ada tagihan untuk filter ini.</p>
            @endif
        </section>
    </main>
</body>
</html>
