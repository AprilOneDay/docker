FROM siyuedays/mysql-xtrabackup:8.0.18

MAINTAINER siyue 350375092@qq.com  

# 定义会被容器自动执行的目录
ENV AUTO_RUN_DIR /docker-entrypoint-initdb.d
ENV CRON_SHELL /cron-shell


# 把要执行的init-shell文件放到/docker-entrypoint-initdb.d/目录下，容器会自动执行这个shell
COPY ./init-shell/ $AUTO_RUN_DIR/
COPY ./cron-shell/ $CRON_SHELL/

RUN dos2unix $AUTO_RUN_DIR/install.sh && dos2unix $CRON_SHELL/backup.sh

# 给执行文件增加可执行权限
# RUN chmod a+x $AUTO_RUN_DIR
# RUN chmod a+x $CRON_SHELL