# name:SERVER + PHP + APACHE  
# use:store  
# date:2017-09-04  
  
FROM centos  

MAINTAINER siyue 350375092@qq.com  

ENV MYSQL_ADDR 172.17.0.36:3306     		# 定义远程mysql地址、用户名和密码 ip为docker ip  
ENV MYSQL_USRR test  
ENV MYSQL_PASS password  
ENV TERM linux  
ENV LC_ALL en_US.UTF-8  

WORKDIR /root/ 

RUN yum -y install httpd php || true        # 安装apache，php  ||true 保证整个命令返回true  
RUN yum -y install mysql php-mysqlnd        # 安装mysql客户端 与 php-mysqlnd  

# 创建必要目录  
RUN mkdir -p  /var/log/httpd \           		
RUN mkdir -p  /var/www/ \      
RUN mkdir -p  /var/www/html/ \  
RUN cd /var

# 导出apache 80端口  
EXPOSE 80               	

COPY run.sh ./run.sh     # 添加启动脚本  

RUN chmod +x run.sh  

CMD ["run.sh"]

