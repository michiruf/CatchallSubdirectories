#!/usr/bin/env sh
# shellcheck shell=sh
set -e
. /opt/docker/etc/print.sh

if [ ! -d "$APPLICATION_PATH" ]; then
    p "cannot execute artisan command \"$*\" yet: deployment not yet complete (there is no $APPLICATION_PATH directory)" 'red'
    exit 1
fi

if [ ! -f "$APPLICATION_PATH/artisan" ]; then
    p "cannot execute artisan command \"$*\" yet: deployment not yet complete (there is no artisan file in $APPLICATION_PATH)" 'red'
    exit 1
fi

if [ ! -d "$APPLICATION_PATH/vendor" ]; then
    p "cannot execute artisan command \"$*\" yet: composer did not install dependencies yet (there is no vendor directory in $APPLICATION_PATH)" 'red'
    exit 1
fi

# Some commands require to be in the artisan directory already, so we first need to switch directories
cd "$APPLICATION_PATH"
php artisan "$@" --ansi

# Exit with artisans exit code
exit $?
