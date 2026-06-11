@php
    $profile = data_get($mahasiswa, 'data', []);
    $health = data_get($kesehatan, 'data.pemeriksaan', data_get($kesehatan, 'data', []));
    $bills = data_get($pembayaran, 'data.data', data_get($pembayaran, 'data', []));
    $pplRows = data_get($pplStatus, 'data', []);
    $items = [
        ['label' => 'Status mahasiswa aktif', 'ok' => $requirements['mahasiswa_active'], 'detail' => data_get($mahasiswaStatus, 'message', '-')],
        ['label' => 'Kesehatan layak', 'ok' => $requirements['health_eligible'], 'detail' => data_get($kesehatan, 'message', '-')],
        ['label' => 'Tagihan PPL lunas', 'ok' => $requirements['payment_paid'], 'detail' => data_get($pembayaranStatus, 'message', '-')],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Mahasiswa SIMPUS</title>
    <style>
        :root { font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color-scheme: light; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f6fb; color: #14213d; }
        .shell { max-width: 1120px; margin: 0 auto; padding: 28px 18px 54px; }
        .top { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 18px; }
        .top form { margin: 0; }
        .hero { background: linear-gradient(135deg, #0f172a, #1d4ed8); color: white; border-radius: 28px; padding: 28px; box-shadow: 0 22px 55px rgba(15, 23, 42, .22); }
        .hero h1 { margin: 0 0 8px; font-size: clamp(30px, 5vw, 46px); letter-spacing: -0.04em; }
        .hero p { margin: 0; color: #dbeafe; line-height: 1.7; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 22px; align-items: center; }
        input { min-width: 260px; border: 1px solid rgba(255,255,255,.28); background: rgba(255,255,255,.12); color: white; border-radius: 999px; padding: 12px 16px; outline: none; }
        input::placeholder { color: #bfdbfe; }
        button, .pill { border: 0; border-radius: 999px; padding: 12px 16px; font-weight: 800; background: #facc15; color: #111827; cursor: pointer; text-decoration: none; display: inline-flex; }
        .pill.secondary, .top button { background: #e2e8f0; color: #0f172a; }
        .grid { display: grid; gap: 18px; }
        .summary { grid-template-columns: repeat(3, 1fr); margin: 22px 0; }
        .sections { grid-template-columns: 1fr 1fr; align-items: start; }
        .card { background: white; border: 1px solid #e5e7eb; border-radius: 22px; padding: 20px; box-shadow: 0 10px 28px rgba(15, 23, 42, .06); }
        h2 { margin: 0 0 14px; letter-spacing: -0.03em; }
        .check { display: flex; gap: 12px; align-items: flex-start; }
        .icon { width: 36px; height: 36px; border-radius: 50%; display: grid; place-items: center; font-weight: 900; background: #fee2e2; color: #991b1b; flex: 0 0 auto; }
        .icon.ok { background: #dcfce7; color: #166534; }
        .label { font-weight: 900; }
        .muted { color: #64748b; font-size: 14px; line-height: 1.5; }
        .badge { display: inline-flex; border-radius: 999px; padding: 5px 10px; font-size: 12px; font-weight: 900; background: #e0f2fe; color: #075985; }
        .badge.green { background: #dcfce7; color: #166534; }
        .badge.yellow { background: #fef9c3; color: #854d0e; }
        .badge.red { background: #fee2e2; color: #991b1b; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: .07em; border-bottom: 1px solid #e5e7eb; padding: 10px 8px; }
        td { border-bottom: 1px solid #eef2f7; padding: 12px 8px; vertical-align: top; }
        .cta { margin-top: 18px; padding: 16px; border-radius: 18px; background: {{ $canRegisterPpl ? '#dcfce7' : '#fee2e2' }}; color: {{ $canRegisterPpl ? '#166534' : '#991b1b' }}; font-weight: 900; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; align-items: end; margin-top: 14px; }
        .form-grid label { display: grid; gap: 7px; font-weight: 900; color: #334155; }
        .form-grid input { min-width: 0; width: 100%; color: #0f172a; background: #fff; border: 1px solid #cbd5e1; border-radius: 14px; }
        .form-grid input::placeholder { color: #94a3b8; }
        @media (max-width: 900px) { .summary, .sections, .form-grid { grid-template-columns: 1fr; } .hero { padding: 22px; } .top { align-items: flex-start; flex-direction: column; } }
    </style>
</head>
<body>
    <main class="shell">
        <div class="top">
            <div><strong>Login sebagai:</strong> {{ data_get($user, 'name', '-') }} <span class="muted">({{ data_get($user, 'role.slug', '-') }})</span></div>
            <form method="POST" action="{{ route('logout') }}">@csrf <button type="submit">Logout</button></form>
        </div>

        @if (session('success'))
            <section class="card" style="margin-bottom:18px; background:#dcfce7; color:#166534; font-weight:900">{{ session('success') }}</section>
        @endif

        @if ($errors->any())
            <section class="card" style="margin-bottom:18px; background:#fee2e2; color:#991b1b; font-weight:900">{{ $errors->first() }}</section>
        @endif

        <section class="hero">
            <h1>Portal Mahasiswa</h1>
            <p>Checklist syarat PPL ini ditarik dari Mahasiswa, Klinik, Bank, dan PPL Service lewat REST API. Ini demo paling kuat buat nunjukin microservice-nya beneran saling ngobrol.</p>
            <form class="toolbar" method="GET" action="{{ route('portal') }}">
                <input name="nim" value="{{ $nim }}" placeholder="NIM mahasiswa">
                <button type="submit">Cek Syarat</button>
                <a class="pill secondary" href="{{ route('dashboard') }}">Dashboard Admin</a>
            </form>
        </section>

        <section class="grid summary">
            @foreach ($items as $item)
                <article class="card check">
                    <div class="icon {{ $item['ok'] ? 'ok' : '' }}">{{ $item['ok'] ? '✓' : '!' }}</div>
                    <div>
                        <div class="label">{{ $item['label'] }}</div>
                        <div class="muted">{{ $item['detail'] }}</div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="card">
            <h2>Checklist Syarat PPL</h2>
            <div class="cta">
                {{ $canRegisterPpl ? 'Semua syarat terpenuhi. Mahasiswa boleh submit pendaftaran PPL.' : 'Belum boleh daftar PPL. Selesaikan item yang masih merah dulu.' }}
            </div>

            <form class="form-grid" method="POST" action="{{ route('portal.ppl-daftar') }}">
                @csrf
                <input type="hidden" name="nim" value="{{ $nim }}">
                <label>
                    Lokasi PPL
                    <input name="lokasi_ppl" value="{{ old('lokasi_ppl', 'Dinas Kominfo Banyumas') }}" placeholder="Contoh: Dinas Kominfo Banyumas" required {{ $canRegisterPpl ? '' : 'disabled' }}>
                </label>
                <label>
                    Tahun Ajaran
                    <input name="tahun_ajaran" value="{{ old('tahun_ajaran', now()->year.'/'.(now()->year + 1)) }}" placeholder="2026/2027" required {{ $canRegisterPpl ? '' : 'disabled' }}>
                </label>
                <button type="submit" {{ $canRegisterPpl ? '' : 'disabled' }}>Daftar PPL Sekarang</button>
            </form>
        </section>

        <section class="grid sections" style="margin-top:18px">
            <article class="card">
                <h2>Profil</h2>
                <p><strong>NIM:</strong> {{ data_get($profile, 'nim', $nim) }}</p>
                <p><strong>Nama:</strong> {{ data_get($profile, 'nama', '-') }}</p>
                <p><strong>Prodi:</strong> {{ data_get($profile, 'prodi', '-') }}</p>
                <p><strong>Status:</strong> <span class="badge green">{{ data_get($profile, 'status', '-') }}</span></p>
            </article>

            <article class="card">
                <h2>Kesehatan</h2>
                <p><strong>Status:</strong> <span class="badge {{ data_get($health, 'status_kesehatan') === 'layak' ? 'green' : 'red' }}">{{ data_get($health, 'status_kesehatan', '-') }}</span></p>
                <p><strong>Tekanan darah:</strong> {{ data_get($health, 'tekanan_darah', '-') }}</p>
                <p class="muted">{{ data_get($health, 'hasil_pemeriksaan', data_get($kesehatan, 'message', '-')) }}</p>
            </article>

            <article class="card">
                <h2>Pembayaran</h2>
                @if (is_array($bills) && count($bills) > 0)
                    <table><thead><tr><th>Kode</th><th>Jenis</th><th>Status</th></tr></thead><tbody>
                    @foreach ($bills as $bill)
                        <tr><td>{{ $bill['kode_tagihan'] ?? '-' }}</td><td>{{ $bill['jenis_pembayaran'] ?? '-' }}</td><td><span class="badge {{ ($bill['status_pembayaran'] ?? '') === 'lunas' ? 'green' : 'red' }}">{{ $bill['status_pembayaran'] ?? '-' }}</span></td></tr>
                    @endforeach
                    </tbody></table>
                @else
                    <p class="muted">Belum ada tagihan.</p>
                @endif
            </article>

            <article class="card">
                <h2>Status PPL</h2>
                @if (is_array($pplRows) && count($pplRows) > 0)
                    <table><thead><tr><th>Lokasi</th><th>Tahun</th><th>Status</th></tr></thead><tbody>
                    @foreach ($pplRows as $row)
                        <tr><td>{{ $row['lokasi_ppl'] ?? '-' }}</td><td>{{ $row['tahun_ajaran'] ?? '-' }}</td><td><span class="badge {{ ($row['status_pendaftaran'] ?? '') === 'disetujui' ? 'green' : 'yellow' }}">{{ $row['status_pendaftaran'] ?? '-' }}</span></td></tr>
                    @endforeach
                    </tbody></table>
                @else
                    <p class="muted">Belum ada pendaftaran PPL.</p>
                @endif
            </article>
        </section>
    </main>
</body>
</html>
