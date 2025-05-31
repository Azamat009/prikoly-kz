FROM php:8.2-fpm

# Define build arguments for UID and GID, defaulting to 1000
ARG UID=1000
ARG GID=1000

# Create a group and user with the specified GID and UID, and set up a home directory
RUN groupadd -g ${GID} appgroup && \
    useradd -u ${UID} -g appgroup -m -d /home/appuser appuser

# Install system dependencies including PostgreSQL client and development libraries
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libicu-dev \
    procps \
    libpq-dev \
    libonig-dev \
    && docker-php-ext-install intl pdo pdo_mysql opcache pdo_pgsql  # Install pdo_pgsql extension

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin \
    --filename=composer

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Set working directory
WORKDIR /var/www/html

RUN { \
    echo 'upload_max_filesize = 100M'; \
    echo 'post_max_size = 100M'; \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 300'; \
    echo 'max_input_time = 300'; \
} > /usr/local/etc/php/conf.d/uploads.ini