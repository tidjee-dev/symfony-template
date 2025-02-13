FROM php:8.4-apache

# Update and Install dependencies | Mise a jour et installation des dependances
RUN apt-get update \
  &&  apt-get install -y --no-install-recommends locales apt-utils git libicu-dev g++ \
  libpng-dev libxml2-dev libzip-dev libonig-dev libxslt-dev unzip lsd

# Install required locales | Installation des locales requises
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen  \
  &&  echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen \
  &&  locale-gen

# Install composer | Installation de composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install symfony CLI | Installation de la CLI symfony
RUN curl -sS https://get.symfony.com/cli/installer | bash \
  &&  mv /root/.symfony5/bin/symfony /usr/local/bin

# Install PHP extensions | Installation des extensions PHP
RUN  docker-php-ext-install pdo pdo_mysql opcache intl zip calendar dom mbstring gd xsl

# Install and enable APCu and Xdebug | Installation et activation de APCu et Xdebug
RUN  pecl install apcu && docker-php-ext-enable apcu \
  &&  pecl install xdebug && docker-php-ext-enable xdebug

# Configure xdebug | Configuration de xdebug
RUN echo "xdebug.coverage_enable" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Set working directory | Definition du repertoire de travail
WORKDIR /var/www

# Create useful aliases | Creation d'alias utiles
RUN echo "alias cls='clear'" >> ~/.bashrc \
  echo "alias ls='lsd -al --group-dirs first'" >> ~/.bashrc

# Expose port 80 | Expose le port 80
EXPOSE 80