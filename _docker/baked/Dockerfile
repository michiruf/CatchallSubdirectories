## syntax = devthefuture/dockerfile-x:v1.4.1
## See https://github.com/devthefuture-org/dockerfile-x
## See issue https://github.com/devthefuture-org/dockerfile-x/issues/6
## Also see/update the other docker files in this repo
#
#FROM ../Dockerfile

FROM base

# Add project files using a manually defined context of the repository root: src_root
COPY --from=src_root --chown=$APPLICATION_UID:$APPLICATION_GID . /app

COPY --chmod=644 laravel-env-apply.entrypoint.sh /opt/docker/provision/entrypoint.d/2-apply-laravel-env.sh
