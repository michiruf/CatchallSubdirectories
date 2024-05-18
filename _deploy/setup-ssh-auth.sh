#!/usr/bin/env bash
# shellcheck shell=bash

# Configure SSH https://stackoverflow.com/a/49018871 (but slightly changed)
sed -i 's/#\?\(PermitRootLogin\)\s*.*$/\1 no/' /etc/ssh/sshd_config
sed -i 's/#\?\(PermitEmptyPasswords\)\s*.*$/\1 no/' /etc/ssh/sshd_config
if [ "$USE_PUBLIC_KEY" = true ]; then
    sed -i 's/#\?\(PubkeyAuthentication\)\s*.*$/\1 yes/' /etc/ssh/sshd_config
    sed -i 's/#\?\(PasswordAuthentication\)\s*.*$/\1 no/' /etc/ssh/sshd_config
else
    sed -i 's/#\?\(PubkeyAuthentication\)\s*.*$/\1 no/' /etc/ssh/sshd_config
    sed -i 's/#\?\(PasswordAuthentication\)\s*.*$/\1 yes/' /etc/ssh/sshd_config
fi

# Set valid/invalid password for user and unlock it afterwards
if [ "$USE_PUBLIC_KEY" = true ]; then
    pass="$(openssl rand -hex 100)"
    usermod -p "$pass" "$APPLICATION_USER"
else
    # See https://stackoverflow.com/a/75669312
    echo -e "$SSH_PASSWORD\n$SSH_PASSWORD" | passwd "$APPLICATION_USER"
fi
usermod -U "$APPLICATION_USER"

# Configure authorized keys for application user
dir=$( getent passwd "$APPLICATION_USER" | cut -d: -f6 )
if [ ! -d "$dir/.ssh" ]; then
    mkdir "$dir/.ssh"
    echo "$SSH_PUBLIC_KEY" > "$dir/.ssh/authorized_keys"
    chown -R "$APPLICATION_USER:$APPLICATION_GROUP" "$dir"
    chmod go-w "$dir"
    chmod 700 "$dir/.ssh"
    chmod 600 "$dir/.ssh/authorized_keys"
    echo "SSH auth set up successfully"
fi
