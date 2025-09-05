FROM php:7.2-apache

# Set Debian archive repos for old PHP version
RUN sed -i 's|http://deb.debian.org/debian|http://archive.debian.org/debian|g' /etc/apt/sources.list \
 && sed -i 's|http://security.debian.org/debian-security|http://archive.debian.org/debian-security|g' /etc/apt/sources.list \
 && apt-get update -o Acquire::Check-Valid-Until=false \
 && apt-get install -y \
      libssl-dev pkg-config \
      snmp snmp-mibs-downloader \
      libsnmp-dev \
 && docker-php-ext-install sockets snmp \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# Copy app to Apache doc root
COPY . /var/www/html/
WORKDIR /var/www/html

# Permissions and mod_rewrite
RUN chown -R www-data:www-data /var/www/html \
 && a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]
