# name:cmj_jenkins

# use:store  
# date:2017-09-04  
  
FROM jenkins:latest

MAINTAINER siyue 350375092@qq.com  

# 保证时间一致
COPY /etc/localtime /etc/localtime

# 让jenkins容器中具有执行sudo命令的权限
USER root


RUN apt-get update && apt-get install -y \ 
		zip \
		unzip \
		sudo \
		php-cli \
		php-xml \
		re2c \
	--no-install-recommends && rm -rf /var/lib/apt/lists/*

RUN echo "jenkins ALL=NOPASSWD: ALL" >> /etc/sudoers

USER jenkins
