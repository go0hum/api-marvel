FROM php:7.4-apache
#Install git
RUN apt-get update && apt-get install -y \
    		libfreetype6-dev \
    		libpng-dev \
    		libwebp-dev \
    		libjpeg62-turbo-dev \
    		libmcrypt-dev \
    		libzip-dev \
            libicu-dev \
            zip \
    		git \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
    pdo_mysql \
    gd \
    zip \
    intl \
    && a2enmod rewrite
#Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=. --filename=composer
RUN mv composer /usr/local/bin/

WORKDIR /var/www/html
COPY default.conf /etc/apache2/sites-enabled/000-default.conf
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]

EXPOSE 8000