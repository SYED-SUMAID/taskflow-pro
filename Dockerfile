FROM php:8.2-apache

# Install PostgreSQL development libraries
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Copy public website files into Apache's document root
COPY public/ /var/www/html/

# Copy application configuration outside the public directory
COPY app/ /var/www/app/

# Set the correct ownership
RUN chown -R www-data:www-data /var/www/html /var/www/app
