# docker/php/Dockerfile

FROM php:8.2-fpm

# 1) Install dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) intl pdo pdo_mysql zip gd

# 2) Copier la conf d’upload avant de démarrer PHP
COPY docker/php/conf.d/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# 3) Install Yarn
RUN npm install --global yarn


# 5) Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6) Set working directory & copy files
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . .

# Copy existing application directory permissions
COPY --chown=www-data:www-data . .

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]

# Install Xdebug via PECL
RUN pecl install xdebug \
 && docker-php-ext-enable xdebug

# Optionnel: montrer la version à build-time
RUN php -v && php -m | grep -i xdebug || true
