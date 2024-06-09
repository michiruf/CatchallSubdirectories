#!/usr/bin/env sh
# shellcheck shell=sh
set -e
. /opt/docker/etc/print.sh

# Load environment file
# 'set -a' ensures they are treated as exported
# See https://superuser.com/a/1240860
set -a; . /etc/environment; set +a

provision="/opt/docker/bin/service.d/provision.sh"
cd "$APPLICATION_PATH"

# Move project over if app directory empty and perform initial setups
if [ -z "$(ls -A "$APPLICATION_PATH")" ]; then
    p "=> initial project setup, because '$APPLICATION_PATH' was empty" 'purple'

    p "> make project source available in '$APPLICATION_PATH'" 'cyan'
    find /app-src -maxdepth 1 -mindepth 1 \( ! -name '.' ! -name '..' \) -exec mv {} "$APPLICATION_PATH" \;

    p '> generate app key' 'cyan'
    $provision artisan:key:generate --force
fi

p '=> performing deploy now' 'purple'

IFS=$DEPLOY_COMMAND_SEPARATOR; for command in $DEPLOY_COMMANDS; do
    p "> $command" 'cyan'
    $provision "$command"
done

p '=> deploy completed' 'purple'
