#!/usr/bin/env sh
# shellcheck shell=sh
set -e
. /opt/docker/etc/print.sh

p "> preparing general docker environment" 'cyan'
printenv | grep "^APPLICATION_" >> /etc/environment

p "> preparing docker environment for laravel (env with prefix 'LARAVEL_')" 'cyan'
printenv | grep "^LARAVEL_" >> /etc/environment
