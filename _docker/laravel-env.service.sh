#!/usr/bin/env sh
# shellcheck shell=sh
# shellcheck disable=SC1090 # disable 'cannot follow non constant source' because it just works
set -e
. /opt/docker/etc/print.sh

# This common file ensures that even in a user change env values are available if
# the user with the env from docker first run this script with 'prepare' option once
common_env_file='/common.env'
if [ -f "$common_env_file" ]; then
    . "$common_env_file"
fi

prefix="LARAVEL_" # Note this could easily get enhanced for NPM_ as well
user_laravel_env_file="/home/$APPLICATION_USER/laravel.env"
laravel_env_file=${APPLICATION_ENV_FILE:-"$APPLICATION_PATH/.env"}
laravel_example_env_file=${APPLICATION_ENV_EXAMPLE_FILE:-"$APPLICATION_PATH/.env.example"}

case $1 in
    prepare)
        p "> preparing common environment in '$common_env_file' with essential env values from docker" 'cyan'
        printenv | grep "^APPLICATION_" > "$common_env_file"
        # This export is necessary only for the ssh session to pick environment variables with '.' in this script up
        # and it cannot get reproduced entering via docker exec in the container
        echo "export $(printenv | grep "^APPLICATION_" | cut -d '=' -f 1 | awk '{print $0" "}' | tr -d '\n')" >> "$common_env_file"
        chown "$APPLICATION_USER:$APPLICATION_GROUP" "$common_env_file"

        p "> preparing environment in '$user_laravel_env_file' with env values for laravel" 'cyan'
        printenv | grep "^${prefix}" > "$user_laravel_env_file"
        # This export is necessary only for the ssh session to pick environment variables with '.' in this script up
        # and it cannot get reproduced entering via docker exec in the container
        echo "export $(printenv | grep "^${prefix}" | cut -d '=' -f 1 | awk '{print $0" "}' | tr -d '\n')" >> "$user_laravel_env_file"
        chown "$APPLICATION_USER:$APPLICATION_GROUP" "$user_laravel_env_file"

        ;;

    apply)
        p "> generating new '$laravel_env_file' from '$laravel_example_env_file' with values from '$user_laravel_env_file'" 'cyan'
        if [ ! -f "$laravel_example_env_file" ]; then
            p "there is no '$laravel_example_env_file' to copy over, exiting" 'red'
            exit 1
        fi

        cp "$laravel_example_env_file" "$laravel_env_file"

        # Load the environment variables from the user env file
        . "$user_laravel_env_file"

        for line in $(printenv | grep "^${prefix}"); do
            original_var_name=$(echo "$line" | cut -d '=' -f 1)
            var_value=$(printenv "$original_var_name")
            var_name="${original_var_name#"$prefix"}"

            p "sub: $var_name with $var_name=$var_value"

            sed -i "s|\(# \?\)\?$var_name=.*|$var_name=$var_value|g" "$laravel_env_file"
        done
        ;;

    *)
        p 'usage: ./laravel-env.sh prepare|apply' 'red'
        ;;
esac

exit 0
