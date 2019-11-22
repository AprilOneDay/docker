# 创建用户
INSERT INTO mysql.user(HOST,USER,authentication_string) VALUES("%","beta",PASSWORD("lvgVcNFvEk"));
# 为用户授权
GRANT ALL PRIVILEGES ON kljgj_beta.* TO "beta"@"%" IDENTIFIED BY 'lvgVcNFvEk';
# 删除用户
DROP USER beta@localhost;
# 刷新权限
FLUSH PRIVILEGES;
# 查询用户表
SELECT HOST,USER,authentication_string FROM mysql.user
# 查询当前用户
SELECT USER();
# 容器内检查cron 是否执行
/etc/init.d/crond status 或者 /etc/init.d/cron status
# 启动cron
/etc/init.d/cron restart


docker build -t mysql:8.0 .
docker run -d -t -v /var/cowrie:/data/ -p 22:2222 --restart=always  --name cowrie_auto xxxxx /xx.sh
--name cowrie_auto 启动容器自动运行脚本


docker run -itd  --name mysql-3 --privileged=true --restart=always -p 3309:3306  -e MYSQL_ROOT_PASSWORD=123456 mysql:test-3  /bin/bash -c -c 'sh /cron-shell/init.sh'

xtrabackup --defaults-file=/etc/mysql/conf.d/mysql.cnf --user=siyue --password=siyue1q2w3e4r --backup --parallel=3 --target-dir=/home/backup

/usr/bin/mysqldump –defaults-extra-file=/etc/mysql/conf.d/mysql.cnf --all-databases -usiyue -psiyue1q2w3e4r  | gzip > /var/lib/mysql/back.sql.gz