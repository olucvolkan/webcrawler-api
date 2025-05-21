FROM php:8.2-apache

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

# Copy project files
COPY . /app/

# Set environment variables
ENV APP_ENV=prod
ENV APP_DEBUG=0

# Install dependencies
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-scripts

# Generate optimized Symfony configuration
RUN composer dump-env prod

# Clear and warm up cache
RUN php bin/console cache:clear --no-warmup
RUN php bin/console cache:warmup

# Set permissions
RUN chown -R www-data:www-data var

# Configure Apache
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Update apache configuration to use port 8080
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf
RUN sed -i 's/*:80/*:8080/g' /etc/apache2/sites-available/000-default.conf

# Expose port 8080
EXPOSE 8080

# Start Apache
CMD ["apache2-foreground"]
