#!/usr/bin/env sh
# shellcheck shell=sh
set -e

dir="/app/current"

if [ ! -d "$dir" ]; then
    echo "cannot execute artisan command \"$*\" yet: deployment not yet complete (there is no $dir directory)"
    exit 1
fi

if [ ! -f "$dir/artisan" ]; then
    echo "cannot execute artisan command \"$*\" yet: deployment not yet complete (there is no artisan file)"
    exit 1
fi

# Some commands require to be in the artisan directory already, so we first need to switch directories
cd "$dir"
php artisan "$@"

# Exit with artisans exit code
exit $?
