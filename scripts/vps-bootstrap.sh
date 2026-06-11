#!/usr/bin/env bash
set -euo pipefail

COMPOSE="docker compose -f docker-compose.vps.yml"

$COMPOSE up -d --build simpus-db
$COMPOSE up -d --build auth-app auth-nginx mahasiswa-app mahasiswa-nginx klinik-app klinik-nginx bank-app bank-nginx ppl-app ppl-nginx frontend-app-php frontend-nginx

for svc in auth-app mahasiswa-app klinik-app bank-app ppl-app frontend-app-php; do
  echo "==> optimize:clear $svc"
  $COMPOSE exec -T "$svc" php artisan optimize:clear || true

done

for svc in auth-app mahasiswa-app klinik-app bank-app ppl-app; do
  echo "==> migrate --force $svc"
  $COMPOSE exec -T "$svc" php artisan migrate --force

done

echo "==> seed auth-service"
$COMPOSE exec -T auth-app php artisan db:seed --force || true

for svc in auth-app mahasiswa-app klinik-app bank-app ppl-app frontend-app-php; do
  echo "==> route cache $svc"
  $COMPOSE exec -T "$svc" php artisan config:cache || true
  $COMPOSE exec -T "$svc" php artisan route:cache || true
done

$COMPOSE ps
