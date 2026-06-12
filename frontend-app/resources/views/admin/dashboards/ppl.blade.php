@extends('layouts.app')

@section('content')
@php($rows = data_get($ppl, 'data.data', []))
<section class="hero purple"><h1>Dashboard Admin PPL</h1><p>Ringkasan pendaftaran PPL. Approval dan reject tetap dilakukan di Admin PPL.</p></section>
<section class="grid stats"><article class="card"><div class="muted">Total Pendaftaran</div><h2>{{ data_get($ppl, 'data.total', count($rows)) }}</h2></article><article class="card"><div class="muted">Service</div><h2>PPL</h2></article><article class="card"><div class="muted">Status API</div><h2><span class="badge {{ data_get($ppl, '_meta.ok') ? 'green' : 'red' }}">{{ data_get($ppl, '_meta.ok') ? 'Online' : 'Error' }}</span></h2></article><article class="card"><div class="muted">Aksi</div><h2><a class="btn" href="{{ route('admin.ppl') }}">Kelola</a></h2></article></section>
@endsection
