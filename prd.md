# Product Requirements Document (PRD)
# SIMPUS — Sistem Informasi Manajemen Kampus Terpadu
**Versi:** 1.0.0  
**Tanggal:** Juni 2026  
**Status:** Draft  
**Tech Stack:** Laravel 12, Livewire 3, Tailwind CSS, MySQL/MariaDB, Docker Compose

---

## 1. Gambaran Produk

### 1.1 Latar Belakang

Kampus membutuhkan sistem terpadu yang menghubungkan proses administrasi mahasiswa, pendaftaran magang (PPL), pemeriksaan kesehatan, dan pembayaran dalam satu ekosistem digital. Saat ini proses-proses tersebut masih berjalan secara terpisah-pisah, menyebabkan inefisiensi, duplikasi data, dan sulitnya monitoring oleh pihak administrasi.

**SIMPUS (Sistem Informasi Manajemen Kampus Terpadu)** hadir sebagai solusi berbasis arsitektur terdistribusi yang mengintegrasikan seluruh proses tersebut melalui pendekatan microservice dengan RESTful API.

### 1.2 Visi Produk

Menjadi platform kampus digital tunggal yang memudahkan mahasiswa dan admin dalam mengelola seluruh proses akademik dan non-akademik secara efisien, transparan, dan real-time.

### 1.3 Tujuan Bisnis

- Mengurangi waktu proses pendaftaran PPL dari manual menjadi otomatis
- Memastikan validasi kesehatan dan pembayaran berjalan sistematis sebelum mahasiswa bisa daftar PPL
- Memberikan visibilitas penuh kepada admin atas status setiap mahasiswa
- Mengurangi kesalahan administrasi akibat proses manual

---

## 2. Ruang Lingkup Sistem

### 2.1 Komponen Utama (Microservice)

| Service | Fungsi |
|---|---|
| **Auth Service** | Autentikasi, manajemen user, role, permission, menu |
| **Mahasiswa Service** | Data profil mahasiswa, prodi, fakultas, angkatan |
| **PPL Service** | Pendaftaran magang, validasi, status, penempatan |
| **Klinik Service** | Pemeriksaan kesehatan, hasil cek, status layak/tidak layak |
| **Bank Service** | Tagihan, pembayaran, konfirmasi lunas |
| **Frontend App** | Admin Dashboard (Livewire) + Portal Mahasiswa (Livewire) |

### 2.2 Di Luar Ruang Lingkup (Out of Scope)

- Integrasi dengan sistem eksternal perguruan tinggi lain
- Aplikasi mobile native (Android/iOS)
- Sistem penilaian/grading akademik
- Sistem absensi

---

## 3. Pengguna (User Personas)

### 3.1 Super Admin
**Siapa:** Staf IT atau kepala administrasi kampus  
**Kebutuhan:** Mengelola seluruh sistem, termasuk user, role, permission, dan menu navigasi  
**Pain Point:** Tidak ada satu tempat untuk melihat dan mengelola seluruh data lintas service

### 3.2 Admin Mahasiswa
**Siapa:** Staf bagian kemahasiswaan  
**Kebutuhan:** Menambah, mengedit, dan memverifikasi data mahasiswa  
**Pain Point:** Data mahasiswa tersebar, sulit validasi status aktif/tidak aktif

### 3.3 Admin PPL
**Siapa:** Staf bagian akademik/magang  
**Kebutuhan:** Menerima atau menolak pendaftaran PPL, memonitor status tiap mahasiswa  
**Pain Point:** Harus mengecek manual kelengkapan syarat (kesehatan + bayar) sebelum approve

### 3.4 Admin Klinik
**Siapa:** Petugas klinik kampus  
**Kebutuhan:** Input hasil pemeriksaan kesehatan mahasiswa, update status layak/tidak layak  
**Pain Point:** Tidak ada sistem digital untuk mencatat dan membagikan hasil cek ke sistem lain

### 3.5 Admin Bank
**Siapa:** Staf keuangan kampus  
**Kebutuhan:** Membuat tagihan, konfirmasi pembayaran, memonitor tunggakan  
**Pain Point:** Sulit tracking pembayaran mana yang sudah/belum lunas per mahasiswa

### 3.6 Mahasiswa
**Siapa:** Mahasiswa aktif yang ingin mendaftar PPL  
**Kebutuhan:** Daftar PPL, lihat status kesehatan, cek dan bayar tagihan, pantau status pendaftaran  
**Pain Point:** Tidak tahu syarat apa saja yang sudah/belum terpenuhi untuk bisa daftar PPL

