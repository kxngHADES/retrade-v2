FROM php:8.4-fpm

# Install system dependencies for Composer and common PHP extensions
RUN apt-get update && apt-get install -y \
	git \
	zip \
	unzip \
	curl \
	default-libmysqlclient-dev \
	default-mysql-client \
	&& rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql
RUN pecl install redis
RUN docker-php-ext-enable redis

RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    libzstd-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# PHP-FPM config
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

# Copy custom entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set PHP upload limits
RUN echo "upload_max_filesize=100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size=100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit=256M" >> /usr/local/etc/php/conf.d/uploads.ini

# Set entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]

# Set working directory
WORKDIR /var/www/html