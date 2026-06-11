# SIMPUS API Contract — Backend Phase 1

Dokumen ini adalah kontrak API untuk integrasi frontend SIMPUS Phase 1.

- Base project: `/home/apan/coding/simpusss`
- Format response standar:

```json
{
  "status": "success|error",
  "message": "Pesan singkat",
  "data": {}
}
```

- Content-Type request JSON:

```http
Content-Type: application/json
Accept: application/json
```

- Header wajib untuk semua endpoint Mahasiswa, Bank, Klinik, dan PPL:

```http
X-Internal-Token: <INTERNAL_API_TOKEN>
```

Semua route API pada service Mahasiswa, Bank, Klinik, dan PPL berada di dalam middleware `VerifyInternalToken`, termasuk endpoint `GET`. Tanpa header ini, service harus mengembalikan `401 Unauthorized`. Frontend tidak boleh memanggil service-service ini langsung dari browser dengan token internal bocor; panggilan sebaiknya diproxy oleh backend/frontend server-side client yang menyimpan token di environment.

- Status code umum:
  - `200 OK`: request sukses.
  - `201 Created`: data berhasil dibuat.
  - `401 Unauthorized`: login gagal, token tidak valid, atau `X-Internal-Token` tidak cocok.
  - `404 Not Found`: data tidak ditemukan.
  - `409 Conflict`: data bentrok/duplikat.
  - `422 Unprocessable Content`: validasi gagal atau syarat bisnis tidak terpenuhi.
  - `500 Internal Server Error`: konfigurasi service belum lengkap, misalnya `INTERNAL_API_TOKEN` kosong.

## Service Base URL

Untuk akses dari host/browser lokal:

- Auth Service: `http://localhost:8000`
- Mahasiswa Service: `http://localhost:8001`
- PPL Service: `http://localhost:8002`
- Klinik Service: `http://localhost:8003`
- Bank Service: `http://localhost:8004`
- Frontend App: `http://localhost:8080`

Untuk komunikasi antar-container Docker:

- Auth: `http://auth-nginx`
- Mahasiswa: `http://mahasiswa-nginx`
- PPL: `http://ppl-nginx`
- Klinik: `http://klinik-nginx`
- Bank: `http://bank-nginx`

---

# 1. Auth Service

Base URL: `http://localhost:8000`

## 1.1 Login

```http
POST /api/auth/login
```

Request:

```json
{
  "email": "admin@simpus.test",
  "password": "password123"
}
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Login berhasil",
  "data": {
    "token": "SANCTUM_TOKEN",
    "user": {
      "id": 1,
      "name": "...",
      "email": "admin@simpus.test",
      "role": {
        "id": 1,
        "name": "super_admin",
        "permissions": [],
        "menus": []
      }
    }
  }
}
```

Failure response: `401`

```json
{
  "status": "error",
  "message": "Email, password, atau status akun tidak valid.",
  "data": null
}
```

Catatan frontend:

- Simpan `data.token` untuk request protected route.
- Kirim token sebagai Bearer token:

```http
Authorization: Bearer SANCTUM_TOKEN
```

## 1.2 Current User

```http
GET /api/auth/me
Authorization: Bearer SANCTUM_TOKEN
```

Success response: `200`

```json
{
  "status": "success",
  "message": "User aktif",
  "data": {
    "id": 1,
    "email": "admin@simpus.test",
    "role": {
      "permissions": [],
      "menus": []
    }
  }
}
```

## 1.3 Logout

```http
POST /api/auth/logout
Authorization: Bearer SANCTUM_TOKEN
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Logout berhasil",
  "data": null
}
```

## 1.4 Protected Management Endpoints

Semua endpoint berikut wajib Bearer token:

```http
GET    /api/users
POST   /api/users
GET    /api/users/{id}
PUT    /api/users/{id}
DELETE /api/users/{id}

GET    /api/roles
POST   /api/roles
PUT    /api/roles/{id}
DELETE /api/roles/{id}
PUT    /api/roles/{id}/permissions
PUT    /api/roles/{id}/menus

GET    /api/permissions
POST   /api/permissions

GET    /api/menus
POST   /api/menus
```

---

# 2. Mahasiswa Service

Base URL: `http://localhost:8001`

## 2.1 List Mahasiswa

```http
GET /api/mahasiswa
```

Query params opsional:

- `prodi`
- `fakultas`
- `angkatan`
- `semester`
- `status`: `aktif|non_aktif|cuti|lulus`
- `q`: search by `nim` atau `nama`
- `per_page`: default `15`

Success response: `200`

