#!/bin/bash
# Runs on the test server from /var/www/vine after `vine-deploy` has pulled the branch.
set -euo pipefail
export PATH=$PATH:/snap/bin:/usr/local/bin
export COMPOSER_ALLOW_SUPERUSER=1

cd /var/www/vine

aws ssm get-parameter --name /vine-test/env --with-decryption \
  --query Parameter.Value --output text | cat > .env

composer install --no-dev --optimize-autoloader --no-interaction

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache .env
chmod -R ug+rwX storage bootstrap/cache

sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

systemctl reload php8.3-fpm
echo "Deployed $(git rev-parse --short HEAD)"
