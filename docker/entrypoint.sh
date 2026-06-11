#!/bin/sh
set -e

cd /var/www/html

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data \
    storage/app/public storage/logs bootstrap/cache /run/nginx

if [ -f "composer.json" ] && [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if [ ! -f ".env" ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

env_replace() {
    local key="$1" value="$2"
    if grep -q "^${key}=" .env; then
        sed -i "s|^${key}=.*|${key}=${value}|" .env
    else
        echo "${key}=${value}" >> .env
    fi
}

env_replace DB_CONNECTION mysql
env_replace DB_HOST mysql
env_replace DB_PORT 3306
env_replace DB_DATABASE "${DB_DATABASE:-klikabsen}"
env_replace DB_USERNAME "${DB_USERNAME:-klikabsen}"
env_replace DB_PASSWORD "${DB_PASSWORD:-secret}"

if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

if grep -q "APP_ENV=local" .env; then
    php artisan config:clear 2>/dev/null || true
    php artisan route:clear 2>/dev/null || true
    php artisan view:clear 2>/dev/null || true
else
    php artisan config:cache 2>/dev/null || true
    php artisan route:cache 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true
fi

echo "Running migrations..."
until php artisan migrate --force 2>/dev/null; do
    echo "Waiting for database..."
    sleep 2
done

php artisan storage:link --force 2>/dev/null || true

chown -R appuser:appuser storage bootstrap/cache /run/nginx 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "KlikAbsen ready."
exec "$@"
