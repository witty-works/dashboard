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
[ ! -d "/var/www/html/storage/app/public/profile-photos" ] \
    && mkdir -p "/var/www/html/storage/app/public/profile-photos" \
    && chown www-data:www-data "/var/www/html/storage/app/public/profile-photos"

# # if env vars for basic auth are set lets create the htaccess file
# # this could be done more elegantely inside app service with azure ad integration
# # but basic auth is clearer easier for scripted usage etc

# run artisan setup
cd /var/www/html
php artisan clear-compiled
php artisan config:cache
php artisan route:trans:cache
php artisan view:cache
php artisan migrate --force
php artisan storage:link

/usr/local/bin/apache2-foreground
