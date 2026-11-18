#!/bin/sh
# Container start-up: prepare Laravel, then run Apache on Railway's $PORT
set -e
cd /var/www/html

PORT="${PORT:-8080}"

# Hosted MySQL (e.g. Aiven) needs an encrypted connection: paste the provider's
# CA certificate into the MYSQL_SSL_CA_CERT variable and it is used automatically.
if [ -n "$MYSQL_SSL_CA_CERT" ]; then
    printf '%s\n' "$MYSQL_SSL_CA_CERT" > /var/www/html/storage/mysql-ca.pem
    export MYSQL_ATTR_SSL_CA=/var/www/html/storage/mysql-ca.pem
fi
# Apache must load exactly one MPM (prefork, required by mod_php). Some hosts
# (e.g. Railway) end up with mpm_event enabled too -> "More than one MPM loaded".
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
[ -e /etc/apache2/mods-enabled/mpm_prefork.load ] || a2enmod mpm_prefork >/dev/null

sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Uploaded photos live on a Railway volume mounted at storage/app/public
mkdir -p storage/app/public/reports storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache

echo "Running database migrations..."
php artisan migrate --force
php artisan db:seed --force          # creates categories + staff accounts if missing (never resets passwords)
[ -L public/storage ] || php artisan storage:link
php artisan optimize

echo "Starting Apache on port ${PORT}"
exec apache2-foreground
