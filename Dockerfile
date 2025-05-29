FROM php:8.2-apache

# Install system dependencies required by zip
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install zip mysqli

# Enable Apache rewrite module (optional)
RUN a2enmod rewrite

# Copy all files to web root
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80