---

## 4. Fitur & Kebutuhan Fungsional

### 4.1 Auth Service

#### F-AUTH-01: Login Multi-Role
- User login menggunakan email & password
- Sistem mengembalikan JWT token beserta data role dan permission
- Session dikelola via Laravel Sanctum
- Redirect otomatis ke dashboard sesuai role setelah login

#### F-AUTH-02: Manajemen User (Super Admin)
- CRUD user dengan assign role
- Aktifasi/deaktivasi akun user
- Reset password oleh admin

#### F-AUTH-03: Manajemen Role & Permission (Super Admin)
- CRUD role (super_admin, admin_mahasiswa, admin_ppl, admin_klinik, admin_bank, mahasiswa)
- Assign/revoke permission ke role
- Permission granular per endpoint/fitur

#### F-AUTH-04: Manajemen Menu Dinamis (Super Admin)
- CRUD menu navigasi
- Assign menu ke role tertentu
- Menu yang ditampilkan di frontend menyesuaikan role user yang login

---

### 4.2 Mahasiswa Service

#### F-MAH-01: Data Profil Mahasiswa
- Menyimpan data: NIM, nama, email, prodi, fakultas, semester, angkatan, status (aktif/non-aktif/cuti/lulus)
- Format NIM: `[tahun_masuk][kode_univ][kode_prodi][kode_fak][nomor_urut]` — contoh: `234110601088`

#### F-MAH-02: CRUD Mahasiswa (Admin Mahasiswa)
- Tambah, edit, lihat, hapus data mahasiswa
- Filter & pencarian berdasarkan prodi, angkatan, semester, status
- Export data mahasiswa ke CSV

#### F-MAH-03: Verifikasi Status Mahasiswa
- API endpoint yang bisa dipanggil oleh service lain untuk validasi status aktif mahasiswa berdasarkan NIM

---

### 4.3 PPL Service

#### F-PPL-01: Pendaftaran PPL oleh Mahasiswa
- Mahasiswa mengisi form: lokasi PPL, tahun ajaran
- Sistem memvalidasi secara otomatis:
  1. Status mahasiswa aktif (→ Mahasiswa Service)
  2. Status kesehatan layak (→ Klinik Service)
  3. Status pembayaran lunas (→ Bank Service)
- Jika salah satu syarat belum terpenuhi, pendaftaran ditolak dengan pesan keterangan jelas

#### F-PPL-02: Review & Approval Pendaftaran (Admin PPL)
- Daftar semua pengajuan PPL dengan status (pending, disetujui, ditolak)
- Admin dapat approve atau reject dengan catatan
- Filter berdasarkan tahun ajaran, prodi, status

#### F-PPL-03: Monitoring Status PPL (Mahasiswa)
- Mahasiswa dapat melihat status pendaftarannya secara real-time
- Notifikasi in-app saat status berubah

#### F-PPL-04: Laporan PPL (Admin PPL)
- Rekap jumlah pendaftar per periode
- Status breakdown (pending, disetujui, ditolak)

---

### 4.4 Klinik Service

#### F-KLN-01: Input Hasil Pemeriksaan (Admin Klinik)
- Form input: tanggal cek, tinggi badan, berat badan, tekanan darah, hasil pemeriksaan (deskripsi bebas), status kesehatan (layak/tidak layak)
- Satu mahasiswa bisa punya riwayat pemeriksaan lebih dari satu

#### F-KLN-02: Status Kesehatan Terkini
- API endpoint untuk service lain mengambil status kesehatan terkini berdasarkan NIM
- Hanya pemeriksaan terbaru yang dijadikan acuan validasi PPL

#### F-KLN-03: Riwayat Kesehatan (Mahasiswa)
- Mahasiswa bisa melihat seluruh riwayat hasil pemeriksaan kesehatannya di portal

---

### 4.5 Bank Service

#### F-BNK-01: Pembuatan Tagihan (Admin Bank)
- Admin membuat tagihan: kode tagihan, jenis pembayaran (biaya PPL, dll.), nominal, jatuh tempo
- Tagihan diterbitkan per NIM mahasiswa

#### F-BNK-02: Konfirmasi Pembayaran (Admin Bank)
- Admin mengkonfirmasi pembayaran: metode pembayaran, tanggal bayar, update status ke "lunas"

#### F-BNK-03: Status Pembayaran (Mahasiswa)
- Mahasiswa melihat daftar tagihan dan status (belum bayar/lunas)
- API endpoint untuk validasi status lunas oleh PPL Service

