#!/bin/bash
#作为crontab运行的脚本,需特别注意环境变量问题,指令写成绝对路径

#读取环境变量
#. /etc/profile
#如果目录不存在则新建
DIR=/var/lib/mysql/backup
if [ ! -e $DIR ]
then
/bin/mkdir -p $DIR
fi

#将所有数据库导出并按日期命名保存成sql文件并压缩
/usr/bin/xtrabackup --defaults-file=/etc/mysql/conf.d/mysql.cnf --user=siyue --password=siyue1q2w3e4r --backup --parallel=3 --target-dir=$DIR/data_`date +%Y%m%d

#查找更改时间在30日以前的sql备份文件并删除
/usr/bin/find $DIR -mtime +30  -name "data_[1-9]*" -exec rm -rf {} \;