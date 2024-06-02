#!/usr/bin/env sh
# shellcheck shell=sh
set -e

if [ ! -d "$LARAVEL_APPLICATION_PATH" ]; then
    echo "cannot execute artisan command \"$*\" yet: deployment not yet complete (there is no $LARAVEL_APPLICATION_PATH directory)"
    exit 1
fi

if [ ! -f "$LARAVEL_APPLICATION_PATH/artisan" ]; then
    echo "cannot execute artisan command \"$*\" yet: deployment not yet complete (there is no artisan file in $LARAVEL_APPLICATION_PATH)"
    exit 1
fi

# Some commands require to be in the artisan directory already, so we first need to switch directories
cd "$LARAVEL_APPLICATION_PATH"
php artisan "$@"

# Exit with artisans exit code
exit $?
