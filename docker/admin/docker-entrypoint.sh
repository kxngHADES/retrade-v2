#!/bin/sh
set -e

# Check if vendor directory exists
if [ ! -d "/var/www/html/vendor" ]; then
	echo "Vendor directory not found. Running composer install..."
	composer install --working-dir=/var/www/html --no-interaction --no-progress
else
	echo "Vendor directory exists. Skipping composer install."
fi

# Execute the original PHP-FPM entrypoint
exec docker-php-entrypoint "$@"