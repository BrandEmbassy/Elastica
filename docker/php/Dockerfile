# Shared company PHP 8.2 base (see BrandEmbassy/docker
# dockerfiles/platform-backend-dev/xdebug-8.2): PHP 8.2, composer and the
# extensions the suite needs (json, mbstring, curl, openssl) are preinstalled,
# and it carries the internal CA. Requires an ECR login to pull:
#   aws ecr get-login-password --region eu-west-1 | docker login --username AWS --password-stdin 563770389081.dkr.ecr.eu-west-1.amazonaws.com
FROM 563770389081.dkr.ecr.eu-west-1.amazonaws.com/php:8.2-debian-bullseye

USER root

# `make` drives the repo's targets; `git` lets composer resolve source installs
RUN apt-get update \
    && apt-get install -y --no-install-recommends make git \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

USER www-data
WORKDIR /var/www/html
