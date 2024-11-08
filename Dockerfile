# Use the official PHP image with Apache
FROM php:7.4-apache

# Copy your PHP files to the Apache server directory
COPY . /var/www/html/

# Install any PHP extensions you need (e.g., PDO for database connectivity)
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Set file permissions (optional)
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80

# Start the Apache server
CMD ["apache2-foreground"]

