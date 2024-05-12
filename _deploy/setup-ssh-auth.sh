#!/usr/bin/env bash
# shellcheck shell=bash

dir=$( getent passwd "$APPLICATION_USER" | cut -d: -f6 )
mkdir "$dir/.ssh"
echo "$SSH_PUBLIC_KEY" > "$dir/.ssh/authorized_keys"
chown -R "$APPLICATION_USER:$APPLICATION_GROUP" "$dir"
echo "SSH auth set up successfully"
