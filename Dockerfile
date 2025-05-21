FROM php:8.4-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    zip \
    opcache

# Enable Apache modules
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Set environment variables
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV DATABASE_URL="postgresql://postgres:${POSTGRES_PASSWORD}@webscraper-case-db.internal:5432/postgres?serverVersion=16&charset=utf8"

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
RUN php bin/console doctrine:migrations:migrate --no-interaction
# Set permissions
RUN chown -R www-data:www-data var

# Configure Apache
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Update apache configuration
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf
RUN sed -i 's/*:80/*:8080/g' /etc/apache2/sites-available/000-default.conf

# Set the document root to public directory
ENV APACHE_DOCUMENT_ROOT /app/public
RUN sed -i -e 's|/var/www/html|${APACHE_DOCUMENT_ROOT}|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i -e 's|/var/www/|${APACHE_DOCUMENT_ROOT}|g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Expose port 8080
EXPOSE 8080

# Start Apache
CMD ["apache2-foreground"]
