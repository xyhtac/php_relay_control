FROM php:7.2-cli
COPY . /usr/src/myapp
WORKDIR /usr/src/myapp
CMD ["php", "-S", "0.0.0.0:80", "-t", "/usr/src/myapp"]