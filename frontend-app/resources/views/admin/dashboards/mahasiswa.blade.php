@extends('layouts.app')

@section('content')
@php($rows = data_get($mahasiswa, 'data.data', []))
<section class="hero"><h1>Dashboard Admin Mahasiswa</h1><p>Ringkasan operasional Mahasiswa Service untuk admin mahasiswa.</p></section>
<section class="grid stats"><article class="card"><div class="muted">Total Mahasiswa</div><h2>{{ data_get($mahasiswa, 'data.total', count($rows)) }}</h2></article><article class="card"><div class="muted">Service</div><h2>Mahasiswa</h2></article><article class="card"><div class="muted">Status API</div><h2><span class="badge {{ data_get($mahasiswa, '_meta.ok') ? 'green' : 'red' }}">{{ data_get($mahasiswa, '_meta.ok') ? 'Online' : 'Error' }}</span></h2></article><article class="card"><div class="muted">Aksi</div><h2><a class="btn" href="{{ route('admin.mahasiswa') }}">Kelola</a></h2></article></section>
@endsection
