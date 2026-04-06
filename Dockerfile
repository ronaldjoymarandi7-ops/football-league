FROM php:8.1-apache
RUN docker-php-ext-install mysqli
RUN echo "display_errors=On" >> /usr/local/etc/php/php.ini
RUN echo "error_reporting=E_ALL" >> /usr/local/etc/php/php.ini
COPY . /var/www/html/