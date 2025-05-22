FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    nginx \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    zip \
    opcache

# Create directories for PHP
RUN mkdir -p /var/lib/php/sessions \
    && mkdir -p /var/lib/php/wsdlcache \
    && chown -R www-data:www-data /var/lib/php

COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Set environment variables
ENV APP_ENV=prod
ENV APP_DEBUG=1
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install Symfony Flex
RUN composer global config --no-plugins allow-plugins.symfony/flex true
RUN composer global require "symfony/flex" --prefer-dist --no-progress --no-suggest --classmap-authoritative

# Install dependencies
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-scripts

# Copy rest of the application
COPY . .

# Generate optimized Symfony configuration
RUN composer dump-env prod

# Clear and warm up cache
RUN php bin/console cache:clear --no-warmup
RUN php bin/console cache:warmup

# Ensure proper permissions and directory structure
RUN mkdir -p public \
    && chown -R www-data:www-data . \
    && chmod -R 777 var \
    && chmod -R 777 public

# Configure Nginx
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
RUN rm /etc/nginx/sites-enabled/default

# Expose port 8080
EXPOSE 8080

# Copy entrypoint scripts
COPY docker/entrypoint.sh /usr/local/bin/
COPY docker/worker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/worker-entrypoint.sh

# Start with entrypoint script
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
