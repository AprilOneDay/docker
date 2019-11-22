#!/bin/bash
# 更换用户 密码
mysql -uroot -p$MYSQL_ROOT_PASSWORD <<EOF
use  mysql;
update user set user = 'siyue' where user='root';
FLUSH PRIVILEGES;
ALTER USER 'siyue'@'localhost' IDENTIFIED WITH mysql_native_password BY 'siyue1q2w3e4r';
FLUSH PRIVILEGES;
ALTER USER 'siyue'@'%' IDENTIFIED WITH mysql_native_password BY 'siyue1q2w3e4r';
FLUSH PRIVILEGES;
GRANT BACKUP_ADMIN ON *.* TO siyue@'localhost';
FLUSH PRIVILEGES;
EOF
# service mysql restart;
# GRANT REPLICATION SLAVE ON *.* TO 'siyue'@'%' IDENTIFIED BY 'siyue1q2w3e4r';
# FLUSH PRIVILEGES;