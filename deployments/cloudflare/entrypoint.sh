#!/bin/sh
set -eu

cd /var/www/html

echo "[entrypoint] starting as $(id -un), APP_ENV=${APP_ENV:-unset}"

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

echo "[entrypoint] caching config, routes, views"
php artisan config:cache 2>&1 || echo "[entrypoint] config:cache failed (continuing)"
php artisan route:cache 2>&1 || echo "[entrypoint] route:cache failed (continuing)"
php artisan view:cache 2>&1 || echo "[entrypoint] view:cache failed (continuing)"

chown -R www-data:www-data storage bootstrap/cache

echo "[entrypoint] starting: $*"

# Keep this script as PID 1 and run the server as a child. Cloudflare's VM
# does not start the server reliably when it replaces PID 1 through exec.
# The server writes to a file instead of the container's stdout/stderr, because
# Apache's logs (/proc/self/fd/*) cannot be reopened there. tail streams the file.
touch /tmp/server.log
tail -F /tmp/server.log 2>/dev/null &
"$@" >> /tmp/server.log 2>&1 &
child=$!
trap 'kill -TERM "$child" 2>/dev/null' TERM INT
status=0
wait "$child" || status=$?
if [ "$status" -gt 128 ]; then
    wait "$child" 2>/dev/null || status=$?
fi
echo "[entrypoint] server exited with status $status"
exit "$status"
