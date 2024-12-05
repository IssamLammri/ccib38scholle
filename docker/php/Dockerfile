# docker/php/Dockerfile

FROM php:8.2-fpm

# Install dependencies
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

# Install Yarn
RUN npm install --global yarn

RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION.(PHP_ZTS ? '-zts' : '');") \
&& architecture=$(uname -m) \
&& curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/$architecture/$version \
&& mkdir -p /tmp/blackfire \
&& tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
&& mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
&& printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8307\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
&& rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . .

# Copy existing application directory permissions
COPY --chown=www-data:www-data . .

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
