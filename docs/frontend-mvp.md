# SIMPUS Frontend MVP

Dokumen ini mencatat scope frontend MVP SIMPUS untuk `frontend-app`.

## Prinsip Integrasi

Frontend Laravel/Blade berperan sebagai server-side integration layer.

- Browser tidak memanggil service internal secara langsung.
- `INTERNAL_API_TOKEN` hanya dibaca di server Laravel melalui `config/services.php`.
- Semua akses Mahasiswa, PPL, Klinik, dan Bank dari frontend harus lewat `App\Services\SimpusApiClient`.
- Jangan menulis token/password/API key asli di dokumentasi, `.env.example`, commit message, atau output test.

## Route Utama

Public/Auth:

- `GET /` — entry dashboard; redirect sesuai role jika sudah login.
- `GET /dashboard` — dashboard mahasiswa untuk role `mahasiswa`.
- `GET /login` — form login.
- `POST /login` — login via Auth Service.
- `POST /logout` — hapus session frontend.

Portal layanan untuk mahasiswa:

- `GET /portal` — redirect legacy ke Portal PPL.
- `GET /portal/mahasiswa` — administrasi data mahasiswa.
- `GET /portal/klinik` — status dan riwayat kesehatan mahasiswa.
- `GET /portal/bank` — tagihan dan status pembayaran mahasiswa.
- `GET /portal/ppl` — checklist syarat dan pendaftaran PPL.
- `POST /portal/ppl-daftar` — daftar PPL.

Admin dashboard:

- `GET /admin/dashboard` — dashboard global super admin.
- `GET /admin/mahasiswa/dashboard`
- `GET /admin/klinik/dashboard`
- `GET /admin/bank/dashboard`
- `GET /admin/ppl/dashboard`

Admin PPL:

- `GET /admin/ppl`
- `POST /admin/ppl/{id}/approve`
- `POST /admin/ppl/{id}/reject`

Admin Mahasiswa:

- `GET /admin/mahasiswa`
- `POST /admin/mahasiswa`
- `PUT /admin/mahasiswa/{nim}`
- `DELETE /admin/mahasiswa/{nim}`

Admin Klinik:

- `GET /admin/klinik`
- `POST /admin/klinik`
- `PUT /admin/klinik/{id}`

Admin Pembayaran:

- `GET /admin/pembayaran`
- `POST /admin/pembayaran` — buat tagihan.
- `POST /admin/pembayaran/{id}/konfirmasi` — konfirmasi lunas.

## Role Protection

Route frontend memakai middleware `simpus.role`.

- `mahasiswa` dan `super_admin`: portal mahasiswa/klinik/bank/PPL untuk mahasiswa.
- `super_admin`: dashboard global dan semua dashboard/admin service.
- `admin_ppl` dan `super_admin`: dashboard/admin PPL.
- `admin_mahasiswa` dan `super_admin`: dashboard/admin Mahasiswa.
- `admin_klinik` dan `super_admin`: dashboard/admin Klinik.
- `admin_bank` dan `super_admin`: dashboard Bank dan admin Pembayaran.

Navbar layout juga menyembunyikan menu yang tidak sesuai role. Ini hanya UX; proteksi utama tetap middleware route.

## Error Handling

Controller admin melakukan validasi request Laravel sebelum memanggil API internal. Response gagal dari service internal ditampilkan sebagai flash error.

Yang sudah ditangani:

- validasi form gagal akan redirect balik dengan error.
- response internal gagal (`_meta.ok !== true`) akan redirect balik dengan pesan error.
- base URL/token internal kosong akan dikembalikan sebagai response error dari `SimpusApiClient`.
- service unreachable ditangkap sebagai response error, bukan fatal exception.

## Menjalankan Build

```bash
npm run build --prefix frontend-app
```

Jika build dalam container PHP gagal karena `npm` tidak tersedia, jalankan dari host/project root seperti di atas.

## Smoke Test Frontend

Script reusable tersedia di:

```bash
scripts/smoke-frontend.sh
```

Jalankan dengan credential dari environment, jangan hardcode secret:

```bash
SIMPUS_SMOKE_ADMIN_EMAIL='[REDACTED]' \
SIMPUS_SMOKE_ADMIN_PASSWORD='[REDACTED]' \
scripts/smoke-frontend.sh all
```

Mode:

- `all` — negative checks + create/confirm pembayaran + cleanup.
- `negative` — cek akses tanpa login dan validasi gagal.
- `payment` — create dan confirm tagihan lewat frontend.
- `cleanup` — hapus data dummy pembayaran smoke test.

## Checklist Sebelum Commit

- `git status --short`
- `git diff`
- `php -l` untuk file PHP yang berubah.
- `npm run build --prefix frontend-app`
- `scripts/smoke-frontend.sh all`
- secret leak check untuk token/password/cookie.

## Batasan Saat Ini

- Desain UI final belum dikerjakan di sini karena sedang dikerjakan terpisah.
- Desain visual final masih placeholder; layout dan screen sudah dipisah sesuai sitemap multi-portal/multi-dashboard.
- Script smoke frontend saat ini fokus pada proteksi dasar, validasi negatif pembayaran, dan flow buat/konfirmasi tagihan. Belum mencakup seluruh kombinasi role salah per halaman.
