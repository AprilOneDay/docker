#name:Mydql<span style="white-space:pre"> </span>  
#use:store  
#date:2016-6-4  
  
FROM centos<span style="white-space:pre">     </span>#基础镜像  
MAINTAINER niufeiy niufeiy@gmail.com<span style="white-space:pre">        </span>#声明维护者信息  
WORKDIR /root/<span style="white-space:pre">          </span>#工作目录  
RUN yum -y install wget<span style="white-space:pre">             </span>#centos7默认没有mysql源，需要下载并安装  
RUN wget http://repo.mysql.com/mysql-community-release-el7-5.noarch.rpm<span style="white-space:pre">     </span>  
RUN rpm -ivh /root/mysql-community-release-el7-5.noarch.rpm  
RUN yum -y install mysql-server<span style="white-space:pre">         </span>#安装mysql服务  
RUN mysql_install_db --user=mysql<span style="white-space:pre">       </span>#创建数据库  
ENV MYSQL_USER test<span style="white-space:pre">         </span>#用户名  
ENV MYSQL_PASS password<span style="white-space:pre">         </span>#密码  
ENV LC_ALL en_US.UTF-8<span style="white-space:pre">      </span>#中文支持  
ADD build_table.sh /root/build_table.sh<span style="white-space:pre">     </span>#利用脚本创建数据库和表  
RUN chmod u+x /root/build_table.sh<span style="white-space:pre">  </span>#给执行权限  
RUN /root/build_table.sh<span style="white-space:pre">    </span>  
EXPOSE 3306<span style="white-space:pre">         </span>#导出3306端口  
ADD run.sh /root/run.sh<span style="white-space:pre">     </span>#脚本启动  
RUN chmod u+x /root/run.sh  
CMD /root/run.sh  