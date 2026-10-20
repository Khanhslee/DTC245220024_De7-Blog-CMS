FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql \
    && sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

COPY src/ /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

USER www-data

EXPOSE 8080
