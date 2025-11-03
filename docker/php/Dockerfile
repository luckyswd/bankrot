FROM php:8.4-fpm-alpine3.19

# Устанавливаем зависимости
RUN apk add --no-cache \
    git \
    unzip \
    bash \
    libzip-dev \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    icu-data-full \
    icu-libs

# Устанавливаем build зависимости для компиляции расширений
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev \
    zlib-dev \
    g++ \
    make \
    autoconf

# Устанавливаем PHP расширения
RUN docker-php-ext-install pdo_mysql \
    && docker-php-ext-install opcache \
    && docker-php-ext-install zip \
    && docker-php-ext-install bcmath \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install gd \
    && docker-php-ext-install intl

# Удаляем build зависимости для уменьшения размера образа
RUN apk del .build-deps

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Копируем php.ini
COPY config/php.ini /usr/local/etc/php/php.ini

WORKDIR /var/www/html

CMD ["php-fpm"]
