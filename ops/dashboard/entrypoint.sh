#!/bin/bash
set -e

# start sshd for app service access
/usr/sbin/sshd

# ensure storage folder is setup
[ ! -d "/var/www/html/storage/framework/sessions" ] \
    && mkdir -p "/var/www/html/storage/framework/sessions" \
    && chown www-data:www-data "/var/www/html/storage/framework/sessions"
[ ! -d "/var/www/html/storage/framework/views" ] \
    && mkdir -p "/var/www/html/storage/framework/views" \
    && chown www-data:www-data "/var/www/html/storage/framework/views"
[ ! -d "/var/www/html/storage/framework/cache" ] \
    && mkdir -p "/var/www/html/storage/framework/cache" \
    && chown www-data:www-data "/var/www/html/storage/framework/cache"

# run artisan setup
php artisan clear-compiled
php artisan config:cache
php artisan route:trans:cache
php artisan view:cache
php artisan migrate --force

apache2-foreground
