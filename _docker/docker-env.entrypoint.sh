#!/usr/bin/env sh
# shellcheck shell=sh
set -e
. /opt/docker/etc/print.sh

p "> preparing general docker environment" 'cyan'
printenv | grep "^APPLICATION_" >> /etc/environment

p "> preparing docker environment for laravel (env with prefix 'LARAVEL_')" 'cyan'
printenv | grep "^LARAVEL_" >> /etc/environment

## Try with ssh env # TODO Test or remove
## TODO This works, but is bad practice
#mkdir -p /home/application/.ssh/
#cp /etc/environment /home/application/.ssh/environment
#sed -i 's/#\?\(PermitUserEnvironment\)\s*.*$/\1 yes/' /etc/ssh/sshd_config
