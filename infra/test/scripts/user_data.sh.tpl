#!/bin/bash
# One-time provisioning of the Vine test server (Ubuntu 24.04 arm64).
set -euxo pipefail
exec > >(tee /var/log/vine-provision.log) 2>&1
export DEBIAN_FRONTEND=noninteractive
# cloud-init runs without HOME; composer and git need it
export HOME=/root
export COMPOSER_HOME=/root/.composer

# 1G swap so composer / vite builds fit in 2GB RAM
fallocate -l 1G /swapfile && chmod 600 /swapfile && mkswap /swapfile && swapon /swapfile
echo '/swapfile none swap sw 0 0' >> /etc/fstab

apt-get update
apt-get install -y nginx mariadb-server git unzip curl nodejs npm certbot python3-certbot-nginx \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip \
  php8.3-bcmath php8.3-gd php8.3-intl
snap install aws-cli --classic

curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Database
mysql <<SQL
CREATE DATABASE IF NOT EXISTS vine CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'vine'@'localhost' IDENTIFIED BY '${db_password}';
GRANT ALL ON vine.* TO 'vine'@'localhost';
FLUSH PRIVILEGES;
SQL

# Uploads can be large
sed -i 's/^upload_max_filesize.*/upload_max_filesize = 20M/; s/^post_max_size.*/post_max_size = 25M/' /etc/php/8.3/fpm/php.ini

mkdir -p /var/www/vine/public /root/.ssh
chmod 700 /root/.ssh

# nginx vhost
cat > /etc/nginx/sites-available/vine <<'NGINX'
server {
    listen 80;
    server_name ${domain};
    root /var/www/vine/public;
    index index.php;
    client_max_body_size 25M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX
ln -sf /etc/nginx/sites-available/vine /etc/nginx/sites-enabled/vine
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
systemctl restart php8.3-fpm

# Wrapper invoked by GitHub Actions through SSM. It pulls the branch, then runs the
# deploy script that lives in the repo (infra/test/scripts/deploy.sh), so deploy logic
# is versioned with the code.
cat > /usr/local/bin/vine-deploy <<'WRAP'
#!/bin/bash
set -euo pipefail
export PATH=$PATH:/snap/bin:/usr/local/bin
export AWS_DEFAULT_REGION=${region}
export COMPOSER_ALLOW_SUPERUSER=1
export HOME=/root

aws ssm get-parameter --name /vine-test/deploy_key --with-decryption \
  --query Parameter.Value --output text | cat > /root/.ssh/vine_deploy_key
chmod 600 /root/.ssh/vine_deploy_key
export GIT_SSH_COMMAND="ssh -i /root/.ssh/vine_deploy_key -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new"
git config --global --add safe.directory /var/www/vine

cd /var/www/vine
if [ ! -d .git ]; then
  git init -q
  git remote add origin git@github.com:${repo}.git
fi
git fetch --depth 1 origin ${branch}
git reset --hard FETCH_HEAD
exec bash infra/test/scripts/deploy.sh
WRAP
chmod +x /usr/local/bin/vine-deploy

# Let's Encrypt: DNS may take a moment to propagate, so retry.
for i in $(seq 1 20); do
  if certbot --nginx -d ${domain} --non-interactive --agree-tos -m ${letsencrypt_email} --redirect; then
    break
  fi
  sleep 30
done
