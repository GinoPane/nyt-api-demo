FROM composer:2.8.8 AS composer_docker
FROM php:8.4.6-fpm-bookworm

ENV DEBIAN_FRONTEND=noninteractive

# Upgrade the system
RUN apt-get update && apt-get upgrade -y && apt-get install -y --no-install-recommends \
  git \
  libxml2-dev \
  zip \
  unzip \
  libzip-dev \
  nano \
  && rm -rf /var/lib/apt/lists/*

RUN pecl install apcu && docker-php-ext-enable apcu
RUN pecl install xdebug-3.4.2 && docker-php-ext-enable xdebug

# Install and enable PHP extensions
RUN docker-php-ext-install \
  intl \
  soap \
  bcmath \
  mysqli \
  pdo \
  pdo_mysql \
  zip \
  && docker-php-ext-enable \
  soap

RUN CFLAGS="$CFLAGS -D_GNU_SOURCE" docker-php-ext-install sockets

ADD ./docker/php/docker-php-maxexectime.ini /usr/local/etc/php/conf.d/docker-php-maxexectime.ini
ADD ./docker/php/docker-php-apcu-cli.ini /usr/local/etc/php/conf.d/docker-php-apcu-cli.ini
ADD ./docker/php/docker-php-memory-limits.ini /usr/local/etc/php/conf.d/docker-php-memory-limits.ini
ADD ./docker/xdebug/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN mkdir -p /var/www/\
  && addgroup -gid 1000 www \
  && useradd -u 1000 -g www -s /bin/bash -d /var/www www \
  && chown -R www:www /var/www/ \
  && echo "app ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

RUN rm /usr/local/etc/php-fpm.d/www.conf
ADD ./docker/php/www.conf /usr/local/etc/php-fpm.d/

USER www

WORKDIR /var/www

COPY --from=composer_docker /usr/bin/composer /usr/bin/composer
COPY --chown=www:www . /var/www

RUN composer install --prefer-dist --optimize-autoloader --no-plugins --no-scripts
