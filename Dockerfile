FROM php:8.2-apache

# Install system packages, PHP extensions, and enable Apache mods
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
    git \
    unzip \
    ca-certificates; \
    rm -rf /var/lib/apt/lists/*; \
    docker-php-ext-install mysqli pdo pdo_mysql; \
    a2enmod rewrite

# Install Composer (from official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Add PHP upload limits
COPY uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Copy composer files first and install dependencies (including PHPMailer)
COPY composer.json composer.lock* ./
RUN set -eux; \
    composer install --no-dev --prefer-dist --no-interaction --no-progress; \
    rm -rf /root/.composer

# Copy the rest of the application
COPY . /var/www/html/

# Ensure correct ownership
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
