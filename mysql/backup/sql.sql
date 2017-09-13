/*
SQLyog Enterprise v12.09 (64 bit)
MySQL - 5.5.53 : Database - denha
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`denha` /*!40100 DEFAULT CHARACTER SET gbk */;

USE `denha`;

/*Table structure for table `dh_article` */

DROP TABLE IF EXISTS `dh_article`;

CREATE TABLE `dh_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型',
  `tag` varchar(20) DEFAULT NULL COMMENT 'tag标签',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `thumb` varchar(255) DEFAULT '' COMMENT '缩略图',
  `description` varchar(255) DEFAULT '' COMMENT '简介',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '显示状态 1显示 0关闭',
  `del_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '删除状态 1删除 0未删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `hot` int(11) DEFAULT '0' COMMENT '热度',
  `is_recommend` tinyint(1) DEFAULT '0' COMMENT '推荐排行榜 1推荐 0不推荐',
  PRIMARY KEY (`id`),
  KEY `is_show` (`is_show`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `dh_article` */

insert  into `dh_article`(`id`,`type`,`tag`,`title`,`thumb`,`description`,`is_show`,`del_status`,`created`,`hot`,`is_recommend`) values (1,1,'6','Nginx 实现跨域使用字体文件','d32b6491067a0e25561eb4d192243d7e.jpeg','Nginx 实现跨域使用字体文件',1,0,1504970134,0,1),(2,1,'6','Nginx 跨域访问php  ','','Access-Control-Allow-Origin 错误',1,0,1504970371,0,0),(3,1,'10','Html 文字内容只显示一行','','&lt;ul&gt;&nbsp;&nbsp&nbsp;&nbsp&nbsp;&lt;li&gt;&lt;a&nbsphref=\"javascript:;\"&gt;餐馆&lt;/a&gt;&lt;/li&gt;&lt;/ul&gt;css:li{&nbsp;white-space:nowrap;&nbsp;&nbsp;overflow:hidden;&nbsp;text-overflow:ellipsis;}',1,0,1505020972,0,0);

/*Table structure for table `dh_article_blog` */

DROP TABLE IF EXISTS `dh_article_blog`;

CREATE TABLE `dh_article_blog` (
  `id` int(10) unsigned NOT NULL,
  `content` mediumtext COMMENT '博客内容',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `dh_article_blog` */

insert  into `dh_article_blog`(`id`,`content`) values (1,'<div>location ~* .(eot|ttf|woff|woff2|svg|otf)$ {</div><div>&nbsp; &nbsp; &nbsp; &nbsp; add_header Access-Control-Allow-Origin http://dist.denha.loc;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; #add_header Access-Control-Allow-Headers X-Requested-With;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; #add_header Access-Control-Allow-Credentials true;&nbsp;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; add_header Access-Control-Allow-Methods GET;</div><div>}</div>'),(2,'<div>location ~ .php(.*)$ {</div><div>&nbsp; &nbsp; &nbsp; &nbsp; add_header Access-Control-Allow-Origin http://dist.denha.loc;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; add_header Access-Control-Allow-Headers X-Requested-With;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; add_header Access-Control-Allow-Credentials true;&nbsp;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; add_header Access-Control-Allow-Methods GET,POST;</div><div>}</div>'),(3,'<div><div style=\"\"><font face=\"Microsoft YaHei\">&lt;ul&gt;</font></div><div style=\"\"><font face=\"Microsoft YaHei\">&nbsp; &nbsp; &nbsp;&lt;li&gt;&lt;a href=\"javascript:;\"&gt;餐馆&lt;/a&gt;&lt;/li&gt;</font></div><div style=\"\"><span style=\"font-family: &quot;Microsoft YaHei&quot;;\">&lt;/ul&gt;</span><br></div></div><span style=\"font-family: &quot;Microsoft YaHei&quot;;\"><div><span style=\"font-family: &quot;Microsoft YaHei&quot;;\"><br></span></div>css:</span><div>li{<br style=\"font-family: &quot;Microsoft YaHei&quot;;\"><span style=\"font-family: &quot;Microsoft YaHei&quot;;\">&nbsp;white-space:nowrap;&nbsp;</span><br style=\"font-family: &quot;Microsoft YaHei&quot;;\"><span style=\"font-family: &quot;Microsoft YaHei&quot;;\">&nbsp;overflow:hidden;</span><br style=\"font-family: &quot;Microsoft YaHei&quot;;\"><span style=\"font-family: &quot;Microsoft YaHei&quot;;\">&nbsp;text-overflow:ellipsis;</span></div><div><span style=\"font-family: &quot;Microsoft YaHei&quot;;\">}</span></div>');

/*Table structure for table `dh_console_admin` */

DROP TABLE IF EXISTS `dh_console_admin`;

CREATE TABLE `dh_console_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `consoleid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '后台用户唯一id',
  `nickname` varchar(10) DEFAULT '' COMMENT '昵称',
  `username` varchar(20) NOT NULL DEFAULT '' COMMENT '登录用户',
  `password` char(128) NOT NULL DEFAULT '' COMMENT 'md5登录密码',
  `salt` char(5) NOT NULL DEFAULT '0' COMMENT '唯一码',
  `mobile` char(11) NOT NULL DEFAULT '0' COMMENT '手机号',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1开启 0关闭',
  `group` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '所属管理组',
  `create_ip` varchar(15) DEFAULT '0' COMMENT '创建ip',
  `login_ip` varchar(15) DEFAULT '0' COMMENT '登录ip',
  `created` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '创建时间',
  `login_time` int(10) DEFAULT '0' COMMENT '登录时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='后台管理员';

/*Data for the table `dh_console_admin` */

insert  into `dh_console_admin`(`id`,`consoleid`,`nickname`,`username`,`password`,`salt`,`mobile`,`status`,`group`,`create_ip`,`login_ip`,`created`,`login_time`) values (1,0,'四月','admin','8895c4947031a4019843c0d00fa303b1','50907','15923882847',1,1,'127.0.0.1','127.0.0.1',1502522576,1505009882),(4,0,'陈明江','cmj','96c76c67a66e92c1e90bce05ebec4b5d','34366','15923882847',1,1,'127.0.0.1','0',1502531990,0);

/*Table structure for table `dh_console_menus` */

DROP TABLE IF EXISTS `dh_console_menus`;

CREATE TABLE `dh_console_menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '类型 1网站管理',
  `parentid` int(11) NOT NULL DEFAULT '0' COMMENT '父级ID',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `module` varchar(50) NOT NULL DEFAULT '' COMMENT '模块名称',
  `controller` varchar(50) NOT NULL DEFAULT '' COMMENT '控制器名称',
  `action` varchar(50) NOT NULL DEFAULT '' COMMENT '方法名称',
  `icon` varchar(60) DEFAULT '' COMMENT 'Icon图标样式',
  `parameter` varchar(20) DEFAULT '' COMMENT '附加参数',
  `url` varchar(150) DEFAULT '' COMMENT '请求地址',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '是否有效 1有效 0无效',
  `is_show` tinyint(1) unsigned DEFAULT '1' COMMENT '是否显示 1显示 0不显示',
  `is_white` tinyint(1) unsigned DEFAULT '0' COMMENT '是否白名单 1白名单 0不进白名单',
  `sort` int(10) unsigned DEFAULT '0' COMMENT '排序',
  `del_status` tinyint(1) DEFAULT '0' COMMENT '删除状态 1删除 0未删除',
  `created` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

/*Data for the table `dh_console_menus` */

insert  into `dh_console_menus`(`id`,`type`,`parentid`,`name`,`module`,`controller`,`action`,`icon`,`parameter`,`url`,`status`,`is_show`,`is_white`,`sort`,`del_status`,`created`) values (2,1,1,'设置','setting','menus','index','glyphicon glyphicon-wrench','','/console/setting/menus/index',1,1,0,0,0,1502508402),(3,1,2,'配置菜单','setting','menus','index','','','/console/setting/menus/index',1,1,0,0,0,1502508459),(4,1,3,'添加/编辑菜单','setting','menus','edit','','','',1,0,0,0,0,1502440822),(5,1,3,'树状菜单列表','setting','menus','tree_list','','','',1,0,0,0,0,1502440812),(6,1,2,'管理员','setting','admin','index','','','/console/setting/admin/index',1,1,0,0,0,1502516144),(7,1,6,'管理员列表','setting','admin','index','','','/console/setting/admin/index',1,1,0,0,0,1502517276),(8,1,6,'管理员分组','setting','group','index','','','/console/setting/group/index',1,1,0,0,0,1502517279),(1,1,0,'系统管理','setting','menus','index','glyphicon glyphicon-triangle-right','','',1,1,0,0,0,1502445648),(10,1,0,'内容管理','content','list','index','glyphicon glyphicon-triangle-right','','/console/content/list/index',1,1,0,0,0,1502702794),(11,1,10,'博客','content','list','index','glyphicon glyphicon-book','','/console/content/list/index',1,1,0,0,0,1502702886),(12,1,11,'文章列表','content','list','index','','','/console/content/list/index',1,1,0,0,0,1502702814);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
