FROM webdevops/php-nginx:8.3-alpine

# Install ssh https://github.com/webdevops/Dockerfile/blob/master/docker/ssh/latest/Dockerfile
RUN set -x \
    && docker-service enable ssh \
    && docker-run-bootstrap \
    && docker-image-cleanup

# Add script to set up ssh auth (script must not be executable)
COPY --chmod=644 setup-ssh-auth.sh /opt/docker/provision/entrypoint.d/1-setup-ssh-auth.sh

# Add laravel horizon supervisor script and config
COPY --chmod=755 laravel-horizon.service.sh /opt/docker/bin/service.d/laravel-horizon.sh
COPY --chmod=644 laravel-horizon.supervisor.conf /opt/docker/etc/supervisor.d/laravel-horizon.conf

# Add excimer php extension for profiling with sentry.io
# This needs autoconf which is installed like here: https://github.com/webdevops/Dockerfile/blob/master/docker/php-dev/8.3-alpine/Dockerfile#L17
RUN set -x \
  && apk-install \
      linux-headers \
      make \
      autoconf \
      g++ \
  && pecl install excimer \
  && apk del -f --purge \
      autoconf \
      linux-headers \
      g++ \
      make \
  && docker-php-ext-enable excimer \
  && docker-run-bootstrap \
  && docker-image-cleanup

EXPOSE 22
