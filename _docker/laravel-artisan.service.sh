#!/usr/bin/env sh
# shellcheck shell=sh
set -e
. /opt/docker/etc/print.sh

if [ ! -d "$LARAVEL_APPLICATION_PATH" ]; then
    p "cannot execute artisan command \"$*\" yet: deployment not yet complete (there is no $LARAVEL_APPLICATION_PATH directory)" 'red'
    exit 1
fi

if [ ! -f "$LARAVEL_APPLICATION_PATH/artisan" ]; then
    p "cannot execute artisan command \"$*\" yet: deployment not yet complete (there is no artisan file in $LARAVEL_APPLICATION_PATH)" 'red'
    exit 1
fi

if [ ! -d "$LARAVEL_APPLICATION_PATH/vendor" ]; then
    p "cannot execute artisan command \"$*\" yet: composer did not install dependencies yet (there is no vendor directory in $LARAVEL_APPLICATION_PATH)" 'red'
    exit 1
fi

# Some commands require to be in the artisan directory already, so we first need to switch directories
cd "$LARAVEL_APPLICATION_PATH"
php artisan "$@" --ansi

# Exit with artisans exit code
exit $?
