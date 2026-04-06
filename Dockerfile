FROM php:8.1-apache
RUN docker-php-ext-install mysqli
COPY . /var/www/html/
RUN echo "DirectoryIndex index.php index.html" >> /etc/apache2/conf-enabled/charset.conf