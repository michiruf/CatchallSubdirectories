#!/usr/bin/env sh
# shellcheck shell=bash
set -e
. /opt/docker/etc/print.sh

/opt/docker/bin/service.d/laravel-env.sh prepare
