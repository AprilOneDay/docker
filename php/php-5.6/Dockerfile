ap# name:docker_php
# use:store  
# date:2017-09-04  
  
FROM php:5.6-fpm

MAINTAINER siyue 350375092@qq.com  

# You may need proxy
# RUN export http_proxy=192.168.1.10:1080
# RUN export https_proxy=192.168.1.10:1080

# 替换国内镜像
COPY ./source.list /etc/apt/repositories

# 保证时间一致
COPY ./etc/localtime /etc/localtime

# Update ubuntu
RUN apt-get update
RUN apt-get upgrade -y

# 安装定时任务模块
# RUN apt-get install -y curl
# RUN apt-get install -y cron

# mcrypt
RUN apt-get install -y libmcrypt-dev
RUN docker-php-ext-install mcrypt

# GD
RUN apt-get install -y libfreetype6-dev libjpeg62-turbo-dev # libpng12-dev
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install -j$(nproc) gd

# Intl
# RUN apt-get install -y libicu-dev
# RUN docker-php-ext-install -j$(nproc) intl

# General
RUN docker-php-ext-install zip 
RUN docker-php-ext-install pcntl 
RUN docker-php-ext-install opcache 
RUN docker-php-ext-install pdo_mysql 
RUN docker-php-ext-install mysqli 
RUN docker-php-ext-install mysql

# SOAP Client
# RUN apt-get install -y libxml2-dev
# RUN docker-php-ext-install soap

# General extensions that may had be installed default
# If not, install them with following command
# RUN docker-php-ext-install ctype
# RUN docker-php-ext-install dom
# RUN docker-php-ext-install fileinfo
# RUN docker-php-ext-install ftp
# RUN docker-php-ext-install hash
RUN docker-php-ext-install iconv
RUN docker-php-ext-install json
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install session
RUN docker-php-ext-install pdo
# RUN docker-php-ext-install pdo_sqlite
# RUN docker-php-ext-install posix
# RUN docker-php-ext-install tokenizer
# RUN docker-php-ext-install simplexml
# RUN docker-php-ext-install xml
# RUN docker-php-ext-install xmlreader
# RUN docker-php-ext-install xmlwriter

# CURL, may had be installed default
RUN apt-get install -y curl
RUN apt-get install -y libcurl3
RUN apt-get install -y libcurl4-openssl-dev
RUN docker-php-ext-install curl

# redis
ENV PHPREDIS_VERSION 3.1.3
RUN curl -L -o /tmp/redis.tar.gz https://github.com/phpredis/phpredis/archive/$PHPREDIS_VERSION.tar.gz \
    && tar xfz /tmp/redis.tar.gz \
    && rm -r /tmp/redis.tar.gz \
    && mkdir -p /usr/src/php/ext \
    && mv phpredis-$PHPREDIS_VERSION /usr/src/php/ext/redis \
    && docker-php-ext-install redis \
    && rm -rf /usr/src/php

# memcached
# ENV PHPMEMCACHED_VERSION 2.2.0
# RUN apt-get install -y libmemcached-dev
# RUN pecl install memcached-$PHPMEMCACHED_VERSION
# RUN docker-php-ext-enable memcached
	
# More extensions
# RUN docker-php-ext-install exif
# RUN docker-php-ext-install bcmath
# RUN docker-php-ext-install calendar
# RUN docker-php-ext-install sockets
# RUN docker-php-ext-install gettext
# RUN docker-php-ext-install phar
# RUN docker-php-ext-install shmop
# RUN docker-php-ext-install sysvmsg
# RUN docker-php-ext-install sysvsem
# RUN docker-php-ext-install sysvshm

# More extensions handle database
# RUN docker-php-ext-install pdo_firebird
# RUN docker-php-ext-install pdo_dblib
# RUN docker-php-ext-install pdo_oci
# RUN docker-php-ext-install pdo_odbc
# RUN docker-php-ext-install pdo_pgsql
# RUN docker-php-ext-install pgsql
# RUN docker-php-ext-install oci8
# RUN docker-php-ext-install odbc
# RUN docker-php-ext-install dba
# RUN docker-php-ext-install interbase

# execute `RUN apt-get install -y libxml2-dev` before using following command
# RUN apt-get install -y libxslt-dev
# RUN docker-php-ext-install xsl
# RUN docker-php-ext-install xmlrpc
# RUN docker-php-ext-install wddx

# Readline
# RUN apt-get install -y libreadline-dev
# RUN docker-php-ext-install readline

# SNMP
# RUN apt-get install -y libsnmp-dev
# RUN apt-get install -y snmp
# RUN docker-php-ext-install snmp

# pspell
# RUN apt-get install -y libpspell-dev
# RUN apt-get install -y aspell-en
# RUN docker-php-ext-install pspell

# recode
# RUN apt-get install -y librecode0
# RUN apt-get install -y librecode-dev
# RUN docker-php-ext-install recode

# Tidy
# RUN apt-get install -y libtidy-dev
# RUN docker-php-ext-install tidy

# GMP
# RUN apt-get install -y libgmp-dev
# RUN docker-php-ext-install gmp

# Client
# RUN apt-get install -y postgresql-client
# RUN apt-get install -y mysql-client

# IMAP
# RUN apt-get install -y libc-client-dev
# RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl
# RUN docker-php-ext-install imap

# LDAP
# RUN apt-get install -y libldb-dev
# RUN apt-get install -y libldap2-dev
# RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu
# RUN docker-php-ext-install ldap

# Composer
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# XDEBUG
# RUN yes | pecl channel-update pecl.php.net && pecl install xdebug \
#    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
#    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
#    && echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/xdebug.ini
#    && echo "xdebug.xdebug.remote_host=127.0.0.1" >> /usr/local/etc/php/conf.d/xdebug.ini
#    && echo "xdebug.xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/xdebug.ini


