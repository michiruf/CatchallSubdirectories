#!/usr/bin/env sh
# shellcheck shell=sh
set -e
. /opt/docker/etc/print.sh

perform_deploy=false

# Check if required GIT_URL exists
if [ -z "$GIT_URL" ]; then
    p 'GIT_URL is not set in the environment variables' 'red'
    exit 1
fi

cd "$APPLICATION_PATH"

# Clone if there is no .git directory yet
if [ ! -d ".git" ]; then
    p "=> initial project setup, because '$APPLICATION_PATH/.git' does not exist'" 'purple'

    p "> clone repository with branch '$BRANCH'" 'cyan'
    # Flag the directory to be usable by both, root and the application user
    git config --global --add safe.directory "$APPLICATION_PATH"
    git clone -b "$BRANCH" "$GIT_URL" .
    echo 'Done'

    IFS=$INITIAL_DEPLOY_COMMAND_SEPARATOR; for command in $INITIAL_DEPLOY_COMMANDS; do
        p "> $command" 'cyan'
        /opt/docker/bin/service.d/provision.sh "$command"
    done

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

    IFS=$DEPLOY_COMMAND_SEPARATOR; for command in $DEPLOY_COMMANDS; do
        p "> $command" 'cyan'
        /opt/docker/bin/service.d/provision.sh "$command"
    done

    p '> adjust rights' 'cyan'
    chown -R "$APPLICATION_UID":"$APPLICATION_GID" .

    p '=> deploy completed' 'purple'
fi

exit 0
