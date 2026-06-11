# SIMPUS Microservices

SIMPUS adalah MVP sistem layanan kampus berbasis microservices untuk kebutuhan tugas kuliah/demo. Fokus backend saat ini adalah integrasi layanan Mahasiswa, Bank, Klinik, PPL, dan Auth dalam Docker Compose.

## Service

- `auth-service` — login, token Sanctum, user, role, permission, menu.
- `mahasiswa-service` — data mahasiswa dan status aktif.
- `bank-service` — tagihan, pembayaran, dan status lunas.
- `klinik-service` — hasil pemeriksaan dan status kesehatan/eligibility.
- `ppl-service` — pendaftaran PPL dengan validasi lintas service.
- `frontend-app` — aplikasi frontend/server-side client untuk mengakses backend internal.

## Port lokal

- Auth: `http://localhost:8000`
- Mahasiswa: `http://localhost:8001`
- PPL: `http://localhost:8002`
- Klinik: `http://localhost:8003`
- Bank: `http://localhost:8004`
- Frontend: `http://localhost:8080`

## Quick start lokal

```bash
docker compose up -d --build
```

Cek container:

```bash
docker compose ps
```

Jalankan migration/seed jika diperlukan:

```bash
for svc in auth-app mahasiswa-app ppl-app klinik-app bank-app; do
  docker compose exec -T "$svc" php artisan migrate --force
  docker compose exec -T "$svc" php artisan db:seed --force || true
done
```

Bersihkan cache/config setelah perubahan env:

```bash
for svc in auth-app mahasiswa-app ppl-app klinik-app bank-app; do
  docker compose exec -T "$svc" php artisan optimize:clear
done
```

Jika app container direcreate, restart Nginx sebelum HTTP smoke test:

```bash
docker compose restart auth-nginx mahasiswa-nginx ppl-nginx klinik-nginx bank-nginx frontend-nginx
```

## Environment penting

Backend menggunakan `CACHE_STORE=file` agar command Laravel seperti `optimize:clear` tidak bergantung pada tabel `cache`.

Service Mahasiswa, Bank, Klinik, dan PPL dilindungi oleh header internal:

```http
X-Internal-Token: <INTERNAL_API_TOKEN>
```

Jangan expose `INTERNAL_API_TOKEN` ke browser. Frontend sebaiknya memanggil service internal lewat server-side client/proxy yang membaca token dari environment container.

## Dokumentasi

- `docs/api-contract.md` — kontrak endpoint untuk integrasi frontend.
- `docs/backend-smoke-test.md` — checklist verifikasi backend MVP.
- `DEPLOY_VPS.md` — profil deploy VPS 2GB RAM / 25GB storage.
- `.env.vps.example` — template environment untuk VPS.

## Status backend MVP

Backend dianggap siap lanjut frontend jika:

- Docker stack hidup.
- `docker compose config --quiet` lolos.
- Semua backend bisa `php artisan optimize:clear`.
- Endpoint internal tanpa `X-Internal-Token` mengembalikan `401`.
- Flow Mahasiswa → Bank → Klinik → PPL lolos.
- PPL menolak NIM palsu/tidak eligible dengan `422`.

## Catatan batasan

Ini MVP untuk tugas kuliah/demo, bukan production high-traffic. Profil VPS sengaja dibuat hemat resource dan memakai satu MySQL container dengan database terpisah per service.
