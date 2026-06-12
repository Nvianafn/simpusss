@extends('layouts.app')

@section('content')
@php
    $bills = data_get($pembayaran, 'data.data', data_get($pembayaran, 'data', []));
    $isPaid = (bool) data_get($pembayaranStatus, 'data.is_paid', false);
@endphp
<section class="hero orange">
    <h1>Portal Bank</h1>
    <p>Area mahasiswa untuk melihat tagihan, status pembayaran, dan riwayat pembayaran administrasi.</p>
</section>

<section class="grid stats">
    <article class="card"><div class="muted">NIM</div><h2>{{ $nim }}</h2></article>
    <article class="card"><div class="muted">Status Syarat Pembayaran</div><h2><span class="badge {{ $isPaid ? 'green' : 'red' }}">{{ $isPaid ? 'Lunas' : 'Belum lunas' }}</span></h2></article>
    <article class="card"><div class="muted">Jumlah Tagihan</div><h2>{{ is_array($bills) ? count($bills) : 0 }}</h2></article>
    <article class="card"><div class="muted">Service</div><h2>Bank</h2></article>
</section>

<section class="card">
    <h2>Daftar Tagihan</h2>
    @if (is_array($bills) && count($bills) > 0)
        <table><thead><tr><th>Kode</th><th>Jenis</th><th>Nominal</th><th>Jatuh Tempo</th><th>Status</th></tr></thead><tbody>
        @foreach ($bills as $bill)
            <tr>
                <td><strong>{{ $bill['kode_tagihan'] ?? '-' }}</strong></td>
                <td>{{ $bill['jenis_pembayaran'] ?? '-' }}</td>
                <td>Rp {{ number_format((float) ($bill['nominal'] ?? 0), 0, ',', '.') }}</td>
                <td>{{ $bill['jatuh_tempo'] ?? '-' }}</td>
                <td><span class="badge {{ ($bill['status_pembayaran'] ?? '') === 'lunas' ? 'green' : 'red' }}">{{ $bill['status_pembayaran'] ?? '-' }}</span></td>
            </tr>
        @endforeach
        </tbody></table>
    @else
        <div class="empty">Belum ada tagihan.</div>
    @endif
</section>
@endsection
