FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    libmemcached-dev \
    zlib-dev \
    gmp-dev \
    libxml2-dev \
    openssl \
    postgresql-dev # Even if not used, some packages might depend on it. Better safe than sorry.

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) pdo_mysql bcmath exif pcntl gd zip intl opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Low-memory PHP-FPM/opcache defaults for small VPS deployments
COPY zz-simpus-low-memory.conf /usr/local/etc/php-fpm.d/zz-simpus-low-memory.conf

# Set working directory
WORKDIR /var/www/html

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
