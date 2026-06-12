@php
    $rows = data_get($pembayaran, 'data.data', []);
@endphp
@extends('layouts.app', ['title' => 'Admin Pembayaran SIMPUS'])

@section('content')
<section class="hero green">
    <h1>Admin Pembayaran</h1>
    <p>Konfirmasi tagihan Bank Service. Setelah lunas, Portal Mahasiswa dan PPL Service membaca syarat pembayaran sebagai terpenuhi.</p>
    <form class="toolbar" method="GET" action="{{ route('admin.pembayaran') }}">
        <select name="status_pembayaran">
            @foreach (['belum_bayar' => 'Belum Bayar', 'lunas' => 'Lunas', 'semua' => 'Semua'] as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <input name="nim" value="{{ $nim }}" placeholder="Filter NIM opsional">
        <button type="submit">Filter</button>
    </form>
</section>

<section class="card">
    <h2>Buat Tagihan</h2>
    <p class="muted">Gunakan ini untuk setup tagihan PPL tanpa harus call Bank API manual. Kode tagihan harus unik.</p>
    <form class="form-grid" method="POST" action="{{ route('admin.pembayaran.store') }}">
        @csrf
        <input name="nim" placeholder="NIM" value="{{ old('nim', $nim) }}" required>
        <input name="kode_tagihan" placeholder="Kode tagihan unik" value="{{ old('kode_tagihan') }}" required>
        <select name="jenis_pembayaran" required>
            @foreach (['biaya_ppl' => 'Biaya PPL', 'ukt' => 'UKT', 'lainnya' => 'Lainnya'] as $value => $label)
                <option value="{{ $value }}" @selected(old('jenis_pembayaran', 'biaya_ppl') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <input name="nominal" type="number" min="0" step="0.01" placeholder="Nominal" value="{{ old('nominal') }}" required>
        <input name="jatuh_tempo" type="date" value="{{ old('jatuh_tempo') }}">
        <button class="primary" type="submit">Buat Tagihan</button>
    </form>
</section>

<section class="card">
    <h2>Daftar Tagihan</h2>
    @if (is_array($rows) && count($rows) > 0)
        <table>
            <thead><tr><th>ID</th><th>NIM</th><th>Kode</th><th>Jenis</th><th>Nominal</th><th>Status</th><th>Metode/Tanggal</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach ($rows as $row)
                @php $isPaid = ($row['status_pembayaran'] ?? '') === 'lunas'; @endphp
                <tr>
                    <td>#{{ $row['id'] ?? '-' }}</td>
                    <td>{{ $row['nim'] ?? '-' }}</td>
                    <td>{{ $row['kode_tagihan'] ?? '-' }}</td>
                    <td>{{ $row['jenis_pembayaran'] ?? '-' }}</td>
                    <td>Rp {{ number_format((float) ($row['nominal'] ?? 0), 0, ',', '.') }}</td>
                    <td><span class="badge {{ $isPaid ? 'green' : 'red' }}">{{ $row['status_pembayaran'] ?? '-' }}</span></td>
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
        <p class="empty">Belum ada tagihan untuk filter ini.</p>
    @endif
</section>
@endsection
