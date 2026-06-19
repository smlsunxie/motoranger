# ---------- PHP 依賴 (composer vendor) ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# 此階段僅下載依賴;執行期擴充由 FrankenPHP 階段(install-php-extensions)提供,故忽略平台檢查
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# ---------- 前端資產 ----------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json vite.config.js ./
RUN npm ci
COPY resources ./resources
COPY app ./app
# Filament 自訂主題 theme.css 會 @import / @source vendor 與 app/Filament,需一併提供
COPY --from=vendor /app/vendor ./vendor
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

# 上傳大小等 PHP 設定
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 沿用 vendor stage 的依賴(利用 layer cache)
COPY --from=vendor /app/vendor ./vendor

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev \
    && php artisan filament:assets \
    && chmod +x docker/entrypoint.sh

ENV OCTANE_SERVER=frankenphp
EXPOSE 8000

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000"]
