#!/usr/bin/env sh
# shellcheck shell=sh
set -e
. /opt/docker/etc/print.sh

artisan='/opt/docker/bin/service.d/laravel-artisan.sh'
perform_deploy=false

# Check if required GIT_URL exists
if [ -z "$GIT_URL" ]; then
    p 'GIT_URL is not set in the environment variables' 'red'
    exit 1
fi

cd "$APPLICATION_PATH"

# Clone if there is no .git directory yet
if [ ! -d ".git" ]; then
    p '=> initial project setup' 'purple'

    p "> clone repository with branch $BRANCH" 'cyan'
    # Flag the directory to be usable by both, root and the application user
    git config --global --add safe.directory "$APPLICATION_PATH"
    git clone -b "$BRANCH" "$GIT_URL" .
    echo 'Done'

    p '> adjust rights' 'cyan'
    chown -R "$APPLICATION_UID":"$APPLICATION_GID" .

    p '> install composer dependencies' 'cyan'
    composer install --no-interaction --no-progress -q # TODO remove -q

    perform_deploy=true
fi

# Check if there is no new stuff and then exit
# See https://stackoverflow.com/questions/3258243/check-if-pull-needed-in-git
git fetch
if [ "$(git rev-parse HEAD)" != "$(git rev-parse @\{u\})" ]; then
    p '=> detected changes in the git revision' 'purple'

    perform_deploy=true
fi

if [ "$perform_deploy" = true ] ; then
    p '=> performing deploy now' 'purple'

    old_ifs=$IFS
    IFS=$DEPLOY_COMMAND_SEPARATOR
    for command in $DEPLOY_COMMANDS; do
        # Trim command
        command=$(echo "$command" | xargs)

        # Inform the user
        p "> $command" 'cyan'

        # Switch what to do with the command
        case $command in
            -*)
                p "skipping command since it starts with '-'" 'yellow'
                ;;
            git:update)
                current_branch=$(git symbolic-ref --short HEAD)
                git reset --hard "origin/$current_branch"
                ;;
            env:update)
                /opt/docker/bin/service.d/laravel-env.sh apply
                ;;
            composer:*)
                eval "composer ${command#composer:} --ansi"
                ;;
            artisan:*)
                # artisan wrapper script already has ansi
                eval "$artisan ${command#artisan:}"
                ;;
            npm:*)
                p "received npm command: ${command#npm:}, but npm commands are not yet implemented, neither is npm installed" 'red'
                exit 1
                ;;
            *)
                eval "$command" || p "exited with $?" 'red'
                #[ $? != 0 ] && exit $?
                ;;
        esac
    done
    IFS=$old_ifs

    p '=> deploy completed' 'purple'
fi

exit 0
