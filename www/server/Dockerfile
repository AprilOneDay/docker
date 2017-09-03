#name:PHP + APACHE  
#use:store  
#date:2016-6-4  
  
FROM centos  
MAINTAINER niufeiy niufeiy@gmail.com  
WORKDIR /root/  
RUN yum -y install httpd php || true<span style="white-space:pre">        </span>#安装apache，php  ||true 保证整个命令返回true  
RUN yum -y install mysql php-mysqlnd<span style="white-space:pre">        </span>#安装mysql客户端 与 php-mysqlnd  
RUN mkdir /var/log/httpd1<span style="white-space:pre">           </span>#创建必要目录  
RUN mkdir /var/www1/<span style="white-space:pre">    </span>  
RUN mkdir /var/www1/html/  
ENV MYSQL_ADDR 172.17.0.36:3306<span style="white-space:pre">     </span>#定义远程mysql地址、用户名和密码 ip为docker ip  
ENV MYSQL_USRR test  
ENV MYSQL_PASS password  
ENV TERM linux  
ENV LC_ALL en_US.UTF-8  
ADD test.php /var/www/html/test.php<span style="white-space:pre"> </span>#添加测试文件  
EXPOSE 80<span style="white-space:pre">               </span>#导出apache 80端口  
ADD run.sh /root/run.sh<span style="white-space:pre">     </span>#添加启动脚本  
RUN chmod u+x /root/run.sh  
CMD /root/run.sh  