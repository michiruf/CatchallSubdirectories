## syntax = devthefuture/dockerfile-x:v1.4.1
## See https://github.com/devthefuture-org/dockerfile-x
## See issue https://github.com/devthefuture-org/dockerfile-x/issues/6
## Also see/update the other docker files in this repo
#
#FROM ../Dockerfile

FROM base

# Install ssh https://github.com/webdevops/Dockerfile/blob/master/docker/ssh/latest/Dockerfile
RUN set -x \
    && docker-service enable ssh \
    && docker-run-bootstrap \
    && docker-image-cleanup

# Add script to set up ssh auth
COPY --chmod=644 ssh-auth.entrypoint.sh /opt/docker/provision/entrypoint.d/11-setup-ssh-auth.sh

# Patch the paths to be away of deployers 'current' directory
# Explicitly set the env file paths
ENV APPLICATION_PATH=/app/current \
    WEB_DOCUMENT_ROOT=/app/current/public \
    APPLICATION_ENV_FILE=/app/shared/.env \
    APPLICATION_ENV_EXAMPLE_FILE=/app/release/.env.example

EXPOSE 22
