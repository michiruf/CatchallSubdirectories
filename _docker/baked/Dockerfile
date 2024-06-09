## syntax = devthefuture/dockerfile-x:v1.4.1
## See https://github.com/devthefuture-org/dockerfile-x
## See issue https://github.com/devthefuture-org/dockerfile-x/issues/6
## Also see/update the other docker files in this repo
#
#FROM ../Dockerfile

FROM base

# Add project files using a manually defined context of the repository root: src_root
COPY --from=src_root --chown=$APPLICATION_UID:$APPLICATION_GID . /app-src
# In case the image was built locally, there might be an .env that was copied over and there might
# be some secrets, when developing locally
RUN set -x && \
    cd /app-src && \
    git config --global --add safe.directory /app-src && \
    git clean -f -d -x --dry-run && \
    composer install --no-progress

COPY --chmod=644 laravel-deploy.entrypoint.sh /opt/docker/provision/entrypoint.d/11-laravel-deploy.sh

# Set the default deploy commands and their separator
ENV DEPLOY_COMMAND_SEPARATOR=;
ENV DEPLOY_COMMANDS \
env:update ; \
artisan:storage:link ; \
artisan:optimize ; \
artisan:migrate --force
