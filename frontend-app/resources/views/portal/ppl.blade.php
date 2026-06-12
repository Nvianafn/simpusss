@extends('layouts.app')

@section('content')
@php
    $pplRows = data_get($pplStatus, 'data', []);
    $items = [
        ['label' => 'Status mahasiswa aktif', 'ok' => $requirements['mahasiswa_active'], 'detail' => data_get($mahasiswaStatus, 'message', '-')],
        ['label' => 'Kesehatan layak', 'ok' => $requirements['health_eligible'], 'detail' => data_get($kesehatan, 'message', '-')],
        ['label' => 'Tagihan PPL lunas', 'ok' => $requirements['payment_paid'], 'detail' => data_get($pembayaranStatus, 'message', '-')],
    ];
@endphp
<section class="hero purple">
    <h1>Portal PPL</h1>
    <p>Area mahasiswa untuk mengecek syarat lintas service dan memproses pendaftaran PPL.</p>
    <form class="toolbar" method="GET" action="{{ route('portal.ppl') }}">
        <input name="nim" value="{{ $nim }}" placeholder="NIM mahasiswa">
        <button type="submit">Cek Syarat</button>
    </form>
</section>

<section class="grid stats">
    @foreach ($items as $item)
        <article class="card">
            <div class="muted">{{ $item['label'] }}</div>
            <h2><span class="badge {{ $item['ok'] ? 'green' : 'red' }}">{{ $item['ok'] ? 'Terpenuhi' : 'Belum' }}</span></h2>
            <p class="muted">{{ $item['detail'] }}</p>
        </article>
    @endforeach
    <article class="card"><div class="muted">Keputusan</div><h2><span class="badge {{ $canRegisterPpl ? 'green' : 'red' }}">{{ $canRegisterPpl ? 'Boleh daftar' : 'Belum boleh' }}</span></h2></article>
</section>

<section class="card">
    <h2>Form Pendaftaran PPL</h2>
    <p class="muted">Form ini tetap submit ke Laravel frontend. Browser tidak mengakses PPL Service langsung.</p>
    <form class="form-grid" method="POST" action="{{ route('portal.ppl-daftar') }}">
        @csrf
        <input type="hidden" name="nim" value="{{ $nim }}">
        <label>Lokasi PPL
            <input name="lokasi_ppl" value="{{ old('lokasi_ppl', 'Dinas Kominfo Banyumas') }}" placeholder="Contoh: Dinas Kominfo Banyumas" required {{ $canRegisterPpl ? '' : 'disabled' }}>
        </label>
        <label>Tahun Ajaran
            <input name="tahun_ajaran" value="{{ old('tahun_ajaran', now()->year.'/'.(now()->year + 1)) }}" placeholder="2026/2027" required {{ $canRegisterPpl ? '' : 'disabled' }}>
        </label>
        <div class="full actions"><button type="submit" {{ $canRegisterPpl ? '' : 'disabled' }}>Daftar PPL Sekarang</button></div>
    </form>
</section>

<section class="card">
    <h2>Status Pendaftaran PPL</h2>
    @if (is_array($pplRows) && count($pplRows) > 0)
        <table><thead><tr><th>Lokasi</th><th>Tahun</th><th>Status</th></tr></thead><tbody>
        @foreach ($pplRows as $row)
            <tr><td>{{ $row['lokasi_ppl'] ?? '-' }}</td><td>{{ $row['tahun_ajaran'] ?? '-' }}</td><td><span class="badge {{ ($row['status_pendaftaran'] ?? '') === 'disetujui' ? 'green' : 'yellow' }}">{{ $row['status_pendaftaran'] ?? '-' }}</span></td></tr>
        @endforeach
        </tbody></table>
    @else
        <div class="empty">Belum ada pendaftaran PPL.</div>
    @endif
</section>
@endsection
