@extends('layouts.app')

@section('content')
@php
    $profile = data_get($mahasiswa, 'data', []);
    $isActive = (bool) data_get($mahasiswaStatus, 'data.is_active', false);
@endphp
<section class="hero">
    <h1>Portal Mahasiswa</h1>
    <p>Area administrasi data mahasiswa. Halaman ini fokus ke identitas, status aktif, dan data akademik dasar mahasiswa.</p>
</section>

<section class="grid stats">
    <article class="card"><div class="muted">NIM</div><h2>{{ data_get($profile, 'nim', $nim) }}</h2></article>
    <article class="card"><div class="muted">Nama</div><h2>{{ data_get($profile, 'nama', '-') }}</h2></article>
    <article class="card"><div class="muted">Prodi</div><h2>{{ data_get($profile, 'prodi', '-') }}</h2></article>
    <article class="card"><div class="muted">Status</div><h2><span class="badge {{ $isActive ? 'green' : 'red' }}">{{ data_get($profile, 'status', $isActive ? 'aktif' : 'tidak aktif') }}</span></h2></article>
</section>

<section class="card">
    <h2>Detail Administrasi Mahasiswa</h2>
    <p><strong>Email:</strong> {{ data_get($profile, 'email', '-') }}</p>
    <p><strong>Semester:</strong> {{ data_get($profile, 'semester', '-') }}</p>
    <p><strong>Keterangan status:</strong> {{ data_get($mahasiswaStatus, 'message', '-') }}</p>
    <p class="muted">Kalau nanti ada fitur pengajuan perubahan biodata/upload dokumen, tempatnya di portal ini. Untuk MVP, mahasiswa hanya membaca data dari Mahasiswa Service.</p>
</section>
@endsection
