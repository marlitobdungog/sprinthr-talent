#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ ! -f vendor/autoload.php ] || [ composer.lock -nt vendor/.install-stamp ] || [ composer.json -nt vendor/.install-stamp ]; then
    composer install --prefer-dist --no-interaction
    touch vendor/.install-stamp
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

exec "$@"
