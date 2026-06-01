FROM php:8.3-apache

# Install PHP extensions and enable required Apache modules
RUN apt-get update && apt-get install -y \
    libzip-dev libxml2-dev libicu-dev libonig-dev \
    && docker-php-ext-install pdo_mysql mysqli zip intl mbstring xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite headers

# Configure Apache to listen on Railway's dynamic PORT
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN cd api && composer install --no-dev --optimize-autoloader --no-interaction

# Create uploads directories with correct permissions
RUN mkdir -p uploads/announcements uploads/students \
    && chown -R www-data:www-data uploads \
    && chmod -R 775 uploads

# Apache configuration
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -name "*.php" -exec chmod 644 {} \; \
    && find /var/www/html -type d -exec chmod 755 {} \;

# Copy and configure startup script
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
