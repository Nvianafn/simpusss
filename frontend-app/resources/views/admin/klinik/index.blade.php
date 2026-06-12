@php
    $latestData = data_get($latest, 'data.pemeriksaan');
    $rows = data_get($history, 'data.data', []);
@endphp
@extends('layouts.app', ['title' => 'Admin Klinik SIMPUS'])

@section('content')
<section class="hero green">
    <h1>Admin Klinik</h1>
    <p>Input dan cek status kesehatan mahasiswa dari Klinik Service. Ini menutup prasyarat PPL dari sisi kesehatan.</p>
    <form class="toolbar" method="GET" action="{{ route('admin.klinik') }}">
        <input name="nim" placeholder="Cari NIM" value="{{ $nim }}" required>
        <button type="submit">Cek Riwayat</button>
    </form>
</section>

@if ($nim !== '')
<section class="card">
    <h2>Status Terkini: {{ $nim }}</h2>
    @if (data_get($latest, '_meta.ok') === true && $latestData)
        <p><span class="badge {{ data_get($latest, 'data.is_eligible') ? 'green' : 'red' }}">{{ data_get($latest, 'data.is_eligible') ? 'layak' : 'tidak_layak' }}</span></p>
        <p class="muted">Tanggal: {{ $latestData['tanggal_cek'] ?? '-' }} · TD: {{ $latestData['tekanan_darah'] ?? '-' }} · TB/BB: {{ $latestData['tinggi_badan'] ?? '-' }}/{{ $latestData['berat_badan'] ?? '-' }}</p>
        <p>{{ $latestData['hasil_pemeriksaan'] ?? '-' }}</p>
    @else
        <p class="empty">{{ data_get($latest, 'message', 'Belum ada pemeriksaan.') }}</p>
    @endif
</section>
@endif

<section class="card">
    <h2>Tambah Pemeriksaan</h2>
    <form class="form-grid" method="POST" action="{{ route('admin.klinik.store') }}">
        @csrf
        <input name="nim" placeholder="NIM" value="{{ old('nim', $nim) }}" required>
        <input name="tanggal_cek" type="date" value="{{ old('tanggal_cek', now()->toDateString()) }}" required>
        <input name="tinggi_badan" type="number" min="1" placeholder="Tinggi badan" value="{{ old('tinggi_badan') }}" required>
        <input name="berat_badan" type="number" min="1" placeholder="Berat badan" value="{{ old('berat_badan') }}" required>
        <input name="tekanan_darah" placeholder="Tekanan darah" value="{{ old('tekanan_darah') }}" required>
        <select name="status_kesehatan" required>
            <option value="layak" @selected(old('status_kesehatan') === 'layak')>layak</option>
            <option value="tidak_layak" @selected(old('status_kesehatan') === 'tidak_layak')>tidak_layak</option>
        </select>
        <textarea class="full" name="hasil_pemeriksaan" placeholder="Hasil pemeriksaan" required>{{ old('hasil_pemeriksaan') }}</textarea>
        <button class="primary" type="submit">Simpan Pemeriksaan</button>
    </form>
</section>

<section class="card">
    <h2>Riwayat Pemeriksaan</h2>
    @if (is_array($rows) && count($rows) > 0)
        <table>
            <thead><tr><th>ID</th><th>Tanggal</th><th>Data</th><th>Status</th><th>Update Status</th></tr></thead>
            <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>#{{ $row['id'] ?? '-' }}</td>
                    <td>{{ $row['tanggal_cek'] ?? '-' }}</td>
                    <td>TD {{ $row['tekanan_darah'] ?? '-' }}<br><span class="muted">TB/BB {{ $row['tinggi_badan'] ?? '-' }}/{{ $row['berat_badan'] ?? '-' }}</span></td>
                    <td><span class="badge {{ ($row['status_kesehatan'] ?? '') === 'layak' ? 'green' : 'red' }}">{{ $row['status_kesehatan'] ?? '-' }}</span></td>
                    <td>
                        <form class="inline-form" method="POST" action="{{ route('admin.klinik.update', $row['id'] ?? 0) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="nim" value="{{ $row['nim'] ?? $nim }}">
                            <input type="hidden" name="status_kesehatan" value="{{ ($row['status_kesehatan'] ?? '') === 'layak' ? 'tidak_layak' : 'layak' }}">
                            <button type="submit">Toggle Layak</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="empty">Masukkan NIM untuk melihat riwayat, atau belum ada data pemeriksaan.</p>
    @endif
</section>
@endsection
