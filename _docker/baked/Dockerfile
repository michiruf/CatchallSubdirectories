# Baked image for CatchAllSubdirectories.
# Extends the generic Laravel container from https://github.com/michiruf/laravel-docker
# with the PHP extensions this application needs, then bakes the application in.
FROM ghcr.io/michiruf/laravel-docker:baked-nightly

# Add imap php extension (removed from PHP core in 8.4, needed by ddeboer/imap)
# and excimer php extension for profiling with sentry.io
# Build deps are installed once and cleaned up after both extensions are built
RUN set -x \
  && apk-install \
      linux-headers \
      make \
      autoconf \
      g++ \
      imap-dev \
      openssl-dev \
      krb5-dev \
  && pecl install imap \
  && docker-php-ext-enable imap \
  && (pecl install excimer && docker-php-ext-enable excimer || true) \
  && apk del -f --purge \
      autoconf \
      linux-headers \
      g++ \
      make \
      imap-dev \
      openssl-dev \
      krb5-dev \
  && docker-run-bootstrap \
  && (docker-image-cleanup || true)

# Bake the application in. The build context already contains an installed 'vendor/'
# (locally your working install; in CI the publish workflow runs 'composer install
# --no-dev' before building). On first container start the base image moves /app-src
# into place, generates the APP_KEY and runs the deploy commands (env, storage:link,
# optimize, migrate).
COPY --chown=$APPLICATION_UID:$APPLICATION_GID . /app-src
