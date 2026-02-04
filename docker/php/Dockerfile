FROM php:8.4-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip zip libgmp-dev \
    && docker-php-ext-install gmp \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN groupadd appuser \
    && useradd -m -g appuser -s /bin/bash appuser
USER appuser:appuser

ENV COMPOSER_MEMORY_LIMIT="-1"

WORKDIR /var/www/

CMD ["php-fpm"]
