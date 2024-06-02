## syntax = devthefuture/dockerfile-x:v1.4.1
## See https://github.com/devthefuture-org/dockerfile-x
## See issue https://github.com/devthefuture-org/dockerfile-x/issues/6
#
#FROM ../Dockerfile

FROM base

# Install ssh https://github.com/webdevops/Dockerfile/blob/master/docker/ssh/latest/Dockerfile
RUN set -x \
    && docker-service enable ssh \
    && docker-run-bootstrap \
    && docker-image-cleanup

# Add script to set up ssh auth (script must not be executable)
COPY --chmod=644 setup-ssh-auth.sh /opt/docker/provision/entrypoint.d/1-setup-ssh-auth.sh

EXPOSE 22
