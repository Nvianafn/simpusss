# SIMPUS VPS Deployment Profile (2GB RAM / 25GB Storage)

Profile ini mempertahankan bentuk microservice, tapi memakai **1 container MySQL** berisi 5 database agar lebih realistis untuk VPS kecil.

## File penting

- `docker-compose.vps.yml` — compose production ringan
- `.env.vps.example` — contoh secret VPS
- `docker/mysql/init/01-create-databases.sql` — membuat `auth_db`, `mahasiswa_db`, `ppl_db`, `klinik_db`, `bank_db`
- `docker/mysql/conf.d/low-memory.cnf` — tuning MySQL RAM kecil
- `docker/php-fpm/zz-simpus-low-memory.conf` — tuning PHP-FPM/opcache RAM kecil
- `scripts/vps-bootstrap.sh` — build, migrate, seed, cache

## Deploy pertama kali di VPS

```bash
cd /path/to/simpusss
cp .env.vps.example .env
nano .env
```

Isi secret kuat:

```env
MYSQL_ROOT_PASSWORD=isi-password-panjang
INTERNAL_API_TOKEN=isi-token-internal-panjang
```

Jalankan:

```bash
./scripts/vps-bootstrap.sh
```

Frontend akan expose ke port 80:

```text
http://IP-VPS/
```

Backend service tidak diexpose ke internet di profile VPS ini. Frontend dan service lain mengaksesnya lewat network Docker internal:

- `http://auth-nginx`
- `http://mahasiswa-nginx`
- `http://ppl-nginx`
- `http://klinik-nginx`
- `http://bank-nginx`

## Operasi harian

Lihat container:

```bash
docker compose -f docker-compose.vps.yml ps
```

Lihat log:

```bash
docker compose -f docker-compose.vps.yml logs -f --tail=100 frontend-nginx frontend-app-php
```

Restart:

```bash
docker compose -f docker-compose.vps.yml restart
```

Update setelah pull kode:

```bash
docker compose -f docker-compose.vps.yml up -d --build
for svc in auth-app mahasiswa-app klinik-app bank-app ppl-app frontend-app-php; do
  docker compose -f docker-compose.vps.yml exec -T "$svc" php artisan optimize:clear
  docker compose -f docker-compose.vps.yml exec -T "$svc" php artisan config:cache || true
  docker compose -f docker-compose.vps.yml exec -T "$svc" php artisan route:cache || true
done
```

## Catatan batasan

- Profile ini dibuat untuk demo/MVP, bukan high-traffic production.
- Semua database berada dalam satu MySQL container, tapi tetap memakai database terpisah per service.
- Memory limit sengaja ketat. Kalau request berat gagal, naikkan `mem_limit` app dari `192m` ke `256m`.
- Jangan jalankan `docker-compose.yml` dan `docker-compose.vps.yml` bersamaan di VPS yang sama karena container name-nya sama.
