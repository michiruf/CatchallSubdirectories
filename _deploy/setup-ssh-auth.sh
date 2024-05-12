#!/usr/bin/env bash
# shellcheck shell=bash

# Configure SSH https://stackoverflow.com/a/49018871 (but slightly changed)
sed -i 's/#\?\(PermitRootLogin\)\s*.*$/\1 no/' /etc/ssh/sshd_config
sed -i 's/#\?\(PubkeyAuthentication\)\s*.*$/\1 yes/' /etc/ssh/sshd_config
sed -i 's/#\?\(PermitEmptyPasswords\)\s*.*$/\1 no/' /etc/ssh/sshd_config
sed -i 's/#\?\(PasswordAuthentication\)\s*.*$/\1 no/' /etc/ssh/sshd_config

# Unlock the user
pass="$(openssl rand -hex 100)"
usermod -p "$pass" "$APPLICATION_USER"
usermod -U "$APPLICATION_USER"

# Configure authorized keys for application user
dir=$( getent passwd "$APPLICATION_USER" | cut -d: -f6 )
mkdir "$dir/.ssh"
echo "$SSH_PUBLIC_KEY" > "$dir/.ssh/authorized_keys"
chown -R "$APPLICATION_USER:$APPLICATION_GROUP" "$dir"
chmod go-w "$dir"
chmod 700 "$dir/.ssh"
chmod 600 "$dir/.ssh/authorized_keys"
echo "SSH auth set up successfully"
