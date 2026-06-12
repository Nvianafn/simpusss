@extends('layouts.app')

@section('content')
@php
    $health = data_get($kesehatan, 'data.pemeriksaan', data_get($kesehatan, 'data', []));
    $history = data_get($kesehatanHistory, 'data.data', data_get($kesehatanHistory, 'data', []));
    $eligible = (bool) data_get($kesehatan, 'data.is_eligible', false);
@endphp
<section class="hero green">
    <h1>Portal Klinik</h1>
    <p>Area mahasiswa untuk melihat status kesehatan, hasil pemeriksaan, dan kelayakan kesehatan sebagai syarat administrasi PPL.</p>
</section>

<section class="grid stats">
    <article class="card"><div class="muted">NIM</div><h2>{{ $nim }}</h2></article>
    <article class="card"><div class="muted">Kelayakan</div><h2><span class="badge {{ $eligible ? 'green' : 'red' }}">{{ $eligible ? 'Layak' : 'Belum layak' }}</span></h2></article>
    <article class="card"><div class="muted">Status Kesehatan</div><h2>{{ data_get($health, 'status_kesehatan', '-') }}</h2></article>
    <article class="card"><div class="muted">Tekanan Darah</div><h2>{{ data_get($health, 'tekanan_darah', '-') }}</h2></article>
</section>

<section class="card">
    <h2>Hasil Pemeriksaan Terbaru</h2>
    <p>{{ data_get($health, 'hasil_pemeriksaan', data_get($kesehatan, 'message', 'Belum ada data pemeriksaan.')) }}</p>
</section>

<section class="card">
    <h2>Riwayat Pemeriksaan</h2>
    @if (is_array($history) && count($history) > 0)
        <table><thead><tr><th>Tanggal</th><th>Status</th><th>Catatan</th></tr></thead><tbody>
        @foreach ($history as $row)
            <tr><td>{{ $row['tanggal_pemeriksaan'] ?? '-' }}</td><td><span class="badge {{ ($row['status_kesehatan'] ?? '') === 'layak' ? 'green' : 'red' }}">{{ $row['status_kesehatan'] ?? '-' }}</span></td><td>{{ $row['hasil_pemeriksaan'] ?? '-' }}</td></tr>
        @endforeach
        </tbody></table>
    @else
        <div class="empty">Belum ada riwayat pemeriksaan.</div>
    @endif
</section>
@endsection
