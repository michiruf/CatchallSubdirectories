## syntax = devthefuture/dockerfile-x:v1.4.1
## See https://github.com/devthefuture-org/dockerfile-x
## See issue https://github.com/devthefuture-org/dockerfile-x/issues/6
## Also see/update the other docker files in this repo
#
#FROM ../Dockerfile

FROM base

# Add project files using a manually defined context of the repository root: src_root
COPY --from=src_root --chown=$APPLICATION_UID:$APPLICATION_GID . /app
# In case the image was built locally, there might be an .env that was copied over and there might
# be some secrets, when developing locally
# TODO Maybe us git to clean? -> no, because things could be persistent!!! -> yes, because we are in the image!
RUN set -e && \
    rm -f /app/.env && \
    rm -Rf /app/vendor
#RUN git reset --hard origin/$branch && git submodule update --init --recursive

# TODO Add composer install in here?

COPY --chmod=644 laravel-deploy.entrypoint.sh /opt/docker/provision/entrypoint.d/11-deploy-laravel.sh