#### F-BNK-04: Laporan Keuangan (Admin Bank)
- Rekap pembayaran per periode
- Daftar mahasiswa dengan tunggakan

---

### 4.6 Frontend Admin Dashboard

#### F-FE-01: Sidebar Menu Dinamis
- Menu navigasi dirender berdasarkan `role_menus` yang dikonfigurasi Super Admin
- Tidak ada hardcode menu di frontend

#### F-FE-02: Dashboard Overview (per role)
- Super Admin: ringkasan jumlah user, mahasiswa aktif, pendaftar PPL, transaksi hari ini
- Admin PPL: jumlah pending approval, disetujui hari ini
- Admin Klinik: jumlah pemeriksaan hari ini
- Admin Bank: jumlah tagihan belum lunas

---

### 4.7 Portal Mahasiswa

#### F-PM-01: Halaman Dashboard Mahasiswa
- Ringkasan status: profil, kesehatan, pembayaran, PPL
- Checklist syarat PPL dengan status tiap item (✅/❌)

#### F-PM-02: Halaman Profil
- Lihat data profil mahasiswa (read-only, edit oleh admin)

#### F-PM-03: Halaman Kesehatan
- Status kesehatan terkini
- Riwayat pemeriksaan

#### F-PM-04: Halaman Pembayaran
- Daftar tagihan dan status
- Instruksi pembayaran

#### F-PM-05: Halaman Daftar & Status PPL
- Form pendaftaran PPL
- Status pendaftaran real-time
- Catatan dari admin jika ditolak

---

## 5. Kebutuhan Non-Fungsional

### 5.1 Keamanan
- Semua endpoint API diproteksi JWT/Sanctum token
- RBAC (Role-Based Access Control) ketat — setiap route dicek permission-nya
- Database per service terisolasi (tidak ada direct cross-DB query)
- Validasi input di semua form (server-side)
- HTTPS mandatory di production

### 5.2 Performa
- Response time API < 500ms untuk operasi read biasa
- Sistem mampu melayani minimal 500 concurrent user
- Pagination wajib untuk semua endpoint daftar data

### 5.3 Keandalan
- Jika salah satu service down, service lain tetap berjalan (graceful degradation)
- Log error terpusat

### 5.4 Skalabilitas
- Setiap service dapat di-scale secara independen via Docker Compose

### 5.5 Kemudahan Penggunaan
- UI responsif (Tailwind CSS)
- Pesan error informatif (bukan kode HTTP mentah)
- Loading state di semua aksi async (Livewire)

---

## 6. Tech Stack & Arsitektur

```
Frontend App (Laravel 12 + Livewire 3 + Tailwind CSS)
        |
        | HTTP/REST
        |
   ┌────┴──────────────────────────────────┐
   │                                       │
Auth Service    Mahasiswa    PPL      Klinik    Bank
(Laravel 12)   Service      Service   Service   Service
   │            │            │         │         │
auth_db    mahasiswa_db   ppl_db  klinik_db  bank_db
(MySQL)     (MySQL)       (MySQL)  (MySQL)   (MySQL)
```

**Deployment:** Docker Compose di Ubuntu VPS  
**Testing:** Postman (API), Laravel Dusk (E2E, opsional)  
**Auth:** Laravel Sanctum + JWT

---

## 7. Struktur Database

### auth_db
```
users         : id, name, email, password, role_id, nim, is_active, timestamps
roles         : id, name, slug, timestamps
permissions   : id, name, slug, timestamps
role_permissions : role_id, permission_id
menus         : id, label, route, icon, parent_id, order
role_menus    : role_id, menu_id
```

### mahasiswa_db
```
mahasiswa : id, nim, nama, prodi, fakultas, semester, angkatan, status, email, timestamps
```

### ppl_db
```
pendaftaran_ppl : id, nim, lokasi_ppl, tahun_ajaran, tanggal_daftar,
                  status_pendaftaran (pending/disetujui/ditolak), catatan, timestamps
```

### klinik_db
```
hasil_kesehatan : id, nim, tanggal_cek, tinggi_badan, berat_badan,
                  tekanan_darah, hasil_pemeriksaan, status_kesehatan (layak/tidak_layak), timestamps
```

### bank_db
```
pembayaran : id, nim, kode_tagihan, jenis_pembayaran, nominal,
             tanggal_bayar, status_pembayaran (belum_bayar/lunas), metode_pembayaran, timestamps
```

---

## 8. API Contract

