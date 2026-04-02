FROM php:8.3-apache
 
# Install PostgreSQL driver for PHP
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean
 
# Set web root to /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
 
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf
 
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf
 
# Enable mod_rewrite
RUN a2enmod rewrite