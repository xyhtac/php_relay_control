FROM php:7.2-cli

# Install dependencies and the sockets extension
RUN apt-get update && apt-get install -y \
        libssl-dev \
        pkg-config \
    && docker-php-ext-install sockets \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
    
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
CMD ["php", "-S", "0.0.0.0:80", "-t", "/usr/src/myapp"]