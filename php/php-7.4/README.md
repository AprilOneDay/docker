# 创建网络组
docker network create --subnet=172.19.0.0/26 es-network
# 运行
docker run -itd --name=php7.4 --restart=always --privileged=true --cap-add=SYS_PTRACE --network es-network --ip 172.19.0.10 \
	-p 9001:9000 \
	-v /docker/php/php-7.4/php-fpm.d/:/usr/local/etc/php-fpm.d/ \
	-v /docker/src/:/var/www/html/ \
	siyuedays/php:7.4-fpm