```json
{
  "status": "success",
  "message": "Daftar mahasiswa",
  "data": {
    "current_page": 1,
    "data": [],
    "per_page": 15,
    "total": 0
  }
}
```

## 2.2 Create Mahasiswa

```http
POST /api/mahasiswa
```

Request:

```json
{
  "nim": "234110601088",
  "nama": "Novian Affan Ashofah",
  "email": "apan@example.test",
  "prodi": "Informatika",
  "fakultas": "Sains dan Teknologi",
  "semester": 6,
  "angkatan": 2023,
  "status": "aktif"
}
```

Validation:

- `nim`: required, string, max 32, unique.
- `nama`: required, string, max 255.
- `email`: required, email, unique.
- `prodi`: required.
- `fakultas`: required.
- `semester`: required, integer, 1-14.
- `angkatan`: required, integer, 2000-2100.
- `status`: required, one of `aktif`, `non_aktif`, `cuti`, `lulus`.

Success response: `201`

```json
{
  "status": "success",
  "message": "Mahasiswa dibuat",
  "data": {
    "nim": "234110601088",
    "nama": "Novian Affan Ashofah",
    "status": "aktif"
  }
}
```

## 2.3 Detail Mahasiswa

```http
GET /api/mahasiswa/{nim}
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Detail mahasiswa",
  "data": {
    "nim": "234110601088",
    "nama": "Novian Affan Ashofah",
    "status": "aktif"
  }
}
```

Not found response: `404`

```json
{
  "status": "error",
  "message": "Mahasiswa tidak ditemukan",
  "data": null
}
```

## 2.4 Update Mahasiswa

```http
PUT /api/mahasiswa/{nim}
```

Request fields opsional:

```json
{
  "nama": "Nama Baru",
  "email": "emailbaru@example.test",
  "prodi": "Informatika",
  "fakultas": "Sains dan Teknologi",
  "semester": 7,
  "angkatan": 2023,
  "status": "aktif"
}
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Mahasiswa diperbarui",
  "data": {}
}
```

## 2.5 Delete Mahasiswa

```http
DELETE /api/mahasiswa/{nim}
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Mahasiswa dihapus",
  "data": null
}
```

## 2.6 Status Mahasiswa

```http
GET /api/mahasiswa/{nim}/status
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Status mahasiswa",
  "data": {
    "nim": "234110601088",
    "status": "aktif",
    "is_active": true
  }
}
```

Not found response: `404`

```json
{
  "status": "error",
  "message": "Mahasiswa tidak ditemukan",
  "data": {
    "nim": "999999999999",
    "is_active": false
  }
}
```

---

# 3. Bank Service

Base URL: `http://localhost:8004`

## 3.1 List Pembayaran

```http
GET /api/pembayaran
```

Query params opsional:

- `nim`
- `jenis_pembayaran`
- `status_pembayaran`
- `per_page`

Success response: `200`

```json
{
  "status": "success",
  "message": "Daftar pembayaran",
  "data": {
    "current_page": 1,
    "data": [],
    "per_page": 15,
    "total": 0
  }
}
```

## 3.2 Create Tagihan

```http
POST /api/pembayaran
```

Request:

```json
{
  "nim": "234110601088",
  "kode_tagihan": "PPL-234110601088-001",
  "jenis_pembayaran": "biaya_ppl",
  "nominal": 500000,
  "jatuh_tempo": "2026-07-01"
}
```

Validation:

- `nim`: required, string.
- `kode_tagihan`: required, string, unique.
- `jenis_pembayaran`: required, string.
- `nominal`: required, numeric, min 0.
- `jatuh_tempo`: optional date.

Success response: `201`

```json
{
  "status": "success",
  "message": "Tagihan dibuat",
  "data": {
    "id": 1,
    "nim": "234110601088",
    "kode_tagihan": "PPL-234110601088-001",
    "jenis_pembayaran": "biaya_ppl",
    "nominal": "500000.00",
    "status_pembayaran": "belum_bayar"
  }
}
```

## 3.3 Tagihan by NIM

```http
GET /api/pembayaran/{nim}
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Tagihan mahasiswa",
  "data": []
}
```

## 3.4 Status Pembayaran

```http
GET /api/pembayaran/{nim}/status?jenis_pembayaran=biaya_ppl
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Status pembayaran",
  "data": {
    "nim": "234110601088",
    "jenis_pembayaran": "biaya_ppl",
    "is_paid": true
  }
}
```

Catatan:

- Jika `jenis_pembayaran` tidak dikirim, default backend adalah `biaya_ppl`.
- `is_paid` bernilai true jika ada pembayaran dengan `status_pembayaran = lunas`.

