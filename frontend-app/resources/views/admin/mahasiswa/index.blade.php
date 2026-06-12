@php
    $rows = data_get($mahasiswa, 'data.data', []);
@endphp
@extends('layouts.app', ['title' => 'Admin Mahasiswa SIMPUS'])

@section('content')
<section class="hero">
    <h1>Admin Mahasiswa</h1>
    <p>Kelola data dasar mahasiswa via Mahasiswa Service. Semua request tetap lewat Laravel server-side client, bukan browser ke service internal.</p>
    <form class="toolbar" method="GET" action="{{ route('admin.mahasiswa') }}">
        <input name="q" placeholder="Cari NIM/nama" value="{{ $filters['q'] ?? '' }}">
        <select name="status">
            <option value="">Semua status</option>
            @foreach (['aktif', 'non_aktif', 'cuti', 'lulus'] as $value)
                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $value }}</option>
            @endforeach
        </select>
        <button type="submit">Filter</button>
    </form>
</section>

<section class="card">
    <h2>Tambah Mahasiswa</h2>
    <form class="form-grid" method="POST" action="{{ route('admin.mahasiswa.store') }}">
        @csrf
        <input name="nim" placeholder="NIM" value="{{ old('nim') }}" required>
        <input name="nama" placeholder="Nama" value="{{ old('nama') }}" required>
        <input name="email" type="email" placeholder="Email" value="{{ old('email') }}" required>
        <input name="prodi" placeholder="Prodi" value="{{ old('prodi') }}" required>
        <input name="fakultas" placeholder="Fakultas" value="{{ old('fakultas') }}" required>
        <input name="semester" type="number" min="1" max="14" placeholder="Semester" value="{{ old('semester') }}" required>
        <input name="angkatan" type="number" min="2000" max="2100" placeholder="Angkatan" value="{{ old('angkatan') }}" required>
        <select name="status" required>
            @foreach (['aktif', 'non_aktif', 'cuti', 'lulus'] as $value)
                <option value="{{ $value }}" @selected(old('status') === $value)>{{ $value }}</option>
            @endforeach
        </select>
        <button class="primary" type="submit">Simpan</button>
    </form>
</section>

<section class="card">
    <h2>Daftar Mahasiswa</h2>
    @if (is_array($rows) && count($rows) > 0)
        <table>
            <thead><tr><th>NIM</th><th>Nama</th><th>Prodi</th><th>Semester</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['nim'] ?? '-' }}</td>
                    <td>{{ $row['nama'] ?? '-' }}<br><span class="muted">{{ $row['email'] ?? '-' }}</span></td>
                    <td>{{ $row['prodi'] ?? '-' }}<br><span class="muted">{{ $row['fakultas'] ?? '-' }}</span></td>
                    <td>{{ $row['semester'] ?? '-' }} / {{ $row['angkatan'] ?? '-' }}</td>
                    <td><span class="badge {{ ($row['status'] ?? '') === 'aktif' ? 'green' : 'gray' }}">{{ $row['status'] ?? '-' }}</span></td>
                    <td class="actions">
                        <form class="inline-form" method="POST" action="{{ route('admin.mahasiswa.update', $row['nim'] ?? '-') }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="{{ ($row['status'] ?? '') === 'aktif' ? 'cuti' : 'aktif' }}">
                            <button type="submit">Toggle Aktif</button>
                        </form>
                        <form method="POST" action="{{ route('admin.mahasiswa.destroy', $row['nim'] ?? '-') }}" onsubmit="return confirm('Hapus mahasiswa ini?')">
                            @csrf @method('DELETE')
                            <button class="danger" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="empty">Belum ada data mahasiswa untuk filter ini.</p>
    @endif
</section>
@endsection
