FROM mysql:8.0.18

MAINTAINER siyue 350375092@qq.com  

# 定义会被容器自动执行的目录
ENV AUTO_RUN_DIR /docker-entrypoint-initdb.d
ENV CRON_SHELL /cron-shell

COPY ./source.list /etc/apt/repositories

# 保证时间一致
COPY ./etc/localtime /etc/localtime

RUN apt-get update

RUN apt-get install -y cron && apt-get install -y dos2unix 

wget https://www.percona.com/downloads/XtraBackup/Percona-XtraBackup-8.0.4/binary/debian/stretch/x86_64/percona-xtrabackup-80_8.0.4-1.stretch_amd64.deb && dpkg -i percona-xtrabackup-80_0.4-1.stretch_amd64.deb
# RUN apt-get install -y percona-xtrabackup

# 把要执行的init-shell文件放到/docker-entrypoint-initdb.d/目录下，容器会自动执行这个shell
COPY ./init-shell/ $AUTO_RUN_DIR/
COPY ./cron-shell/ $CRON_SHELL/

# 给执行文件增加可执行权限
RUN chmod a+x $AUTO_RUN_DIR
RUN chmod a+x $CRON_SHELL