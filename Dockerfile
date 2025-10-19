FROM php:8.4.8-apache-bookworm

# Install dependencies for building PHP modules
RUN apt-get update && apt-get install -y --no-install-recommends \
    zip libzip-dev libpng-dev libicu-dev libxml2-dev libmariadb-dev

# Install additional PHP modules
RUN docker-php-ext-install mysqli pdo pdo_mysql gd zip intl xml

# Clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/*