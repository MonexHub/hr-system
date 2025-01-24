FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libapache2-mod-security2 \
    libzip-dev \
    libicu-dev\
    nano

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

ENV APACHE_DOCUMENT_ROOT=/var/www/html/hr-system/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/hr-system

RUN a2enmod rewrite

COPY . /var/www/html/hr-system

RUN composer install

RUN chown -R www-data:www-data /var/www/html/hr-system/storage /var/www/html/hr-system/bootstrap/cache /var/www/html/hr-system



EXPOSE 9000

CMD ["apache2-foreground"]
