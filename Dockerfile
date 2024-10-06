# Используем официальный образ PHP с поддержкой Apache
FROM php:8.3-apache

# Установка необходимых расширений PHP и инструментов
RUN apt-get update && apt-get install -y \
    rsyslog \
    git \
    unzip \
    zip \
    wget \
    default-mysql-client \
    curl \
    nano \
    nodejs \
    libzip-dev \
    npm \
    libpq-dev \
    && docker-php-ext-install exif bcmath mysqli pdo pdo_pgsql pgsql pdo_mysql


# intl
RUN apt-get install -y libicu-dev \
  && docker-php-ext-configure intl \
  && docker-php-ext-install intl

# Установка gd
RUN apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev && \
docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ && \
docker-php-ext-install gd

# Установка Xdebug extension
RUN pecl install xdebug \
    # Enable xdebug
    && docker-php-ext-enable xdebug

# Установка memcached
RUN apt-get install -y libmemcached-dev zlib1g-dev && \
pecl install memcached \

RUN docker-php-ext-enable memcached

# Установка phalcon
RUN pecl install phalcon-5.6.0 && docker-php-ext-enable phalcon

#zip
RUN pecl install zip && docker-php-ext-enable zip


# Install swool
RUN pecl install swoole && docker-php-ext-enable swoole
RUN apt-get install -y libpcre3-dev software-properties-common


# pcov
RUN pecl install pcov && docker-php-ext-enable pcov

# redis
RUN pecl install redis && docker-php-ext-enable redis

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копирование файлов проекта в контейнер
COPY . /var/www/html

# Установка зависимостей проекта
WORKDIR /var/www/html
RUN composer install

# Настройка прав доступа
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 755 /var/www/html

# Включение модуля переписывания Apache и настройка хоста
RUN a2enmod rewrite

# Копирование пользовательского файла конфигурации Apache
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

# Установка рабочей директории
WORKDIR /var/www/html

# Экспонирование порта 80 для доступа к приложению
EXPOSE 80
