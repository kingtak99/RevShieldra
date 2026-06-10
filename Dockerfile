FROM php:8.2-apache

# تثبيت الاعتمادات وتحديث الشهادات الأمنية لضمان عمل طلبات الـ API الآمنة (HTTPS)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    ca-certificates \
    openssl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql pdo_pgsql pgsql gd

RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

COPY . /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تحديث الاعتمادات وتثبيت البكج الخاصة بـ Brevo والمكتبات الأخرى
RUN composer update --no-dev --optimize-autoloader --ignore-platform-reqs \
    && composer require guzzlehttp/guzzle resend/resend-laravel symfony/brevo-mailer --ignore-platform-reqs

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

CMD sh -c "php artisan migrate --force && php artisan storage:link && apache2-foreground"