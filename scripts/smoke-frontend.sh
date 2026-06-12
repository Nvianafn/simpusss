#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${SIMPUS_FRONTEND_URL:-http://localhost:8080}"
ADMIN_EMAIL="${SIMPUS_SMOKE_ADMIN_EMAIL:-}"
ADMIN_PASSWORD="${SIMPUS_SMOKE_ADMIN_PASSWORD:-}"
PROJECT_DIR="${SIMPUS_PROJECT_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
RUN_ID="${SIMPUS_SMOKE_RUN_ID:-$(( $(date +%s) % 1000000 ))}"
NIM="${SIMPUS_SMOKE_NIM:-99777${RUN_ID}}"
KODE_TAGIHAN="${SIMPUS_SMOKE_KODE_TAGIHAN:-FRONT-SMOKE-${RUN_ID}}"
COOKIE_JAR="$(mktemp)"
LOGIN_HTML="$(mktemp)"
cleanup_files() { rm -f "$COOKIE_JAR" "$LOGIN_HTML"; }
trap cleanup_files EXIT

pass() { printf 'PASS: %s\n' "$1"; }
fail() { printf 'FAIL: %s\n' "$1" >&2; exit 1; }

require_credentials() {
  if [[ -z "$ADMIN_EMAIL" || -z "$ADMIN_PASSWORD" ]]; then
    fail "Set SIMPUS_SMOKE_ADMIN_EMAIL and SIMPUS_SMOKE_ADMIN_PASSWORD. Do not hardcode secrets in this script."
  fi
}

csrf_from_file() {
  python3 - "$1" <<'PY'
import re, sys
html = open(sys.argv[1], encoding='utf-8', errors='ignore').read()
match = re.search(r'name=["\']_token["\']\s+value=["\']([^"\']+)', html)
if not match:
    match = re.search(r'value=["\']([^"\']+)["\']\s+name=["\']_token["\']', html)
if not match:
    raise SystemExit('CSRF token not found')
print(match.group(1))
PY
}

http_code() {
  curl -sS -o /dev/null -w '%{http_code}' "$@"
}

login_admin() {
  require_credentials
  curl -sS -c "$COOKIE_JAR" "$BASE_URL/login" -o "$LOGIN_HTML"
  local token
  token="$(csrf_from_file "$LOGIN_HTML")"
  local code
  code="$(curl -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" -o /dev/null -w '%{http_code}' \
    -X POST "$BASE_URL/login" \
    --data-urlencode "_token=$token" \
    --data-urlencode "email=$ADMIN_EMAIL" \
    --data-urlencode "password=$ADMIN_PASSWORD")"
  [[ "$code" == "302" ]] || fail "login admin expected 302, got $code"

  code="$(curl -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" -o /dev/null -w '%{http_code}' "$BASE_URL/admin/pembayaran")"
  [[ "$code" == "200" ]] || fail "login admin did not reach protected page; expected /admin/pembayaran 200, got $code"
  pass "login_admin"
}

negative_checks() {
  local code
  code="$(http_code "$BASE_URL/admin/pembayaran")"
  [[ "$code" == "302" ]] || fail "unauth admin pembayaran expected 302, got $code"
  pass "unauth_admin_redirect"

  login_admin

  curl -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" "$BASE_URL/admin/pembayaran" -o "$LOGIN_HTML"
  local form_token
  form_token="$(csrf_from_file "$LOGIN_HTML")"
  code="$(curl -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" -o /dev/null -w '%{http_code}' \
    -X POST "$BASE_URL/admin/pembayaran" \
    -H 'Content-Type: application/x-www-form-urlencoded' \
    --data-urlencode "_token=$form_token" \
    --data-urlencode "nim=" \
    --data-urlencode "kode_tagihan=" \
    --data-urlencode "jenis_pembayaran=" \
    --data-urlencode "nominal=-1")"
  [[ "$code" == "302" ]] || fail "invalid create tagihan expected validation redirect 302, got $code"
  pass "invalid_tagihan_validation_redirect"

  code="$(curl -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" -o /dev/null -w '%{http_code}' \
    -X POST "$BASE_URL/admin/pembayaran/999999999/konfirmasi" \
    -H 'Content-Type: application/x-www-form-urlencoded' \
    --data-urlencode "_token=$form_token" \
    --data-urlencode "metode_pembayaran=smoke" \
    --data-urlencode "tanggal_bayar=$(date +%F)")"
  [[ "$code" == "302" ]] || fail "confirm missing payment expected handled redirect 302, got $code"
  pass "missing_payment_confirm_handled"
}

