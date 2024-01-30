FROM php:8.2-fpm

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y build-essential

RUN apt-get install -y git libcurl4-openssl-dev libxml2-dev libzip-dev zip unzip g++

RUN chmod uga+x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions xdebug gd mysqli pdo pdo_mysql tokenizer curl dom xml ctype zip fileinfo

RUN apt-get install libicu-dev

RUN docker-php-ext-configure intl \
        && docker-php-ext-install intl

RUN pecl install redis

RUN curl -sS https://getcomposer.org/installer | php -- --version=2.4.0 --install-dir=/usr/local/bin --filename=composer


ARG UNAME
ARG UID
ARG GID

RUN groupadd -g $GID -o $UNAME
RUN useradd $UNAME -u $UID -g $GID -m -s /bin/bash  

RUN { \
        echo 'upload_max_filesize = 5M'; \
        echo 'post_max_size = 5M'; \
	} > /usr/local/etc/php/conf.d/upload.ini
RUN { \
        echo 'memory_limit = 512M'; \
	} > /usr/local/etc/php/conf.d/docker-php-memlimit.ini

COPY ./docker/php.entrypoint.sh /usr/local/bin/php.entrypoint

CMD ["php.entrypoint"]
