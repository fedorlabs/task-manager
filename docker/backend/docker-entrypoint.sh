#!/bin/bash
set -e

echo "Installing dependencies..."
if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

echo "Generating JWT keys..."
mkdir -p config/jwt
if [ ! -f config/jwt/private.pem ]; then
    openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:${JWT_PASSPHRASE:-taskmanager}
    openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:${JWT_PASSPHRASE:-taskmanager}
    chmod 644 config/jwt/private.pem config/jwt/public.pem
fi

DB_HOST="${POSTGRES_HOST:-db}"
DB_PORT="${POSTGRES_PORT:-5432}"
DB_NAME="${POSTGRES_DB:-postgres}"
DB_USER="${POSTGRES_USER:-postgres}"
DB_PASSWORD="${POSTGRES_PASSWORD:-psql}"

echo "Waiting for database..."
until php -r "try { new PDO('pgsql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_NAME}', '${DB_USER}', '${DB_PASSWORD}'); echo 'ok'; } catch(Exception \$e) { exit(1); }" 2>/dev/null; do
    sleep 1
done

if [ ! -f var/.initialized ]; then
    echo "Creating database (if missing)..."
    php bin/console doctrine:database:create --if-not-exists --no-interaction

    echo "Running migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>/dev/null || true

    if [ "${LOAD_FIXTURES:-false}" = "true" ]; then
        echo "Loading fixtures..."
        php bin/console doctrine:fixtures:load --no-interaction --append
    fi

    touch var/.initialized
fi

echo "Clearing cache..."
php bin/console cache:clear

echo "Starting PHP-FPM..."
exec php-fpm -F
