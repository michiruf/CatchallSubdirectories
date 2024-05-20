#!/usr/bin/env sh
set -e

dir="/app/current"

if [ ! -d "$dir" ]; then
    echo "cannot start horizon yet: deployment not yet complete (there is no /app/current directory)"
    exit 1
fi

if [ ! -f "$dir/artisan" ]; then
    echo "cannot start horizon yet: deployment not yet complete (there is no artisan file)"
    exit 1
fi

php "$dir/artisan" horizon

# Exit with horizons exit code
exit $?
