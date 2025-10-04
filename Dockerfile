FROM php:8.2-apache

# Install extensions and tools
RUN docker-php-ext-install mysqli pdo pdo_mysql && a2enmod rewrite

# Copy project into the container's web root
WORKDIR /var/www/html
COPY . /var/www/html/

# Adjust permissions (if needed)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
