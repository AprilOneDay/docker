# name:SERVER + PHP + APACHE  
# use:store  
# date:2017-09-04  

FROM centos  

MAINTAINER siyue 350375092@qq.com 

# 安装wget
RUN yum install -y wget

WORKDIR /usr/local/src

# 下载并解压源码包
RUN wget http://apache.fayea.com/httpd/httpd-2.4.17.tar.gz
RUN tar -zxvf httpd-2.4.17.tar.gz

WORKDIR httpd-2.4.17

# 编译安装apache
RUN yum install -y gcc make apr-devel apr apr-util apr-util-devel pcre-devel 
RUN ./configure --prefix=/usr/local/apache2 --enable-mods-shared=most --enable-so
RUN make
RUN make install
RUN sed -i 's/#ServerName www.example.com:80/ServerName localhost:80/g' /usr/local/apache2/conf/httpd.conf # 修改apache配置文件
RUN /usr/local/apache2/bin/httpd # 启动apache服务

ADD run.sh /usr/local/sbin/run.sh # 复制服务启动脚本并设置权限
RUN chmod 755 /usr/local/sbin/run.sh

EXPOSE 80 # 导出apache 80端口  
CMD ["/usr/local/sbin/run.sh"]