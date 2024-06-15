FROM webdevops/php-nginx:8.3-alpine

# Patch fastcgi to use the realpath_root instead of the document_root, so we do not need to reload fpm
# See https://deployer.org/docs/7.x/avoid-php-fpm-reloading
RUN set -x \
    && sed -i 's|fastcgi_param.\+SCRIPT_FILENAME.\+\$request_filename\;|fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;|' /opt/docker/etc/nginx/vhost.common.d/10-php.conf

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

# Add configs and scripts
COPY --chmod=755 print.sh /opt/docker/etc/print.sh
COPY --chmod=755 provision.service.sh /opt/docker/bin/service.d/provision.sh
COPY --chmod=644 prepare-env.entrypoint.sh /opt/docker/provision/entrypoint.d/10-prepare-env.sh
COPY --chmod=755 laravel-dotenv.service.sh /opt/docker/bin/service.d/laravel-dotenv.sh
COPY --chmod=755 laravel-artisan.service.sh /opt/docker/bin/service.d/laravel-artisan.sh
COPY --chmod=644 laravel-horizon.supervisor.conf /opt/docker/etc/supervisor.d/laravel-horizon.conf
COPY --chmod=644 laravel-schedule-worker.supervisor.conf /opt/docker/etc/supervisor.d/laravel-schedule-worker.conf
COPY --chmod=644 laravel-pulse.supervisor.conf /opt/docker/etc/supervisor.d/laravel-pulse.conf

# Patch the web document root from webdevops container for laravels structure
ENV WEB_DOCUMENT_ROOT=${APPLICATION_PATH}/public
