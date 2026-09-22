FROM php:8.3-apache

# pdo_sqlite для базы, mod_rewrite для «чистых» URL (/login вместо /index.php?...)
RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_sqlite \
    && a2enmod rewrite

# Продакшн-настройки PHP: ошибки пишутся в лог, а не в ответ браузеру
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# DocumentRoot -> public/, чтобы src/, views/ и data/ не были доступны из браузера
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf

COPY --chown=www-data:www-data . /var/www/html

# Каталог для файла SQLite должен быть доступен на запись веб-серверу
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html/data

EXPOSE 80
