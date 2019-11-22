FROM mysql:8.0.18

MAINTAINER siyue 350375092@qq.com  

# 定义会被容器自动执行的目录
ENV AUTO_RUN_DIR /docker-entrypoint-initdb.d
ENV CRON_SHELL /cron-shell

# 替换成阿里云地址
COPY ./source.list /etc/apt/sources.list

# 保证时间一致
COPY ./etc/localtime /etc/localtime

RUN apt-get update && apt-get install -y wget && apt-get install -y sudo 

# 定时任务
RUN apt-get install -y cron && apt-get install -y dos2unix 

# xtrabackup
RUN apt-get install libev4 -y && apt-get install -y libcurl4-openssl-dev &&  apt-get install -y libdbd-mysql-perl &&  apt-get install -y rsync && wget https://www.percona.com/downloads/Percona-XtraBackup-LATEST/Percona-XtraBackup-8.0-7/binary/debian/stretch/x86_64/percona-xtrabackup-80_8.0.7-1.stretch_amd64.deb && dpkg -i percona-xtrabackup-80_8.0.7-1.stretch_amd64.deb
# apt-get remove percona-xtrabackup-80 -y 卸载
# xtrabackup --version

# 清理文件
RUN  rm -rf /var/lib/apt/lists/*  && apt-get clean

# 把要执行的init-shell文件放到/docker-entrypoint-initdb.d/目录下，容器会自动执行这个shell
COPY ./init-shell/ $AUTO_RUN_DIR/
COPY ./cron-shell/ $CRON_SHELL/

RUN dos2unix $AUTO_RUN_DIR/install.sh && dos2unix $AUTO_RUN_DIR/init-cron.sh

# 给执行文件增加可执行权限
# RUN chmod a+x $AUTO_RUN_DIR
# RUN chmod a+x $CRON_SHELL