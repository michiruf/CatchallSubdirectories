#!/usr/bin/env sh
# shellcheck shell=sh
set -e
. /opt/docker/etc/print.sh

cd "$APPLICATION_PATH"

/opt/docker/bin/service.d/laravel-env.sh

p '=> performing deploy now' 'purple'

composer install --no-progress

php artisan key:generate
php artisan storage:link
php artisan optimize
php artisan migrate --force

p '=> deploy completed' 'purple'
