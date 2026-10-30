FROM php:8.4-fpm

ARG UID=1000
ARG GID=1000

RUN groupmod -g ${GID} www-data \
    && usermod -u ${UID} -g ${GID} www-data

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        bcmath \
        gd \
        intl \
        opcache \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN mkdir -p /var/www/html \
    && chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

COPY php.ini /usr/local/etc/php/conf.d/custom.ini

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
