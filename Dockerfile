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
    libicu-dev

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/hr-system

COPY apache-config.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

COPY . /var/www/html/hr-system

RUN composer install

RUN chown -R www-data:www-data /var/www/html/hr-system/storage /var/www/html/hr-system/bootstrap/cache

EXPOSE 9000

CMD ["apache2-foreground"]