### Auth Service
| Method | Endpoint | Deskripsi |
|---|---|---|
| POST | /api/auth/login | Login, return token |
| POST | /api/auth/logout | Logout, revoke token |
| GET | /api/auth/me | Data user yang sedang login |
| GET | /api/users | Daftar user (Super Admin) |
| POST | /api/users | Buat user baru |
| PUT | /api/users/{id} | Edit user |
| DELETE | /api/users/{id} | Hapus user |

### Mahasiswa Service
| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | /api/mahasiswa | Daftar mahasiswa |
| GET | /api/mahasiswa/{nim} | Detail mahasiswa by NIM |
| POST | /api/mahasiswa | Tambah mahasiswa |
| PUT | /api/mahasiswa/{nim} | Edit mahasiswa |
| DELETE | /api/mahasiswa/{nim} | Hapus mahasiswa |

### PPL Service
| Method | Endpoint | Deskripsi |
|---|---|---|
| POST | /api/ppl/daftar | Mahasiswa daftar PPL |
| GET | /api/ppl/status/{nim} | Status pendaftaran PPL |
| GET | /api/ppl/pendaftaran | Semua pendaftaran (Admin) |
| PUT | /api/ppl/pendaftaran/{id}/approve | Approve pendaftaran |
| PUT | /api/ppl/pendaftaran/{id}/reject | Reject pendaftaran |

### Klinik Service
| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | /api/kesehatan/{nim} | Status kesehatan terkini |
| GET | /api/kesehatan/{nim}/riwayat | Riwayat pemeriksaan |
| POST | /api/kesehatan | Input hasil pemeriksaan |
| PUT | /api/kesehatan/{id} | Update hasil pemeriksaan |

### Bank Service
| Method | Endpoint | Deskripsi |
|---|---|---|
| GET | /api/pembayaran/{nim} | Tagihan mahasiswa |
| POST | /api/pembayaran | Buat tagihan |
| PUT | /api/pembayaran/{id}/konfirmasi | Konfirmasi lunas |
| GET | /api/pembayaran | Semua pembayaran (Admin) |

---

## 9. User Flow

### 9.1 User Flow: Mahasiswa Daftar PPL

```
[Mahasiswa Login]
       │
       ▼
[Portal Dashboard]
  ─ Tampil checklist syarat PPL:
    □ Status mahasiswa aktif?
    □ Sudah cek kesehatan & layak?
    □ Tagihan PPL sudah lunas?
       │
       ├─── Semua ✅ ──► [Tombol "Daftar PPL" aktif]
       │                        │
       │                        ▼
       │               [Form Pendaftaran PPL]
       │               (lokasi, tahun ajaran)
       │                        │
       │                        ▼
       │               [Submit → PPL Service]
       │               PPL Service validasi ulang ke:
       │               • Mahasiswa Service (status aktif?)
       │               • Klinik Service (layak?)
       │               • Bank Service (lunas?)
       │                        │
       │               ┌────────┴────────┐
       │             Semua OK         Ada yang gagal
       │               │                 │
       │               ▼                 ▼
       │        [Status: PENDING]  [Gagal + pesan error]
       │               │
       │               ▼
       │        [Admin PPL Review]
       │        Approve / Reject + catatan
       │               │
       │        ┌──────┴──────┐
       │      Approve        Reject
       │        │               │
       │        ▼               ▼
       └► [Status: DISETUJUI] [Status: DITOLAK + catatan]
          Mahasiswa dapat notifikasi
```

---

### 9.2 User Flow: Admin Klinik Input Pemeriksaan

```
[Admin Klinik Login]
       │
       ▼
[Dashboard Klinik]
  ─ Lihat daftar pemeriksaan hari ini
       │
       ▼
[Cari Mahasiswa by NIM]
       │
       ▼
[Tampil Data Mahasiswa dari Mahasiswa Service]
       │
       ▼
[Form Input Hasil Pemeriksaan]
  ─ Tanggal cek
  ─ Tinggi badan, berat badan
  ─ Tekanan darah
  ─ Hasil pemeriksaan (teks)
  ─ Status: Layak / Tidak Layak
       │
       ▼
[Simpan → klinik_db]
       │
       ▼
[Status kesehatan mahasiswa terupdate]
  ─ Bisa diakses PPL Service saat validasi
```

---

### 9.3 User Flow: Admin Bank Kelola Pembayaran

