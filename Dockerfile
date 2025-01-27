FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    libicu-dev \
    libzip-dev\
    nano

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl
RUN docker-php-ext-configure intl

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable mod_rewrite
RUN a2enmod rewrite

# Set ServerName globally
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html/hr-system

# Fix git ownership issue
RUN git config --global --add safe.directory /var/www/html/hr-system

# Copy existing application directory contents
COPY . /var/www/html/hr-system

# Configure Apache Document Root
RUN sed -i 's!/var/www/html!/var/www/html/hr-system/public!g' /etc/apache2/sites-available/000-default.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html/hr-system \
    && chmod -R 755 /var/www/html/hr-system \
    && chmod -R 777 /var/www/html/hr-system/storage \
    && chmod -R 777 /var/www/html/hr-system/bootstrap/cache

# Create storage directory structure if it doesn't exist
RUN mkdir -p /var/www/html/hr-system/storage/framework/{sessions,views,cache} \
    && chmod -R 777 /var/www/html/hr-system/storage/framework

# Start Apache as root (needed for port 80)
USER root

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
