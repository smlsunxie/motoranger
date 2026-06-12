# ---------- 前端資產 ----------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json vite.config.js ./
RUN npm ci
COPY resources ./resources
RUN npm run build

# ---------- 應用程式 (Octane + FrankenPHP) ----------
FROM dunglas/frankenphp:1-php8.4

RUN install-php-extensions \
    pcntl \
    pdo_mysql \
    gd \
    intl \
    zip \
    bcmath \
    opcache \
    redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 先裝依賴(利用 layer cache)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev \
    && php artisan filament:assets \
    && chmod +x docker/entrypoint.sh

ENV OCTANE_SERVER=frankenphp
EXPOSE 8000

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000"]