```
[Admin Bank Login]
       │
       ▼
[Dashboard Bank]
  ─ Rekap tagihan belum lunas
       │
       ├── [Buat Tagihan Baru]
       │       │
       │       ▼
       │   Input: NIM, jenis pembayaran,
       │          nominal, jatuh tempo
       │       │
       │       ▼
       │   [Tagihan tersimpan, status: belum_bayar]
       │
       └── [Konfirmasi Pembayaran]
               │
               ▼
           Cari tagihan by NIM / kode tagihan
               │
               ▼
           Input: metode bayar, tanggal bayar
               │
               ▼
           [Status tagihan → lunas]
           Bank Service siap diquery PPL Service
```

---

### 9.4 User Flow: Super Admin Setup Sistem

```
[Super Admin Login]
       │
       ▼
[Admin Dashboard]
       │
       ├── [Kelola Role]
       │       └── CRUD role → assign permission
       │
       ├── [Kelola User]
       │       └── Buat akun admin service
       │           (admin_ppl, admin_klinik, admin_bank, dll.)
       │
       ├── [Kelola Menu]
       │       └── CRUD menu → assign ke role
       │           (menu yang muncul di sidebar menyesuaikan role)
       │
       └── [Monitor Sistem]
               └── Lihat data lintas service
```

---

### 9.5 User Flow: Admin PPL Review Pendaftaran

```
[Admin PPL Login]
       │
       ▼
[Dashboard PPL]
  ─ Notifikasi: X pendaftaran pending
       │
       ▼
[Daftar Pendaftaran → filter: pending]
       │
       ▼
[Klik detail mahasiswa]
  ─ Lihat profil (dari Mahasiswa Service)
  ─ Lihat status kesehatan (dari Klinik Service)
  ─ Lihat status pembayaran (dari Bank Service)
  ─ Lihat data pendaftaran
       │
       ├── [Approve]         [Reject]
       │       │                 │
       │       ▼                 ▼
       │  Status: DISETUJUI   Input catatan penolakan
       │                         │
       │                         ▼
       │                    Status: DITOLAK
       │
       ▼
  Mahasiswa mendapat notifikasi in-app
```

---

## 10. Struktur Project

```
simpus/
├── docker-compose.yml
├── docs/
│   ├── PRD_SIMPUS.md
│   └── SDD_SIMPUS.md
│
├── frontend-app/              # Laravel 12 + Livewire 3
│   ├── app/
│   │   ├── Livewire/
│   │   │   ├── Admin/         # Komponen admin dashboard
│   │   │   └── Mahasiswa/     # Komponen portal mahasiswa
│   │   └── Http/
│   ├── resources/views/
│   └── ...
│
├── auth-service/              # Laravel 12 REST API
├── mahasiswa-service/         # Laravel 12 REST API
├── ppl-service/               # Laravel 12 REST API
├── klinik-service/            # Laravel 12 REST API
└── bank-service/              # Laravel 12 REST API
```

---

## 11. Prioritas Pengembangan (Roadmap)

### Phase 1 — Fondasi (Sprint 1-2)
- [ ] Setup Docker Compose, 5 service + frontend
- [ ] Auth Service: login, JWT, RBAC, menu dinamis
- [ ] Mahasiswa Service: CRUD data mahasiswa

### Phase 2 — Service Pendukung (Sprint 3-4)
- [ ] Klinik Service: input & query hasil pemeriksaan
- [ ] Bank Service: tagihan & konfirmasi pembayaran

### Phase 3 — Fitur Utama (Sprint 5-6)
- [ ] PPL Service: pendaftaran dengan validasi lintas service
- [ ] Portal Mahasiswa: dashboard, checklist syarat, form daftar PPL

### Phase 4 — Finalisasi (Sprint 7-8)
- [ ] Admin Dashboard: semua fitur admin per role
- [ ] Testing & QA (Postman collection)
- [ ] Deployment ke VPS

---

## 12. Kriteria Penerimaan (Acceptance Criteria)

- [ ] Mahasiswa yang belum cek kesehatan **tidak bisa** submit form daftar PPL
- [ ] Mahasiswa yang belum lunasi tagihan PPL **tidak bisa** submit form daftar PPL
- [ ] Setiap role hanya melihat menu dan data yang sesuai permission-nya
- [ ] Token expired → otomatis redirect ke halaman login
- [ ] Admin PPL bisa approve/reject dengan catatan, mahasiswa dapat notifikasi
- [ ] Seluruh service tetap berjalan meskipun salah satu service down (graceful degradation)
- [ ] Semua endpoint API mengembalikan response JSON konsisten (struktur: `status`, `message`, `data`)

---

*Dokumen ini merupakan living document dan akan diperbarui seiring perkembangan proyek.*