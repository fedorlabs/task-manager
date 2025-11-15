#!/bin/bash
set -e

echo "Installing dependencies..."
composer install --no-interaction --prefer-dist

echo "Generating JWT keys..."
mkdir -p config/jwt
if [ ! -f config/jwt/private.pem ]; then
    openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:${JWT_PASSPHRASE:-taskmanager}
    openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:${JWT_PASSPHRASE:-taskmanager}
    chmod 644 config/jwt/private.pem config/jwt/public.pem
fi

echo "Waiting for database..."
until php -r "try { new PDO('pgsql:host=db;port=5432;dbname=postgres', 'postgres', 'psql'); echo 'ok'; } catch(Exception \$e) { exit(1); }" 2>/dev/null; do
    sleep 1
done

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration 2>/dev/null || true

echo "Creating schema..."
php bin/console doctrine:schema:update --force --no-interaction

echo "Loading fixtures..."
php bin/console doctrine:fixtures:load --no-interaction --append

echo "Clearing cache..."
php bin/console cache:clear

echo "Starting server..."
exec php -S 0.0.0.0:8000 -t public
