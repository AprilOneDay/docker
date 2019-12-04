# 创建容器
docker run -itd  --name=<mysql8.0> --privileged=true --restart=always -p 3306:3306 <mysql-images>
# 启动全量备份
docker exec -d <mysql8.0> sh /cron-shell/backup.sh
## 备份文件地址 
/mysql_backup 
# 获取mysql密码
docker exec -it <mysql8.0> cat /tmp/mysql_tmp_password.log
# 当无法获取mysql密码的时候尝试手动执行
docker exec -d <mysql8.0> sh /docker-entrypoint-initdb.d/install.sh
# 直接进入mysql
docker exec -it mysql-4 mysql -usiyue -p获取密码
# 当一直无法获取密码时尝试使用初始密码用户登录
docker exec -it mysql-4 mysql -uroot -p123456
# 服务器响应的最大连接数	
show global status like 'Max_used_connections'
# 定时任务每天凌晨3点执行
* * 3 * * ? docker exec -d <mysql8.0> sh /cron-shell/backup.sh

# 创建网络组
docker network create --subnet=172.19.0.0/26 es-network
# 主MYSQL
docker run -itd --name=mysql8.0 --restart=always --privileged=true --network es-network --ip 172.19.0.3 \
	-p 3309:3306 \
	-v /docker/mysql/mysql-8.0.18/conf.d/mysql.cnf:/etc/mysql/conf.d/mysql.cnf \
	-v /docker/mysql/mysql-8.0.18/data/:/var/lib/mysql/ \
	-v /docker/mysql/mysql-8.0.18/backup/:/mysql_backup/ \
	-v /docker/mysql/mysql-8.0.18/log/:/var/log/mysql/  \
	siyuedays/mysql-xtrabackup:8.0.18
# 从MYSQL
docker run -itd --name=mysql8.0-slave-1 --restart=always --privileged=true --network es-network --ip 172.19.0.4 \
	-p 3310:3306 \
	-v /docker/mysql/mysql-8.0.18-slave-1/conf.d/mysql.cnf:/etc/mysql/conf.d/mysql.cnf \
	-v /docker/mysql/mysql-8.0.18-slave-1/data/:/var/lib/mysql/ \
	-v /docker/mysql/mysql-8.0.18-slave-1/backup/:/mysql_backup/ \
	-v /docker/mysql/mysql-8.0.18-slave-1/log/:/var/log/mysql/  \
	siyuedays/mysql-xtrabackup:8.0.18

主从复制
SHOW MASTER STATUS;

CHANGE MASTER TO
MASTER_HOST='172.19.0.3',
MASTER_USER='siyue',
MASTER_PASSWORD='ZDgzZjMyMGNiMWJiMDA3MWYxZjQzODJi',
MASTER_LOG_FILE='mysql-bin.000007',
MASTER_LOG_POS=1414;

START SLAVE;
SHOW SLAVE STATUS;

# MTS 并行复制方式
slave-parallel-type=LOGICAL_CLOCK  # 基于组提交的并行复制方式
slave-parallel-workers=16  # 并行复制测试 开启16个线程 效果最佳
slave_preserve_commit_order=1 # slave的并行复制和master的事务执行的顺序一致
master_info_repository=TABLE # 开启MTS功能后，务必将参数master_info_repostitory设置为TABLE
relay_log_info_repository=TABLE
relay_log_recovery=ON

SHOW VARIABLES LIKE '%slave_para%'


docker run -itd --name=mysql8.0 --restart=always --privileged=true --network es-network --ip 172.19.0.3 \
	-p 3309:3306 \
	-v /docker/mysql/mysql-8.0.18/conf.d/mysql.cnf:/etc/mysql/conf.d/mysql.cnf \
	-v /docker/mysql/mysql-8.0.18/data/:/var/lib/mysql/ \
	-v /docker/mysql/mysql-8.0.18/backup/:/mysql_backup/ \
	-v /docker/mysql/mysql-8.0.18/log/:/var/log/mysql/  \
	mysql:test-2


#错误 Slave failed to initialize relay log info structure from the repository
RESET SLAVE;
START SLAVE IO_THREAD;
STOP SLAVE IO_THREAD;
RESET SLAVE;
START SLAVE;

START SLAVE;
SHOW SLAVE STATUS;

#错误 Slave_SQL_Running：no
STOP SLAVE; 
SET GLOBAL SQL_SLAVE_SKIP_COUNTER=1; 
START SLAVE;     


