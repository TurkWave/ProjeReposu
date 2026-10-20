FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql

# Document root is public/; unknown paths fall back to the front controller.
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/000-default.conf \
        /etc/apache2/apache2.conf \
 && printf '<Directory /var/www/html/public>\n    FallbackResource /index.php\n</Directory>\n' \
        > /etc/apache2/conf-available/front-controller.conf \
 && a2enconf front-controller

WORKDIR /var/www/html
COPY src/ /var/www/html/

# Upload target must exist and be writable by Apache (see app/ayarlar.php 'upload.dir').
RUN mkdir -p public/storage/uploads \
 && chown -R www-data:www-data public/storage

EXPOSE 80