## 3.5 Konfirmasi Pembayaran

```http
PUT /api/pembayaran/{id}/konfirmasi
```

Request:

```json
{
  "metode_pembayaran": "transfer",
  "tanggal_bayar": "2026-06-11"
}
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Pembayaran dikonfirmasi",
  "data": {
    "id": 1,
    "status_pembayaran": "lunas",
    "metode_pembayaran": "transfer",
    "tanggal_bayar": "2026-06-11T00:00:00.000000Z"
  }
}
```

---

# 4. Klinik Service

Base URL: `http://localhost:8003`

## 4.1 Latest Status Kesehatan

```http
GET /api/kesehatan/{nim}
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Status kesehatan terkini",
  "data": {
    "nim": "234110601088",
    "is_eligible": true,
    "pemeriksaan": {
      "id": 1,
      "nim": "234110601088",
      "status_kesehatan": "layak"
    }
  }
}
```

Not found response: `404`

```json
{
  "status": "error",
  "message": "Belum ada pemeriksaan kesehatan",
  "data": {
    "nim": "999999999999",
    "is_eligible": false
  }
}
```

## 4.2 Riwayat Kesehatan

```http
GET /api/kesehatan/{nim}/riwayat
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Riwayat kesehatan",
  "data": {
    "current_page": 1,
    "data": [],
    "per_page": 15
  }
}
```

## 4.3 Create Hasil Kesehatan

```http
POST /api/kesehatan
```

Request:

```json
{
  "nim": "234110601088",
  "tanggal_cek": "2026-06-11",
  "tinggi_badan": 170,
  "berat_badan": 65,
  "tekanan_darah": "120/80",
  "hasil_pemeriksaan": "Sehat dan layak mengikuti PPL",
  "status_kesehatan": "layak"
}
```

Validation:

- `nim`: required, string.
- `tanggal_cek`: required, date.
- `tinggi_badan`: required, integer, min 1.
- `berat_badan`: required, integer, min 1.
- `tekanan_darah`: required, string, max 32.
- `hasil_pemeriksaan`: required, string.
- `status_kesehatan`: required, one of `layak`, `tidak_layak`.

Success response: `201`

```json
{
  "status": "success",
  "message": "Hasil pemeriksaan dibuat",
  "data": {
    "id": 1,
    "nim": "234110601088",
    "status_kesehatan": "layak"
  }
}
```

## 4.4 Update Hasil Kesehatan

```http
PUT /api/kesehatan/{id}
```

Request fields opsional:

```json
{
  "tanggal_cek": "2026-06-12",
  "tinggi_badan": 170,
  "berat_badan": 66,
  "tekanan_darah": "120/80",
  "hasil_pemeriksaan": "Update hasil",
  "status_kesehatan": "layak"
}
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Hasil pemeriksaan diperbarui",
  "data": {}
}
```

---

# 5. PPL Service

Base URL: `http://localhost:8002`

PPL Service melakukan eligibility check ke service lain:

- Mahasiswa: `GET http://mahasiswa-nginx/api/mahasiswa/{nim}/status`
- Klinik: `GET http://klinik-nginx/api/kesehatan/{nim}`
- Bank: `GET http://bank-nginx/api/pembayaran/{nim}/status?jenis_pembayaran=biaya_ppl`

## 5.1 Daftar PPL

```http
POST /api/ppl/daftar
```

Request:

```json
{
  "nim": "234110601088",
  "lokasi_ppl": "Dinas Kominfo Banyumas",
  "tahun_ajaran": "2026/2027"
}
```

Validation:

- `nim`: required, string.
- `lokasi_ppl`: required, string, max 255.
- `tahun_ajaran`: required, string, max 32.

Business rules:

- Mahasiswa harus aktif.
- Status kesehatan harus layak.
- Tagihan `biaya_ppl` harus lunas.

Success response: `201`

```json
{
  "status": "success",
  "message": "Pendaftaran PPL berhasil dibuat",
  "data": {
    "id": 1,
    "nim": "234110601088",
    "lokasi_ppl": "Dinas Kominfo Banyumas",
    "tahun_ajaran": "2026/2027",
    "status_pendaftaran": "pending"
  }
}
```

Rejected response karena syarat belum terpenuhi: `422`

```json
{
  "status": "error",
  "message": "Pendaftaran PPL ditolak karena syarat belum terpenuhi.",
  "data": {
    "requirements": {
      "mahasiswa_active": false,
      "health_eligible": false,
      "payment_paid": false,
      "raw": {
        "mahasiswa": {},
        "klinik": {},
        "bank": {}
      }
    },
    "missing": [
      "Status mahasiswa tidak aktif atau tidak ditemukan",
      "Status kesehatan belum layak",
      "Tagihan PPL belum lunas"
    ]
  }
}
```

