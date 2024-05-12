FROM webdevops/php-nginx:8.3-alpine

## Env for app context and php
#ENV APPLICATION_USER=application \
#    APPLICATION_GROUP=application \
#    APPLICATION_PATH=/app \
#    APPLICATION_UID=1000 \
#    APPLICATION_GID=1000
#
## Install app context https://github.com/webdevops/Dockerfile/blob/master/docker/base-app/alpine/Dockerfile
## NOTE This might be not needed at all, since the php image also incudes the service ssh definitins:
##      https://github.com/webdevops/Dockerfile/blob/master/docker/php-official/8.3-alpine/conf/provision/service.d/ssh.sh
#RUN set -x \
#    && apk-install shadow \
#    && apk-install \
#        # Install common tools
#        zip \
#        unzip \
#        bzip2 \
#        drill \
#        ldns \
#        openssh-client \
#        rsync \
#        patch \
#        git \
#    && docker-run-bootstrap \
#    && docker-image-cleanup

# Install ssh https://github.com/webdevops/Dockerfile/blob/master/docker/ssh/latest/Dockerfile
RUN set -x \
    && docker-service enable ssh \
    && docker-run-bootstrap \
    && docker-image-cleanup

# Add the script to set up ssh auth
COPY setup-ssh-auth.sh /opt/docker/provision/entrypoint.d/10-setup-ssh-auth.sh
RUN chmod +x /opt/docker/provision/entrypoint.d/10-setup-ssh-auth.sh

EXPOSE 22
