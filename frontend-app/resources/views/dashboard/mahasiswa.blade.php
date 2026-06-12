@extends('layouts.app')

@section('content')
@php
    $profile = data_get($mahasiswa, 'data', []);
    $healthOk = (bool) data_get($kesehatan, 'data.is_eligible', false);
    $paymentOk = (bool) data_get($pembayaranStatus, 'data.is_paid', false);
    $activeOk = (bool) data_get($mahasiswaStatus, 'data.is_active', false);
@endphp
<section class="hero">
    <h1>Dashboard Mahasiswa</h1>
    <p>Ringkasan semua layanan untuk mahasiswa. Dari sini mahasiswa masuk ke portal Mahasiswa, Klinik, Bank, dan PPL.</p>
</section>

<section class="grid stats">
    <article class="card"><div class="muted">Mahasiswa</div><h2>{{ data_get($profile, 'nama', '-') }}</h2><p>{{ $nim ?? data_get($profile, 'nim', '-') }}</p></article>
    <article class="card"><div class="muted">Status Akademik</div><h2><span class="badge {{ $activeOk ? 'green' : 'red' }}">{{ $activeOk ? 'Aktif' : 'Tidak aktif' }}</span></h2></article>
    <article class="card"><div class="muted">Klinik</div><h2><span class="badge {{ $healthOk ? 'green' : 'red' }}">{{ $healthOk ? 'Layak' : 'Belum layak' }}</span></h2></article>
    <article class="card"><div class="muted">Pembayaran</div><h2><span class="badge {{ $paymentOk ? 'green' : 'red' }}">{{ $paymentOk ? 'Lunas' : 'Belum lunas' }}</span></h2></article>
</section>

<section class="grid sections">
    <article class="card">
        <h2>Portal Layanan</h2>
        <div class="actions">
            <a class="btn primary" href="{{ route('portal.mahasiswa') }}">Portal Mahasiswa</a>
            <a class="btn primary" href="{{ route('portal.klinik') }}">Portal Klinik</a>
            <a class="btn primary" href="{{ route('portal.bank') }}">Portal Bank</a>
            <a class="btn primary" href="{{ route('portal.ppl') }}">Portal PPL</a>
        </div>
    </article>
    <article class="card">
        <h2>Status PPL</h2>
        <p class="muted">Pendaftaran PPL hanya bisa diproses setelah status mahasiswa aktif, kesehatan layak, dan pembayaran terkait sudah lunas.</p>
        <a class="btn" href="{{ route('portal.ppl') }}">Cek syarat PPL</a>
    </article>
</section>
@endsection
