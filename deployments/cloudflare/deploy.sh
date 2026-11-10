#!/usr/bin/env bash
# Deploy the Talent Portal to Cloudflare Containers.
#
# Steps: check Docker -> load production env -> build image (linux/amd64)
#        -> run migrations against the configured database -> push secrets -> wrangler deploy
#
# Usage:
#   deployments/cloudflare/deploy.sh                # uses deployments/cloudflare/.env.production
#   ENV_FILE=path/to/file deployments/cloudflare/deploy.sh
#   SKIP_MIGRATE=1 deployments/cloudflare/deploy.sh  # skip migrations (not recommended)

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
CF_DIR="$ROOT/deployments/cloudflare"
ENV_FILE="${ENV_FILE:-$CF_DIR/.env.production}"
IMAGE="talent-portal-cloudflare:$(date +%Y%m%d%H%M%S)"
WRANGLER="npx --yes wrangler@4"

SECRET_KEYS=(
  APP_NAME APP_KEY APP_URL APP_TIMEZONE
  DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD DB_SSLMODE
  TALENT_API_KEY TALENT_LOCAL_AUTH_ENABLED TALENT_PRIVACY_POLICY_URL
  TALENT_PRIVACY_POLICY_VERSION TALENT_CONSENT_POLICY_VERSION TALENT_RESUME_DISK
  BUCKET_API_URL BUCKET_API_KEY BUCKET_NAME
  GOOGLE_CLIENT_ID GOOGLE_CLIENT_SECRET GOOGLE_REDIRECT_URI
  MAIL_MAILER LOG_LEVEL
)

log() { printf '\n==> %s\n' "$*"; }
fail() { printf 'Error: %s\n' "$*" >&2; exit 1; }

command -v docker >/dev/null || fail "Docker is not installed."
docker info >/dev/null 2>&1 || fail "Docker is not running. Start Docker Desktop and try again."
command -v npx >/dev/null || fail "Node.js (npx) is required for Wrangler."

[ -f "$ENV_FILE" ] || fail "Missing $ENV_FILE. Copy .env.production.example and fill it in."

set -a
# shellcheck disable=SC1090
. "$ENV_FILE"
set +a

for required in APP_KEY APP_URL DB_HOST DB_DATABASE DB_USERNAME DB_PASSWORD TALENT_API_KEY; do
  [ -n "${!required:-}" ] || fail "$required is empty in $ENV_FILE."
done
case "${DB_HOST}" in
  localhost|127.0.0.1|db) fail "DB_HOST=${DB_HOST} is not reachable from Cloudflare. Use an external database such as Neon." ;;
esac
[ "${DB_SSLMODE:-prefer}" = "require" ] || printf 'Warning: DB_SSLMODE is %s. Neon requires DB_SSLMODE=require.\n' "${DB_SSLMODE:-prefer}"

log "Building image $IMAGE (linux/amd64)"
docker build --platform linux/amd64 -f "$ROOT/Dockerfile.cloudflare" -t "$IMAGE" "$ROOT"

if [ "${SKIP_MIGRATE:-0}" != "1" ]; then
  log "Running migrations on $DB_HOST"
  docker run --rm --platform linux/amd64 --env-file "$ENV_FILE" "$IMAGE" php artisan migrate --force
fi

log "Pushing secrets to Cloudflare"
cd "$CF_DIR"
for key in "${SECRET_KEYS[@]}"; do
  value="${!key:-}"
  [ -n "$value" ] || continue
  printf '%s' "$value" | $WRANGLER secret put "$key" >/dev/null
  printf '  set %s\n' "$key"
done

log "Deploying Worker and Container"
$WRANGLER deploy

log "Done. Check the health endpoint:"
printf '  %s/up\n' "${APP_URL%/}"
