#!/bin/bash
# 更换用户 密码
mysql -uroot -p$MYSQL_ROOT_PASSWORD <<EOF
use  mysql;
update  user  set  authentication_string  =  password('siyue1q2w3e4r') where  user='root';
update  user  set  user  =  'siyue' where  user='root';
GRANT REPLICATION SLAVE ON *.* TO 'siyuemaster'@'%' IDENTIFIED BY 'cheng6251';
EOF
service mysql restart;