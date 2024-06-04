#!/usr/bin/env sh
# shellcheck shell=sh
set -e
. /opt/docker/etc/print.sh

prefix="LARAVEL_" # Note this could easily get enhanced for NPM_ as well
user_env_file="/home/$APPLICATION_USER/laravel.env"
laravel_env_file="$APPLICATION_PATH/.env"
laravel_example_env_file="$APPLICATION_PATH/.env.example"

case $1 in
    prepare)
        p "> preparing environment in '$user_env_file' with env values prefixed with '$prefix'" 'cyan'
        rm -f "$user_env_file"
        printenv | grep "^${prefix}" > "$user_env_file"
        chown "$APPLICATION_USER:$APPLICATION_GROUP" "$user_env_file"
        ;;

    apply)
        p "> updating '$laravel_env_file' with values from '$user_env_file'" 'cyan'
        if [ ! -f "$laravel_env_file" ]; then
            p "there is no '$laravel_env_file', copying from '$laravel_example_env_file'" 'yellow'
            if [ ! -f "$laravel_example_env_file" ]; then
                p "there is no '$laravel_example_env_file' to copy over, exiting" 'red'
                exit 1
            fi

            cp "$laravel_example_env_file" "$laravel_env_file"
        fi

        # Load the environment variables from the file
        # shellcheck disable=SC1090 # disable 'cannot follow non constant source' because it just works
        . "$user_env_file"

        for line in $(printenv | grep "^${prefix}"); do
            original_var_name=$(echo "$line" | cut -d '=' -f 1)
            var_value=$(printenv "$original_var_name")
            var_name="${original_var_name#"$prefix"}"

            sed -i "s|\(# \?\)\?$var_name=.*|$var_name=$var_value|g" "$laravel_env_file"
        done
        ;;

    *)
        p 'Usage: ./laravel-env.sh prepare|apply' 'red'
        ;;
esac




