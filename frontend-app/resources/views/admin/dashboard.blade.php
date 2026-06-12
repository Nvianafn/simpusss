@extends('layouts.app')

@section('content')
@php
    $mahasiswaRows = data_get($mahasiswa, 'data.data', []);
    $pplRows = data_get($ppl, 'data.data', []);
    $pembayaranRows = data_get($pembayaran, 'data.data', []);
@endphp
<section class="hero">
    <h1>Dashboard Super Admin</h1>
    <p>Overview global lintas Mahasiswa, Klinik, Bank, dan PPL Service. Super admin punya akses ke semua area admin.</p>
</section>
<section class="grid stats">
    <article class="card"><div class="muted">Mahasiswa</div><h2>{{ data_get($mahasiswa, 'data.total', count($mahasiswaRows)) }}</h2></article>
    <article class="card"><div class="muted">Pendaftaran PPL</div><h2>{{ data_get($ppl, 'data.total', count($pplRows)) }}</h2></article>
    <article class="card"><div class="muted">Tagihan</div><h2>{{ data_get($pembayaran, 'data.total', count($pembayaranRows)) }}</h2></article>
    <article class="card"><div class="muted">Service</div><h2>4</h2></article>
</section>
<section class="card"><h2>Akses Cepat</h2><div class="actions"><a class="btn" href="{{ route('admin.mahasiswa') }}">Admin Mahasiswa</a><a class="btn" href="{{ route('admin.klinik') }}">Admin Klinik</a><a class="btn" href="{{ route('admin.pembayaran') }}">Admin Pembayaran</a><a class="btn" href="{{ route('admin.ppl') }}">Admin PPL</a></div></section>
@endsection
