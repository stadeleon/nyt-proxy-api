FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    libpq-dev \
    libmagickwand-dev \
    libmagickcore-dev \
    pkg-config \
    autoconf \
    iputils-ping \
    telnet \
    wget \
    git \
    && git clone https://github.com/Imagick/imagick.git --depth 1 /tmp/imagick \
    && cd /tmp/imagick \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && docker-php-ext-install \
       pdo_mysql \
       pdo_pgsql \
       zip \
       intl \
       mbstring \
       gd \
    && pecl install redis xdebug \
    && docker-php-ext-enable redis imagick xdebug \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY docker/app/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY app/ ./

RUN if [ -f composer.json ]; then composer install --no-scripts --no-autoloader --prefer-dist; fi
RUN if [ -f composer.json ]; then composer dump-autoload --optimize; fi

RUN chown -R www-data:www-data /var/www

EXPOSE 9000

CMD ["php-fpm"]