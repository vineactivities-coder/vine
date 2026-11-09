#!/bin/bash
# Runs on the test server from /var/www/vine after `vine-deploy` has pulled the branch.
set -euo pipefail
export PATH=$PATH:/snap/bin:/usr/local/bin
export COMPOSER_ALLOW_SUPERUSER=1

cd /var/www/vine

# Verify the current host, app and database before replacing configuration.
php scripts/verify-test-environment.php
candidate_env=$(mktemp /var/www/vine/.env.test-candidate.XXXXXX)
trap 'rm -f "$candidate_env"' EXIT
chmod 600 "$candidate_env"
aws ssm get-parameter --name /vine-test/env --with-decryption \
  --query Parameter.Value --output text | cat > "$candidate_env"
php scripts/verify-test-environment.php "$candidate_env"
cat "$candidate_env" > .env

composer install --no-dev --optimize-autoloader --no-interaction

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache .env
chmod -R ug+rwX storage bootstrap/cache

sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

systemctl reload php8.3-fpm
printf '{"commit":"%s"}\n' "$(git rev-parse HEAD)" > public/deployment.json
echo "Deployed $(git rev-parse --short HEAD) to test.vine-activities.com"
