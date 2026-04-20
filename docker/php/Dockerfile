FROM php:8.4-fpm

# Install system dependencies for Composer and common PHP extensions
RUN apt-get update && apt-get install -y \
	git \
	zip \
	unzip \
	curl \
	&& rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql
RUN pecl install redis
RUN docker-php-ext-enable redis

RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy custom entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]

# Set working directory
WORKDIR /var/www/html