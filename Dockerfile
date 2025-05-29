# Use the official PHP image with Apache
FROM php:8.2-apache

# Install required PHP extensions (add more if needed)
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite (optional, but useful)
RUN a2enmod rewrite

# Copy your app files into the Apache root directory
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

# Expose default Apache port
EXPOSE 80
