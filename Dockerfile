FROM php:8.2-apache

# تثبيت الإضافات والمكتبات الأساسية لـ Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd

# تفعيل مود الـ Rewrite الخاص بـ Apache لـ Laravel routes
RUN a2enmod rewrite

# تعديل مسار الـ DocumentRoot الخاص بـ Apache ليشير إلى مجلد public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# نسخ ملفات المشروع بالكامل للسيرفر
COPY . /var/www/html

# تثبيت Composer وتنزيل المكتبات
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# إعطاء الصلاحيات المناسبة لمجلدات الـ Storage والـ Cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# تنفيذ الـ migration تلقائياً ثم تشغيل سيرفر Apache بالصيغة الصحيحة
CMD sh -c "php artisan migrate --force && apache2-foreground"