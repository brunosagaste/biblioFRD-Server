FROM php:8.0.28-apache
MAINTAINER Bruno Sagaste

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

RUN apt update && sudo apt install curl php-cli php-mbstring git unzip && cd ~ && curl -sS https://getcomposer.org/installer -o composer-setup.php && sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer && composer install

RUN chown -R www-data:www-data /var/www

CMD ["start-apache"]