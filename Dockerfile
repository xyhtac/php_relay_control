FROM php:7.2-apache

# Install dependencies and sockets extension
RUN sed -i 's|http://deb.debian.org/debian|http://archive.debian.org/debian|g' /etc/apt/sources.list \
 && sed -i 's|http://security.debian.org/debian-security|http://archive.debian.org/debian-security|g' /etc/apt/sources.list \
 && apt-get update -o Acquire::Check-Valid-Until=false \
 && apt-get install -y libssl-dev pkg-config \
 && docker-php-ext-install sockets \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# Copy your application to Apache document root
COPY . /var/www/html/
WORKDIR /var/www/html

# Ensure Apache rewrites and proper permissions (optional)
RUN chown -R www-data:www-data /var/www/html \
 && a2enmod rewrite

# Expose port 80 and start Apache in foreground
EXPOSE 80
CMD ["apache2-foreground"]
