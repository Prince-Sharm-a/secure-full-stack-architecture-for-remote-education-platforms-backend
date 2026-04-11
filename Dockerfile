FROM php:8.2-cli

# Install PostgreSQL driver + dependencies
RUN apt-get update && apt-get install -y \
    unzip curl git libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

EXPOSE 10000

# 🔥 IMPORTANT: move cache commands to runtime
CMD php artisan config:clear && \
    php artisan cache:clear && \
    php artisan config:cache && \
    php artisan serve --host=0.0.0.0 --port=10000