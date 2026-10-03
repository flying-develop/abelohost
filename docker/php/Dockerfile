FROM php:8.4-fpm-bookworm

RUN apt-get update \
	&& apt-get install -y --no-install-recommends git unzip libonig-dev \
	&& docker-php-ext-install pdo_mysql mbstring \
	&& rm -rf /var/lib/apt/lists/* \
	&& sed -i '/^user = /d; /^group = /d' /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html
