#!/usr/bin/env sh
# shellcheck shell=sh
set -e
. /opt/docker/etc/print.sh

# Trim command
command=$(echo "$@" | xargs)

# Switch what to do with the command
case $command in
    -*)
        p "skipping command since it starts with '-'" 'yellow'
        ;;
    git:update)
        current_branch=$(git symbolic-ref --short HEAD)
        git reset --hard "origin/$current_branch"
        ;;
    env:update)
        /opt/docker/bin/service.d/laravel-dotenv.sh
        ;;
    permissions:fix)
        chown -R "$APPLICATION_UID":"$APPLICATION_GID" .
        ;;
    composer:*)
        eval "composer ${command#composer:} --ansi"
        ;;
    artisan:*)
        # artisan wrapper script already has ansi
        eval "/opt/docker/bin/service.d/laravel-artisan.sh ${command#artisan:}"
        ;;
    npm:*)
        p "received npm command: ${command#npm:}, but npm commands are not yet implemented, neither is npm installed" 'red'
        exit 1
        ;;
    *)
        eval "$command" || p "exited with $?" 'red'
        #[ $? != 0 ] && exit $?
        ;;
esac
