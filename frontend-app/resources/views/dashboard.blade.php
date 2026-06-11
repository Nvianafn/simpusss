@php
    $mahasiswaRows = data_get($mahasiswa, 'data.data', []);
    $pplRows = data_get($ppl, 'data.data', []);
    $pembayaranRows = data_get($pembayaran, 'data.data', []);
    $kesehatanData = data_get($kesehatan, 'data');

    $cards = [
        ['label' => 'Mahasiswa', 'value' => data_get($mahasiswa, 'data.total', count($mahasiswaRows)), 'service' => 'mahasiswa-service', 'ok' => data_get($mahasiswa, '_meta.ok', false)],
        ['label' => 'Pendaftaran PPL', 'value' => data_get($ppl, 'data.total', count($pplRows)), 'service' => 'ppl-service', 'ok' => data_get($ppl, '_meta.ok', false)],
        ['label' => 'Pembayaran', 'value' => data_get($pembayaran, 'data.total', count($pembayaranRows)), 'service' => 'bank-service', 'ok' => data_get($pembayaran, '_meta.ok', false)],
        ['label' => 'Kesehatan Dicek', 'value' => $selectedNim ?: '-', 'service' => 'klinik-service', 'ok' => data_get($kesehatan, '_meta.ok', false)],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SIMPUS Frontend</title>
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f6fb; color: #14213d; }
        a { color: inherit; }
        .shell { max-width: 1180px; margin: 0 auto; padding: 32px 18px 56px; }
        .hero { background: linear-gradient(135deg, #0f172a, #1d4ed8); color: white; border-radius: 28px; padding: 30px; box-shadow: 0 22px 55px rgba(15, 23, 42, .22); }
        .hero h1 { margin: 0 0 8px; font-size: clamp(30px, 5vw, 48px); letter-spacing: -0.04em; }
        .hero p { max-width: 760px; margin: 0; color: #dbeafe; line-height: 1.7; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 24px; align-items: center; }
        .toolbar input { min-width: 260px; border: 1px solid rgba(255,255,255,.28); background: rgba(255,255,255,.12); color: white; border-radius: 999px; padding: 12px 16px; outline: none; }
        .toolbar input::placeholder { color: #bfdbfe; }
        .toolbar button, .pill { border: 0; border-radius: 999px; padding: 12px 16px; font-weight: 700; background: #facc15; color: #111827; cursor: pointer; text-decoration: none; }
        .grid { display: grid; gap: 18px; }
        .stats { grid-template-columns: repeat(4, minmax(0, 1fr)); margin: 22px 0; }
        .card { background: white; border: 1px solid #e5e7eb; border-radius: 22px; padding: 20px; box-shadow: 0 10px 28px rgba(15, 23, 42, .06); }
        .stat .label { color: #64748b; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
        .stat .value { margin-top: 10px; font-size: 32px; font-weight: 900; letter-spacing: -0.04em; }
        .status { display: inline-flex; gap: 8px; align-items: center; margin-top: 14px; font-size: 13px; color: #475569; }
        .dot { width: 9px; height: 9px; border-radius: 50%; background: #ef4444; }
        .dot.ok { background: #22c55e; }
        .sections { grid-template-columns: 1.1fr .9fr; align-items: start; }
        h2 { margin: 0 0 14px; letter-spacing: -0.03em; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th { text-align: left; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: .07em; border-bottom: 1px solid #e5e7eb; padding: 10px 8px; }
        td { border-bottom: 1px solid #eef2f7; padding: 12px 8px; vertical-align: top; }
        .badge { display: inline-flex; border-radius: 999px; padding: 5px 10px; font-size: 12px; font-weight: 800; background: #e0f2fe; color: #075985; }
        .badge.green { background: #dcfce7; color: #166534; }
        .badge.yellow { background: #fef9c3; color: #854d0e; }
        .badge.red { background: #fee2e2; color: #991b1b; }
        .muted { color: #64748b; }
        .stack { display: grid; gap: 18px; }
        .empty { padding: 16px; border-radius: 16px; background: #f8fafc; color: #64748b; }
        .footer { margin-top: 18px; color: #64748b; font-size: 13px; }
        @media (max-width: 900px) { .stats, .sections { grid-template-columns: 1fr; } .hero { padding: 22px; } }
    </style>
</head>
<body>
    <main class="shell">
        <section class="hero">
            <h1>SIMPUS Frontend</h1>
            <p>Dashboard ini mengambil data dari backend microservice lewat REST API internal Docker, bukan baca database langsung. Ini pola yang lebih bener buat arsitektur service terpisah.</p>
            <form class="toolbar" method="GET" action="{{ route('dashboard') }}">
                <input name="nim" value="{{ $selectedNim }}" placeholder="Cek kesehatan berdasarkan NIM">
                <button type="submit">Cek NIM</button>
                <a class="pill" href="{{ route('dashboard') }}">Refresh Data</a>
            </form>
        </section>

        <section class="grid stats">
            @foreach ($cards as $card)
                <article class="card stat">
                    <div class="label">{{ $card['label'] }}</div>
                    <div class="value">{{ $card['value'] }}</div>
                    <div class="status"><span class="dot {{ $card['ok'] ? 'ok' : '' }}"></span>{{ $card['service'] }}</div>
                </article>
            @endforeach
        </section>

        <section class="grid sections">
            <article class="card">
                <h2>Data Mahasiswa</h2>
                @if (count($mahasiswaRows) === 0)
                    <div class="empty">Belum ada data mahasiswa atau service gagal dihubungi.</div>
                @else
                    <table>
                        <thead><tr><th>NIM</th><th>Nama</th><th>Prodi</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach ($mahasiswaRows as $row)
                                <tr>
                                    <td><strong>{{ $row['nim'] ?? '-' }}</strong></td>
                                    <td>{{ $row['nama'] ?? '-' }}<br><span class="muted">{{ $row['email'] ?? '-' }}</span></td>
                                    <td>{{ $row['prodi'] ?? '-' }}<br><span class="muted">Semester {{ $row['semester'] ?? '-' }}</span></td>
                                    <td><span class="badge green">{{ $row['status'] ?? '-' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </article>

            <div class="stack">
                <article class="card">
                    <h2>Status Kesehatan</h2>
                    @if (is_array($kesehatanData) && count($kesehatanData) > 0 && ! isset($kesehatanData['data']))
                        <p><strong>NIM:</strong> {{ $kesehatanData['nim'] ?? $selectedNim }}</p>
                        <p><strong>Status:</strong> <span class="badge green">{{ $kesehatanData['status_kesehatan'] ?? $kesehatanData['status'] ?? 'tersedia' }}</span></p>
                        <p class="muted">{{ $kesehatanData['catatan'] ?? $kesehatan['message'] ?? 'Data kesehatan terbaru tersedia.' }}</p>
                    @else
                        <div class="empty">{{ $kesehatan['message'] ?? 'Data kesehatan belum tersedia.' }}</div>
                    @endif
                </article>

                <article class="card">
                    <h2>Pendaftaran PPL</h2>
                    @if (count($pplRows) === 0)
                        <div class="empty">Belum ada data PPL.</div>
                    @else
                        <table>
                            <thead><tr><th>NIM</th><th>Lokasi</th><th>Status</th></tr></thead>
                            <tbody>
                                @foreach ($pplRows as $row)
                                    <tr>
                                        <td><strong>{{ $row['nim'] ?? '-' }}</strong></td>
                                        <td>{{ $row['lokasi_ppl'] ?? '-' }}<br><span class="muted">{{ $row['tahun_ajaran'] ?? '-' }}</span></td>
                                        <td><span class="badge {{ ($row['status_pendaftaran'] ?? '') === 'disetujui' ? 'green' : 'yellow' }}">{{ $row['status_pendaftaran'] ?? '-' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </article>
            </div>
        </section>

        <section class="card" style="margin-top:18px">
            <h2>Pembayaran</h2>
            @if (count($pembayaranRows) === 0)
                <div class="empty">Belum ada data pembayaran.</div>
            @else
                <table>
                    <thead><tr><th>Kode</th><th>NIM</th><th>Jenis</th><th>Nominal</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($pembayaranRows as $row)
                            <tr>
                                <td><strong>{{ $row['kode_tagihan'] ?? '-' }}</strong></td>
                                <td>{{ $row['nim'] ?? '-' }}</td>
                                <td>{{ $row['jenis_pembayaran'] ?? '-' }}</td>
                                <td>Rp {{ number_format((float) ($row['nominal'] ?? 0), 0, ',', '.') }}</td>
                                <td><span class="badge {{ ($row['status_pembayaran'] ?? '') === 'lunas' ? 'green' : 'red' }}">{{ $row['status_pembayaran'] ?? '-' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <p class="footer">Sumber data: mahasiswa-service, ppl-service, klinik-service, bank-service lewat REST API.</p>
    </main>
</body>
</html>
