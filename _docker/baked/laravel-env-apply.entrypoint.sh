#!/usr/bin/env sh
# shellcheck shell=sh
set -e
. /opt/docker/etc/print.sh

/opt/docker/bin/service.d/laravel-env.sh
