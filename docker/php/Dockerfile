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
    && docker-php-ext-install -j$(nproc) intl pdo pdo_mysql zip gd \
    # Install Encore CLI
    && curl -sSL https://encore.dev/install.sh | bash \
    # Ensure Encore is in PATH
    && echo 'export PATH="$PATH:/root/.encore/bin"' >> /root/.bashrc

# 2) Copier la conf d’upload avant de démarrer PHP
COPY docker/php/conf.d/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# 3) Install Yarn
RUN npm install --global yarn

# 4) Blackfire probe (inchangé)
RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION.(PHP_ZTS ? '-zts' : '');") \
 && architecture=$(uname -m) \
 && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s \
     https://blackfire.io/api/v1/releases/probe/php/linux/$architecture/$version \
 && mkdir -p /tmp/blackfire \
 && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
 && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
 && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8307\n" \
     > $PHP_INI_DIR/conf.d/blackfire.ini \
 && rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz

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
