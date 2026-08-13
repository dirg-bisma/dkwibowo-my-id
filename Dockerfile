FROM php:8.3-apache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

COPY . .
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint

RUN sed -ri 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/000-default.conf \
        /etc/apache2/apache2.conf \
    && chmod +x /usr/local/bin/app-entrypoint \
    && mkdir -p database storage/cache/htmlpurifier storage/media/cover storage/media/inline storage/trash \
    && chown -R www-data:www-data database storage content

ENV APP_ENV=production
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

ENTRYPOINT ["app-entrypoint"]