Duplicate response untuk NIM + tahun ajaran yang sudah terdaftar: `409`

```json
{
  "status": "error",
  "message": "Mahasiswa sudah terdaftar PPL pada tahun ajaran ini.",
  "data": {
    "nim": "234110601088",
    "tahun_ajaran": "2026/2027",
    "pendaftaran": {
      "id": 1,
      "nim": "234110601088",
      "status_pendaftaran": "pending"
    }
  }
}
```

## 5.2 Status Pendaftaran PPL by NIM

```http
GET /api/ppl/status/{nim}
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Status pendaftaran PPL",
  "data": [
    {
      "id": 1,
      "nim": "234110601088",
      "lokasi_ppl": "Dinas Kominfo Banyumas",
      "tahun_ajaran": "2026/2027",
      "status_pendaftaran": "pending",
      "catatan": null
    }
  ]
}
```

## 5.3 List Pendaftaran PPL

```http
GET /api/ppl/pendaftaran
```

Query params opsional:

- `nim`
- `tahun_ajaran`
- `status_pendaftaran`
- `per_page`

Success response: `200`

```json
{
  "status": "success",
  "message": "Daftar pendaftaran PPL",
  "data": {
    "current_page": 1,
    "data": [],
    "per_page": 15,
    "total": 0
  }
}
```

## 5.4 Approve Pendaftaran PPL

```http
PUT /api/ppl/pendaftaran/{id}/approve
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Pendaftaran PPL disetujui",
  "data": {
    "id": 1,
    "status_pendaftaran": "disetujui",
    "catatan": null
  }
}
```

## 5.5 Reject Pendaftaran PPL

```http
PUT /api/ppl/pendaftaran/{id}/reject
```

Request:

```json
{
  "catatan": "Berkas belum lengkap"
}
```

Success response: `200`

```json
{
  "status": "success",
  "message": "Pendaftaran PPL ditolak",
  "data": {
    "id": 1,
    "status_pendaftaran": "ditolak",
    "catatan": "Berkas belum lengkap"
  }
}
```

---

# 6. Frontend Integration Flow

Flow minimum yang harus diikuti frontend:

1. Login admin ke Auth Service:
   - `POST /api/auth/login`
   - simpan token.

2. Siapkan data mahasiswa:
   - `POST /api/mahasiswa`
   - atau cek existing: `GET /api/mahasiswa/{nim}/status`.

3. Buat tagihan PPL di Bank:
   - `POST /api/pembayaran`

4. Konfirmasi pembayaran:
   - `PUT /api/pembayaran/{id}/konfirmasi`

5. Buat hasil kesehatan layak:
   - `POST /api/kesehatan`

6. Daftar PPL:
   - `POST /api/ppl/daftar`
   - jika semua syarat terpenuhi, response `201`.
   - jika tidak, tampilkan `data.missing` ke user.

7. Admin approve/reject:
   - `PUT /api/ppl/pendaftaran/{id}/approve`
   - atau `PUT /api/ppl/pendaftaran/{id}/reject`

---

# 7. Verified Test Data

Data yang sudah pernah dipakai saat test backend:

```json
{
  "nim": "234110601088",
  "nama": "Novian Affan Ashofah",
  "prodi": "Informatika",
  "fakultas": "Sains dan Teknologi",
  "semester": 6,
  "angkatan": 2023,
  "status": "aktif",
  "jenis_pembayaran": "biaya_ppl",
  "lokasi_ppl": "Dinas Kominfo Banyumas",
  "tahun_ajaran": "2026/2027"
}
```

Negative path test:

```json
{
  "nim": "999999999999",
  "lokasi_ppl": "Tempat Test",
  "tahun_ajaran": "2026/2027"
}
```

Expected negative response:

- HTTP `422`
- `data.missing` berisi alasan syarat tidak terpenuhi.

---

# 8. Known Backend Notes

- Auth Service memakai Laravel Sanctum token.
- PPL, Klinik, Bank, Mahasiswa endpoint saat ini belum diproteksi token Auth.
- PPL melakukan HTTP client call ke internal service URL dari config `services.php` dan environment variable Docker.
- `ppl-db` memakai host port `33066` karena `33065` bentrok dengan proses lokal `language_` milik user `apan`.
- `/home/apan/coding/simpusss` saat dokumentasi ini dibuat bukan git repository.
