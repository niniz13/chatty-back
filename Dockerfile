# Dockerfile for Chatty Symfony API
FROM php:8.2-fpm-alpine
WORKDIR /app
RUN apk add --no-cache bash git zip unzip icu-dev libpq-dev oniguruma-dev
COPY composer.* ./
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction --no-progress
COPY . .
RUN composer install --no-interaction --no-progress
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
