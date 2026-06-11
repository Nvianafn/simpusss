# SIMPUS Backend Microservices

Backend-first implementation dari `prd.md`.

## Services

- `auth-service` — auth, user, role, permission, menu.
- `mahasiswa-service` — CRUD mahasiswa dan validasi status aktif.
- `klinik-service` — hasil pemeriksaan dan status kesehatan terkini.
- `bank-service` — tagihan, pembayaran, status lunas.
- `ppl-service` — pendaftaran PPL dengan validasi lintas service.

## Catatan Environment

Sesi saat scaffold dibuat tidak memiliki `php` di PATH dan Docker daemon belum aktif. Kode disiapkan agar bisa dijalankan setelah PHP 8.2+, Composer, dan Docker aktif.

## Setup Database

```bash
docker compose up -d
```

## Setup Tiap Service

Contoh untuk `auth-service`:

```bash
composer install --working-dir=auth-service
cp auth-service/.env.example auth-service/.env
php auth-service/artisan key:generate
php auth-service/artisan migrate --seed
php auth-service/artisan serve --port=8001
```

Ulangi untuk service lain dengan port:

- auth: 8001, DB port 33061
- mahasiswa: 8002, DB port 33062
- klinik: 8003, DB port 33063
- bank: 8004, DB port 33064
- ppl: 8005, DB port 33065

## Response JSON Standar

```json
{ "status": "success", "message": "OK", "data": {} }
```
