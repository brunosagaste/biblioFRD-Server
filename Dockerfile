FROM php:8.0.28-apache
MAINTAINER Bruno Sagaste

COPY apache2conf/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

RUN docker-php-ext-install pdo pdo_mysql
RUN apt update && apt -y install curl git unzip && mkdir -p /composerinstallfiles && cd /composerinstallfiles && curl -sS https://getcomposer.org/installer -o composer-setup.php && php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN chown -R www-data:www-data /var/www