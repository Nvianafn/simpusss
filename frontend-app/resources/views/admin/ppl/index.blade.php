@php
    $rows = data_get($pendaftaran, 'data.data', []);
@endphp
@extends('layouts.app', ['title' => 'Admin PPL SIMPUS'])

@section('content')
<section class="hero purple">
    <h1>Admin Approval PPL</h1>
    <p>Mahasiswa daftar PPL, admin melihat antrean, lalu approve/reject lewat PPL Service. Mutasi tetap server-side dengan internal token.</p>
    <form class="toolbar" method="GET" action="{{ route('admin.ppl') }}">
        <select name="status_pendaftaran">
            @foreach (['pending' => 'Pending', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'semua' => 'Semua'] as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit">Filter</button>
    </form>
</section>

<section class="card">
    <h2>Daftar Pendaftaran PPL</h2>
    @if (is_array($rows) && count($rows) > 0)
        <table>
            <thead><tr><th>ID</th><th>NIM</th><th>Lokasi</th><th>Tahun</th><th>Status</th><th>Catatan</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach ($rows as $row)
                @php
                    $rowStatus = $row['status_pendaftaran'] ?? '-';
                    $badgeClass = $rowStatus === 'disetujui' ? 'green' : ($rowStatus === 'ditolak' ? 'red' : 'yellow');
                @endphp
                <tr>
                    <td>#{{ $row['id'] ?? '-' }}</td>
                    <td>{{ $row['nim'] ?? '-' }}</td>
                    <td>{{ $row['lokasi_ppl'] ?? '-' }}</td>
                    <td>{{ $row['tahun_ajaran'] ?? '-' }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $rowStatus }}</span></td>
                    <td>{{ $row['catatan'] ?? '-' }}</td>
                    <td>
                        @if (($row['id'] ?? null) && $rowStatus === 'pending')
                            <div class="actions">
                                <form method="POST" action="{{ route('admin.ppl.approve', $row['id']) }}">@csrf <button type="submit">Approve</button></form>
                                <form class="inline-form" method="POST" action="{{ route('admin.ppl.reject', $row['id']) }}">
                                    @csrf
                                    <input name="catatan" placeholder="Alasan reject" required>
                                    <button class="danger" type="submit">Reject</button>
                                </form>
                            </div>
                        @else
                            <span class="muted">Tidak ada aksi</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p class="empty">Belum ada data pendaftaran untuk filter ini.</p>
    @endif
</section>
@endsection
