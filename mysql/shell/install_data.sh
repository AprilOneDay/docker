#!/bin/bash
mysql -uroot -p$MYSQL_ROOT_PASSWORD <<EOF
source $WORK_PATH/$FILE_1;
use  mysql;
update  user  set  authentication_string  =  password('siyue1q2w3e4r') where  user='siyue';
update  user  set  user  =  'siyue' where  user='root';
EOF
