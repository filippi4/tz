FROM php:8.4-fpm-alpine

ARG WWWGROUP=1000
ARG WWWUSER=1000

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create user with host UID/GID
RUN deluser --remove-home www-data && \
    addgroup -g ${WWWGROUP} www-data && \
    adduser -D -u ${WWWUSER} -G www-data www-data

# Set working directory
WORKDIR /var/www/html

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
