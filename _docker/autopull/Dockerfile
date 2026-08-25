# Autopull image for CatchAllSubdirectories.
# Extends the generic Laravel container from https://github.com/michiruf/laravel-docker
# with the PHP extensions this application needs. The application source is pulled
# from git at runtime (set GIT_URL / BRANCH), so nothing is copied in here.
FROM ghcr.io/michiruf/laravel-docker:autopull-nightly

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

# Deploy the CatchAllSubdirectories repository by default; override at runtime if needed.
ENV GIT_URL=https://github.com/michiruf/CatchAllSubdirectories.git
ENV BRANCH=master
