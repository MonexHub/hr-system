FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libapache2-mod-security2

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/hr-system

RUN a2enmod rewrite

COPY . /var/www/html/hr-system

RUN composer install

RUN chown -R www-data:www-data /var/www/html/hr-system/storage /var/www/html/hr-system/bootstrap/cache

EXPOSE 9000

CMD ["apache2-foreground"]
