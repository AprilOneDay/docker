# name:docker_nginx
# use:store  
# date:2017-09-04  
  
FROM nginx:latest

MAINTAINER siyue 350375092@qq.com  

# 保证时间一致
COPY /etc/localtime /etc/localtime

# 增加执行权限
RUN mkdir -p  /var/www/html
RUN chmod -R 777 /var/www/html
