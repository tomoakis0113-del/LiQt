FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY run-cron.sh /usr/local/bin/run-cron.sh
RUN chmod +x /usr/local/bin/run-cron.sh
RUN composer install

CMD /usr/local/bin/run-cron.sh & apache2-foreground