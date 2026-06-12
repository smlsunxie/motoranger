#!/bin/sh
set -e

php artisan storage:link --force 2>/dev/null || true

if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
    php artisan migrate --force
fi

php artisan optimize

exec "$@"
