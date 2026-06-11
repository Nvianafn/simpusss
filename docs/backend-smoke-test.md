# SIMPUS Backend Smoke Test

Dokumen ini mencatat verifikasi minimal setelah backend SIMPUS dijalankan atau setelah container app direcreate.

## Tujuan

Smoke test membuktikan bahwa backend bukan sekadar `docker compose ps` berstatus `Up`, tapi benar-benar bisa menjalankan alur lintas service:

1. Mahasiswa aktif tersedia.
2. Tagihan Bank dibuat dan dikonfirmasi lunas.
3. Data Klinik menyatakan mahasiswa eligible.
4. Pendaftaran PPL berhasil dibuat.
5. Pendaftaran PPL bisa di-approve.
6. NIM palsu/tidak memenuhi syarat ditolak.
7. Endpoint internal menolak request tanpa `X-Internal-Token`.

## Jalankan stack lokal

```bash
docker compose up -d --build
```

Port lokal:

- Auth: `http://localhost:8000`
- Mahasiswa: `http://localhost:8001`
- PPL: `http://localhost:8002`
- Klinik: `http://localhost:8003`
- Bank: `http://localhost:8004`
- Frontend: `http://localhost:8080`

## Ambil token internal tanpa mencetak nilainya

Jangan hardcode token di dokumentasi atau script yang dipush. Ambil dari environment container saat runtime:

```bash
INTERNAL_API_TOKEN="$(docker exec mahasiswa-app sh -lc 'printf %s "$INTERNAL_API_TOKEN"')"
export INTERNAL_API_TOKEN
```

Gunakan header ini untuk request ke Mahasiswa, PPL, Klinik, dan Bank:

```http
X-Internal-Token: <INTERNAL_API_TOKEN>
```

## Cek cache/config backend

Semua backend wajib memakai file cache agar `optimize:clear` tidak bergantung ke tabel `cache`.

```bash
for svc in auth-app mahasiswa-app ppl-app klinik-app bank-app; do
  echo "=== $svc ==="
  docker exec "$svc" sh -lc 'echo "CACHE_STORE=$CACHE_STORE"; php artisan optimize:clear'
done
```

Expected:

```text
CACHE_STORE=file
... DONE
```

## Cek security minimal

Endpoint service internal tanpa token harus mengembalikan `401`.

Contoh:

```bash
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8001/api/mahasiswa
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8002/api/ppl/status/FAKE
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8003/api/kesehatan/FAKE
curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8004/api/pembayaran/FAKE/status
```

Expected semua:

```text
401
```

## Catatan penting setelah recreate app container

Jika container PHP-FPM app direcreate, restart Nginx container sebelum smoke test HTTP. Kalau tidak, Nginx bisa menahan upstream lama dan menghasilkan 404/bad routing meskipun `php artisan route:list` benar.

```bash
docker compose restart auth-nginx mahasiswa-nginx ppl-nginx klinik-nginx bank-nginx frontend-nginx
```

## Kriteria PASS backend MVP

Backend dianggap siap untuk integrasi frontend jika:

- `docker compose config --quiet` lolos.
- Semua app container bisa `php artisan optimize:clear`.
- Endpoint internal tanpa token menghasilkan `401`.
- Flow lintas service Mahasiswa → Bank → Klinik → PPL berjalan.
- PPL menolak NIM palsu/tidak eligible dengan `422`.
- Tidak ada secret asli ditulis di dokumentasi.