create_and_confirm_payment() {
  login_admin

  curl -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" "$BASE_URL/admin/pembayaran" -o "$LOGIN_HTML"
  local form_token
  form_token="$(csrf_from_file "$LOGIN_HTML")"

  local code
  code="$(curl -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" -o /dev/null -w '%{http_code}' \
    -X POST "$BASE_URL/admin/pembayaran" \
    -H 'Content-Type: application/x-www-form-urlencoded' \
    --data-urlencode "_token=$form_token" \
    --data-urlencode "nim=$NIM" \
    --data-urlencode "kode_tagihan=$KODE_TAGIHAN" \
    --data-urlencode "jenis_pembayaran=PPL" \
    --data-urlencode "nominal=150000" \
    --data-urlencode "jatuh_tempo=$(date -d '+14 days' +%F)")"
  [[ "$code" == "302" ]] || fail "create tagihan expected 302, got $code"
  pass "create_tagihan_frontend"

  local payment_id
  payment_id="$(docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T bank-db sh -lc \
    "mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\" \"\$MYSQL_DATABASE\" -N -B -e \"SELECT id FROM pembayaran WHERE kode_tagihan='${KODE_TAGIHAN}' LIMIT 1;\"" 2>/dev/null | tr -d '\r')"
  [[ -n "$payment_id" ]] || fail "created payment not found in bank DB"
  pass "verify_tagihan_db"

  code="$(curl -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" -o /dev/null -w '%{http_code}' \
    -X POST "$BASE_URL/admin/pembayaran/$payment_id/konfirmasi" \
    -H 'Content-Type: application/x-www-form-urlencoded' \
    --data-urlencode "_token=$form_token" \
    --data-urlencode "metode_pembayaran=smoke" \
    --data-urlencode "tanggal_bayar=$(date +%F)")"
  [[ "$code" == "302" ]] || fail "confirm tagihan expected 302, got $code"
  pass "confirm_tagihan_frontend"
}

assert_auth_route() {
  local path="$1"
  local label="$2"
  local code
  code="$(curl -sSL -b "$COOKIE_JAR" -c "$COOKIE_JAR" -o /dev/null -w '%{http_code}' "$BASE_URL$path")"
  [[ "$code" == "200" ]] || fail "$label expected final 200, got $code"
  pass "$label"
}

assert_redirect_route() {
  local path="$1"
  local label="$2"
  local expected_location="$3"
  local headers
  headers="$(mktemp)"
  local code
  code="$(curl -sS -b "$COOKIE_JAR" -c "$COOKIE_JAR" -D "$headers" -o /dev/null -w '%{http_code}' "$BASE_URL$path")"
  [[ "$code" == "302" ]] || { rm -f "$headers"; fail "$label expected 302, got $code"; }
  grep -q "[Ll]ocation: .*${expected_location}" "$headers" || { cat "$headers" >&2; rm -f "$headers"; fail "$label expected redirect to $expected_location"; }
  rm -f "$headers"
  pass "$label"
}

navigation_checks() {
  login_admin

  assert_auth_route "/dashboard" "route_dashboard"
  assert_auth_route "/portal/mahasiswa" "route_portal_mahasiswa"
  assert_auth_route "/portal/klinik" "route_portal_klinik"
  assert_auth_route "/portal/bank" "route_portal_bank"
  assert_auth_route "/portal/ppl" "route_portal_ppl"

  assert_auth_route "/admin/dashboard" "route_admin_dashboard"
  assert_auth_route "/admin/mahasiswa/dashboard" "route_admin_mahasiswa_dashboard"
  assert_auth_route "/admin/klinik/dashboard" "route_admin_klinik_dashboard"
  assert_auth_route "/admin/bank/dashboard" "route_admin_bank_dashboard"
  assert_auth_route "/admin/ppl/dashboard" "route_admin_ppl_dashboard"

  assert_redirect_route "/portal" "legacy_portal_redirect" "/portal/ppl"
}

cleanup_dummy() {
  docker compose -f "$PROJECT_DIR/docker-compose.yml" exec -T bank-db sh -lc \
    "mysql -uroot -p\"\$MYSQL_ROOT_PASSWORD\" \"\$MYSQL_DATABASE\" -e \"DELETE FROM pembayaran WHERE nim='${NIM}' OR kode_tagihan='${KODE_TAGIHAN}';\"" >/dev/null 2>&1 || true
  pass "cleanup_dummy"
}

main() {
  case "${1:-all}" in
    negative) negative_checks ;;
    navigation) navigation_checks ;;
    payment) trap 'cleanup_dummy; cleanup_files' EXIT; create_and_confirm_payment ;;
    all) trap 'cleanup_dummy; cleanup_files' EXIT; negative_checks; navigation_checks; create_and_confirm_payment ;;
    cleanup) cleanup_dummy ;;
    *) fail "Usage: $0 [all|negative|navigation|payment|cleanup]" ;;
  esac
}

main "$@"
