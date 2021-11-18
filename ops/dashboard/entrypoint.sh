#!/bin/bash
set -e

# start sshd for app service access
/usr/sbin/sshd

php artisan clear-compiled
php artisan config:cache
php artisan route:trans:cache
php artisan view:cache
php artisan migrate --force

apache2-foreground
