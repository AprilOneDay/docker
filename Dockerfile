# name:SERVER + PHP + APACHE  
# use:store  
# date:2017-09-04  
  
FROM centos  

MAINTAINER siyue 350375092@qq.com 

WORKDIR /root/  

RUN yum -y install httpd php 				# 安装apache，php  ||true 保证整个命令返回true  
RUN yum -y install mysql php-mysqlnd		# 安装mysql客户端 与 php-mysqlnd 

# 创建必要目录 
RUN mkdir -p /www/apache/log/				 
RUN mkdir -p /www/ 
RUN mkdir -p /www/html/  
RUN /usr/local/apache2/bin/httpd 			# 启动apache服务

ENV MYSQL_ADDR 74.121.150.93:3306			# 定义远程mysql地址、用户名和密码 ip为docker ip  
ENV MYSQL_USRR root  
ENV MYSQL_PASS password  
ENV TERM linux  
ENV LC_ALL en_US.UTF-8  


EXPOSE 80 									# 导出apache 80端口  

# 把构建上下文目录httpd.conf，即Dockerfile/centos.bz/apache/conf/httpd.conf文件复制到容器的/usr/local/apach2/conf/httpd.conf
# COPY apache/conf/httpd.conf /usr/local/apach2/conf/httpd.conf

#ADD test.php /var/www/html/test.php 		# 添加测试文件  
ADD run.sh /usr/local/sbin/run.sh 			# 复制服务启动脚本并设置权限

# RUN chmod u+x /root/run.sh  

CMD ["/usr/local/sbin/run.sh"]