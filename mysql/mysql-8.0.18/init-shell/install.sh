#!/bin/bash
# 更换用户 密码
mysql -uroot -p$MYSQL_ROOT_PASSWORD <<EOF
use  mysql;
update user set authentication_string = password('siyue1q2w3e4r') where user='root';
update user set user = 'siyue' where user='root';
GRANT REPLICATION SLAVE ON *.* TO 'siyue'@'%' IDENTIFIED BY 'siyue1q2w3e4r';
ALTER USER 'siyue'@'%' IDENTIFIED WITH mysql_native_password BY 'siyue1q2w3e4r';
EOF
service mysql restart;


