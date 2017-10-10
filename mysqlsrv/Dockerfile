# name:docker_mysql
# use:store  
# date:2017-09-04  
  
FROM mysql:5.7

MAINTAINER siyue 350375092@qq.com  

#定义工作目录
ENV WORK_PATH /usr/local/work

#定义会被容器自动执行的目录
ENV AUTO_RUN_DIR /docker-entrypoint-initdb.d

#定义sql文件名
ENV FILE_1 sql.sql

#定义shell文件名
ENV INSTALL_DATA_SHELL install_data.sh

#创建文件夹
RUN mkdir -p $WORK_PATH
#安装定时任务
#RUN apt-get install cron

#把数据库初始化数据的文件复制到工作目录下
COPY ./backup/$FILE_1 $WORK_PATH/

#把要执行的shell文件放到/docker-entrypoint-initdb.d/目录下，容器会自动执行这个shell
COPY ./shell/$INSTALL_DATA_SHELL $AUTO_RUN_DIR/

#给执行文件增加可执行权限
RUN chmod a+x $AUTO_RUN_DIR/$INSTALL_DATA_SHELL