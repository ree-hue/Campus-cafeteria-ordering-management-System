FROM php:8.2-apache

# Install and enable MySQL extensions
RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev \
    && docker-php-ext-install mysqli pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Create a safe writable session folder
RUN mkdir -p /var/www/html/tmp/sessions \
    && chown -R www-data:www-data /var/www/html/tmp

# Copy project files and custom PHP settings
COPY . /var/www/html/
COPY php.ini /usr/local/etc/php/conf.d/99-custom.ini

# Ensure the app files are owned by the Apache user
RUN chown -R www-data:www-data /var/www/html

# Configure Apache to serve from /var/www/html on Render's port
RUN a2enmod rewrite
RUN printf 'Listen 10000\n<VirtualHost *:10000>\n    DocumentRoot /var/www/html\n    DirectoryIndex index.php index.html\n    <Directory /var/www/html>\n        AllowOverride All\n        Require all granted\n    </Directory>\n    ErrorLog /proc/self/fd/2\n    CustomLog /proc/self/fd/1 common\n</VirtualHost>\n' > /etc/apache2/sites-available/000-default.conf

EXPOSE 10000
ENV PORT=10000
