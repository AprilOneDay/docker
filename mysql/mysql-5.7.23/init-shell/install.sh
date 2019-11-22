#!/bin/bash
# 更换用户 密码
mysql -uroot -p$MYSQL_ROOT_PASSWORD <<EOF
use  mysql;
update  user  set  authentication_string  =  password('siyue1q2w3e4r') where  user='root';
update  user  set  user  =  'siyue' where  user='root';
GRANT REPLICATION SLAVE ON *.* TO 'siyue'@'%' IDENTIFIED BY 'siyue1q2w3e4r';
GRANT REPLICATION SLAVE ON *.* TO 'siyue'@'localhost' IDENTIFIED BY 'siyue1q2w3e4r';
GRANT BACKUP_ADMIN ON *.* TO siyue@'%';
GRANT BACKUP_ADMIN ON *.* TO siyue@'localhost';
FLUSH PRIVILEGES;
EOF
service mysql restart;


