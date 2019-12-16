# name:docker_php
# use:store  
# date:2017-09-04  
  
FROM php:7.4.0-fpm

MAINTAINER siyue 350375092@qq.com  

# redis 扩展
ENV PHPREDIS_VERSION 5.1.1
# msgpack扩展
ENV MSGPACK_VERSION 2.0.3
# memcached扩展
ENV MEMCACHED_VERSION 3.1.3
# mongodb扩展
ENV MONGODB_VERSION 1.5.3
# xhprof扩展
ENV XHPROF_VERSION 2.1.3
# swoole安装 如果以后用到的话，不用再安装了，4.0之后性能更好
ENV SWOOLE_VERSION 4.4.8
# swoole依赖hiredis
ENV HIREDIS_VERSION 0.13.3
# Xdebug 版本
ENV XDEBUG_VERSION 2.8.1
# PHPunit 安装
ENV PHPUNIT_VERSION 8.5

# 设置时间
RUN /bin/cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime \
    && echo 'Asia/Shanghai' > /etc/timezone

# 扩展依赖
RUN apt-get update \
    && apt-get install -y wget 

# Composer安装
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer self-update --clean-backups

# 自带扩展 直接安装即可
# phar扩展 phpunit依赖它
RUN docker-php-ext-install phar 
RUN docker-php-ext-install pcntl 
RUN docker-php-ext-install opcache 
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install mysqli 

# 安装 ZIP 扩展
RUN apt-get install -y zip libzip-dev \
    && docker-php-ext-configure zip --with-libzip \
  	&& docker-php-ext-install zip

# GD库安装
RUN apt-get install -y libwebp-dev libjpeg-dev libpng-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-webp=/usr/include --with-jpeg=/usr/include  --with-freetype=/usr/include \
    && docker-php-ext-install -j$(nproc) gd

# Redis 扩展下载
RUN curl -L -o /tmp/redis.tar.gz https://github.com/phpredis/phpredis/archive/$PHPREDIS_VERSION.tar.gz \
    && tar xfz /tmp/redis.tar.gz \
    && rm -r /tmp/redis.tar.gz \
    && mkdir -p /usr/src/php/ext \
    && mv phpredis-$PHPREDIS_VERSION /usr/src/php/ext/redis \
    && docker-php-ext-install redis \
    && rm -rf /usr/src/php

# memcached 扩展下载 pecl本地安装 开启扩展
RUN apt-get install -y libmemcached-dev \
	&& wget http://pecl.php.net/get/memcached-${MEMCACHED_VERSION}.tgz -O /tmp/memcached.tgz \
    && pecl install /tmp/memcached.tgz \
    && rm -rf /tmp/memcached.tgz \
    && docker-php-ext-enable memcached

# mongodb 扩展下载 pecl本地安装 开启扩展  
RUN wget http://pecl.php.net/get/mongodb-${MONGODB_VERSION}.tgz -O /tmp/mongodb.tgz \
    && pecl install /tmp/mongodb.tgz \
    && rm -rf /tmp/mongodb.tgz \
    && docker-php-ext-enable mongodb

# msgpack 扩展下载 pecl本地安装 (延迟队列使用减少源数据占用空间)
RUN wget http://pecl.php.net/get/msgpack-${MSGPACK_VERSION}.tgz -O /tmp/msgpack.tgz \
    && pecl install /tmp/msgpack.tgz \
    && rm -rf /tmp/msgpack.tgz \
    && docker-php-ext-enable msgpack

# XDEBUG 扩展
RUN wget http://pecl.php.net/get/xdebug-${XDEBUG_VERSION}.tgz -O /tmp/xdebug.tgz \
   && pecl install /tmp/xdebug.tgz \
   && rm -rf /tmp/xdebug.tgz \
   && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
   && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
   && echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/xdebug.ini \
   && echo "xdebug.xdebug.remote_host=127.0.0.1" >> /usr/local/etc/php/conf.d/xdebug.ini \
   && echo "xdebug.xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/xdebug.ini

# phpunit 安装
RUN wget http://phar.phpunit.cn/phpunit-${PHPUNIT_VERSION}.phar \
	&& chmod +x phpunit-${PHPUNIT_VERSION}.phar \
	&& mv phpunit-${PHPUNIT_VERSION}.phar /usr/local/bin/phpunit

# xhprof
RUN wget https://github.com/longxinH/xhprof/archive/v${XHPROF_VERSION}.tar.gz -O /tmp/xhprof.tar.gz \
    && mkdir -p /tmp/xhprof \
    && tar -xf /tmp/xhprof.tar.gz -C /tmp/xhprof --strip-components=1 \
    && rm -rf /tmp/xhprof.tar.gz \
    && ( \
        cd /tmp/xhprof/extension \
        && phpize \
        && ./configure  \
        && make -j$(nproc) \
        && make install \
    ) \
    && rm -rf /tmp/xhprof \
    && docker-php-ext-enable xhprof

# Hiredis扩展安装 swoole依赖
RUN wget https://github.com/redis/hiredis/archive/v${HIREDIS_VERSION}.tar.gz -O /tmp/hiredis.tar.gz \
  && mkdir -p /tmp/hiredis \
    && tar -xf /tmp/hiredis.tar.gz -C /tmp/hiredis --strip-components=1 \
    && rm -rf /tmp/hiredis.tar.gz \
    && ( \
        cd /tmp/hiredis \
        && make -j$(nproc) \
        && make install \
        && ldconfig \
    ) \
    && rm -rf /tmp/hiredis

# Swoole 扩展安装 开启扩展
RUN apt-get install -y libz-dev libssl-dev libnghttp2-dev libpcre3-dev zlib1g-dev \
    && wget https://github.com/swoole/swoole-src/archive/v${SWOOLE_VERSION}.tar.gz -O /tmp/swoole.tar.gz \
    && mkdir -p /tmp/swoole \
    && tar -xf /tmp/swoole.tar.gz -C /tmp/swoole --strip-components=1 \
    && rm -rf /tmp/swoole.tar.gz \
    && ( \
        cd /tmp/swoole \
        && phpize \
        && ./configure --enable-async-redis --enable-mysqlnd --enable-openssl --enable-http2 \
        && make -j$(nproc) \
        && make install \
    ) \
    && rm -r /tmp/swoole \
    && docker-php-ext-enable swoole

# 清理空间
RUN apt-get clean -y && apt-get autoremove -y