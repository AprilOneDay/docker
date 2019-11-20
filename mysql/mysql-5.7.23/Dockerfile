# name:docker_mysql
# use:store  
# date:2017-09-04  
  
FROM mysql:5.7

MAINTAINER siyue 350375092@qq.com  


# 定义会被容器自动执行的目录
ENV AUTO_RUN_DIR /docker-entrypoint-initdb.d
ENV CRON_SHELL /cron-shell

RUN apt-get update

# 安装定时任务
RUN apt-get install -y cron
RUN apt-get install -y dos2unix

# 保证时间一致
COPY /etc/localtime /etc/localtime


# 把要执行的init-shell文件放到/docker-entrypoint-initdb.d/目录下，容器会自动执行这个shell
COPY ./init-shell/ $AUTO_RUN_DIR/
COPY ./cron-shell/ $CRON_SHELL/

# 给执行文件增加可执行权限
RUN chmod a+x $AUTO_RUN_DIR
RUN chmod a+x $CRON_SHELL