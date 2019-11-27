#!/bin/bash
# 更换用户 密码
OLDPASSWORD=$MYSQL_ROOT_PASSWORD
# 随机生成密码
PASSWORD=`tr -dc '_A-Za-z0-9' </dev/urandom  | head -c 32`
# 保存密码记录
echo $PASSWORD > /tmp/mysql_tmp_password.log
unset MYSQL_ROOT_PASSWORD

mysql -uroot -p$OLDPASSWORD <<EOF
use  mysql;
update user set user = 'siyue' where user='root';
FLUSH PRIVILEGES;
ALTER USER 'siyue'@'localhost' IDENTIFIED WITH mysql_native_password BY '${PASSWORD}';
FLUSH PRIVILEGES;
ALTER USER 'siyue'@'%' IDENTIFIED WITH mysql_native_password BY '${PASSWORD}';
FLUSH PRIVILEGES;
GRANT REPLICATION SLAVE ON *.* TO 'siyue'@'%';
FLUSH PRIVILEGES;
GRANT BACKUP_ADMIN ON *.* TO siyue@'localhost';
FLUSH PRIVILEGES;
EOF