@extends('layouts.app')

@section('content')
@php($rows = data_get($mahasiswa, 'data.data', []))
<section class="hero green"><h1>Dashboard Admin Klinik</h1><p>Ringkasan operasional Klinik Service. Detail pemeriksaan tetap dikelola di halaman Admin Klinik.</p></section>
<section class="grid stats"><article class="card"><div class="muted">Mahasiswa Referensi</div><h2>{{ data_get($mahasiswa, 'data.total', count($rows)) }}</h2></article><article class="card"><div class="muted">Service</div><h2>Klinik</h2></article><article class="card"><div class="muted">Status API</div><h2><span class="badge {{ data_get($mahasiswa, '_meta.ok') ? 'green' : 'red' }}">{{ data_get($mahasiswa, '_meta.ok') ? 'Online' : 'Error' }}</span></h2></article><article class="card"><div class="muted">Aksi</div><h2><a class="btn" href="{{ route('admin.klinik') }}">Kelola</a></h2></article></section>
@endsection
