#!/bin/bash  
mysqld_safe &  
sleep 3  
mysql -e "GRANT ALL PRIVILEGES ON *.* TO '$MYSQL_USER'@'%' IDENTIFIED BY '$MYSQL_PASS' WITH GRANT OPTION;"<span style="white-space:pre">      </span>#授权  
mysql -e "create database scores"<span style="white-space:pre">   </span>#创建scores数据库<span style="white-space:pre">   </span>  
mysql -e "create table scores.name_score(name char(20) not null,score int not null)DEFAULT CHARSET=utf8"<span style="white-space:pre">    </span>#创建name_score表格  
mysql -e "insert into scores.name_score values ('李明',80),('张军',90),('王小二',95)"  