FROM php:7.2-cli

# Install dependencies and the sockets extension
RUN sed -i 's|http://deb.debian.org/debian|http://archive.debian.org/debian|g' /etc/apt/sources.list \
 && sed -i 's|http://security.debian.org/debian-security|http://archive.debian.org/debian-security|g' /etc/apt/sources.list \
 && apt-get update -o Acquire::Check-Valid-Until=false \
 && apt-get install -y libssl-dev pkg-config \
 && docker-php-ext-install sockets \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
CMD ["php", "-S", "0.0.0.0:80", "-t", "/usr/src/myapp"]