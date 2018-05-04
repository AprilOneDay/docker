# 安装docker
yum -y install docker 

# 安装docker-compose

## 第一种方法
sudo curl -L https://github.com/docker/compose/releases/download/1.17.0/docker-compose-`uname -s`-`uname -m` -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

## 第二种方法
yum -y install epel-release
yum -y install python-pip
pip install docker-compose

# 查询docker-compose版本的命令
docker-compose version

# docker开启启动
systemctl  enable docker.service

# 启动docker
systemctl start docker

# docker国内镜像加速
vi /etc/docker/daemon.json
{
  "registry-mirrors": ["https://he7u0ka4.mirror.aliyuncs.com"]
}

# 进入文件路径
# 启动docker-compose
docker-compose up

# 关闭所有正在运行容器
docker ps | awk  '{print $1}' | xargs docker stop
docker rm $(docker ps -a -q)
docker rmi docker_nginx

# docker复制本地
docker cp apache2:/root/etc//local/apache2/conf/httpd.conf ~/www/apache2/conf/httpd.conf
docker cp apache2:/usr/local/apache2/logs/ ~/www/apache2/logs
docker cp apache2:/usr/local/apache2/htdocs/ ~/www/src

# 从宿主机拷文件到docker里面
docker cp ~/www/nginx/conf/mime.types nginx:/etc/nginx/mime.types

# 修改写入权限
docker exec -it php /bin/bash
chmod -R 777 /var/www/html/data
chmod -R 777 /var/www/html/public/uploadfile
chmod -R 777 /var/www/html/appliaction/admin/tools/var
exit

# 修改mysql密码
docker exec -it mysql /bin/bash
mysql -uroot -p1qq2ww3ee4rr
use  mysql;
update  user  set  authentication_string  =  password('cnwtoo_kdqc123') where  user='root';
update  user  set  user  =  'kdqc' where  user='root';
exit;exit;
docker restart mysql;
docker restart nginx;

docker-compose up -d
docker exec -it mysql /bin/bash
mysqldump -hlocalhost -usiyue -psiyue1q2w3e4r --databases blog > /usr/local/work/blog.sql


# docker mysql主从
docker run --name mysqlsrv -v ~/docker/mysqlsrv/data:/var/lib/mysql -v ~/docker/mysqlsrv/conf/conf.d:/etc/mysql/conf.d -e MYSQL_ROOT_PASSWORD=123456 -p 3316:3306 mysql:5.7

CHANGE MASTER TO MASTER_HOST='74.121.150.93',MASTER_USER='siyue',MASTER_PASSWORD='siyue1q2w3e4r',MASTER_LOG_FILE='mysql-bin.000004',MASTER_LOG_POS=2552;

# linux 端口查看
lsof -i tcp:443

# 查看Linux系统版本的命令
cat /etc/issue
uname -a

# 查看所有用户的列表
cat /etc/passwd

# 查看用户组
cat /etc/group 