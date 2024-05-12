FROM webdevops/php-nginx:8.3-alpine

# Install ssh https://github.com/webdevops/Dockerfile/blob/master/docker/ssh/latest/Dockerfile
RUN set -x \
    && docker-service enable ssh \
    && docker-run-bootstrap \
    && docker-image-cleanup

# Add the script to set up ssh auth
COPY setup-ssh-auth.sh /opt/docker/provision/entrypoint.d/1-setup-ssh-auth.sh
RUN chmod +x /opt/docker/provision/entrypoint.d/1-setup-ssh-auth.sh

EXPOSE 22
