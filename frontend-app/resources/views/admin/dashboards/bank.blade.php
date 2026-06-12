@extends('layouts.app')

@section('content')
@php($rows = data_get($pembayaran, 'data.data', []))
<section class="hero orange"><h1>Dashboard Admin Bank</h1><p>Ringkasan tagihan dan pembayaran. Operasi create/confirm ada di Admin Pembayaran.</p></section>
<section class="grid stats"><article class="card"><div class="muted">Total Tagihan</div><h2>{{ data_get($pembayaran, 'data.total', count($rows)) }}</h2></article><article class="card"><div class="muted">Service</div><h2>Bank</h2></article><article class="card"><div class="muted">Status API</div><h2><span class="badge {{ data_get($pembayaran, '_meta.ok') ? 'green' : 'red' }}">{{ data_get($pembayaran, '_meta.ok') ? 'Online' : 'Error' }}</span></h2></article><article class="card"><div class="muted">Aksi</div><h2><a class="btn" href="{{ route('admin.pembayaran') }}">Kelola</a></h2></article></section>
@endsection
