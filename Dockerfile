# name:SERVER + PHP + APACHE  
# use:store  
# date:2017-09-04  
  
FROM centos  

MAINTAINER siyue 350375092@qq.com  

WORKDIR /root/ 

RUN yum -y install httpd php || true        # 安装apache，php  ||true 保证整个命令返回true  
RUN yum -y install mysql php-mysqlnd        # 安装mysql客户端 与 php-mysqlnd  
RUN mkdir /var/log/httpd1           		# 创建必要目录  
RUN mkdir /var/www/      
RUN mkdir /var/www/html/  

ENV MYSQL_ADDR 172.17.0.36:3306     		# 定义远程mysql地址、用户名和密码 ip为docker ip  
ENV MYSQL_USRR test  
ENV MYSQL_PASS password  
ENV TERM linux  
ENV LC_ALL en_US.UTF-8  
ADD test.php /var/www/html/test.php # 添加测试文件  
EXPOSE 80               # 导出apache 80端口  
ADD run.sh /root/run.sh     # 添加启动脚本  
RUN chmod u+x /root/run.sh  
CMD /root/run.sh  

