## syntax = devthefuture/dockerfile-x:v1.4.1
## See https://github.com/devthefuture-org/dockerfile-x
## See issue https://github.com/devthefuture-org/dockerfile-x/issues/6
## Also see/update the other docker files in this repo
#
#FROM ../Dockerfile

FROM base

# Copy the deploy service
COPY --chmod=755 laravel-autopull.service.sh /opt/docker/bin/service.d/laravel-autopull.sh

# Register a crontab to periodically call the deploy service
RUN set -x \
  && echo -e '*\t*\t*\t*\t*\t/opt/docker/bin/service.d/laravel-autopull.sh' >> /etc/crontabs/root

# Set the default branch
ENV BRANCH=master

# Set the default deploy commands and their separator
ENV DEPLOY_COMMAND_SEPARATOR=;
ENV DEPLOY_COMMANDS \
  artisan:down ; \
  git:update ; \
  composer:install --no-progress ; \
  env:update ; \
  artisan:storage:link ; \
  artisan:optimize ; \
  artisan:migrate --force ; \
  permissions:fix ; \
  artisan:up
ENV INITIAL_DEPLOY_COMMAND_SEPARATOR=;
ENV INITIAL_DEPLOY_COMMANDS \
  composer:install --no-progress ; \
  env:update ; \
  artisan:key:generate --force ; \
  permissions:fix
