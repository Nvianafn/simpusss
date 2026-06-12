@extends('layouts.app')

@section('content')
<section class="hero">
    <h1>SIMPUS Frontend</h1>
    <p>Frontend Laravel/Blade untuk integrasi Auth, Mahasiswa, Klinik, Bank, dan PPL Service. Login untuk masuk ke dashboard sesuai role.</p>
    <div class="toolbar"><a class="btn primary" href="{{ route('login') }}">Login</a></div>
</section>
@endsection
