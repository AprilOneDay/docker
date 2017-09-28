/*
SQLyog Professional v12.09 (64 bit)
MySQL - 5.5.53 : Database - koudaiche
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`blog` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `blog`;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `dh_article` */

insert  into `dh_article`(`id`,`type`,`tag`,`title`,`thumb`,`description`,`is_show`,`del_status`,`created`,`hot`,`is_recommend`) values (1,1,'6','Nginx 实现跨域使用字体文件','d32b6491067a0e25561eb4d192243d7e.jpeg','Nginx 实现跨域使用字体文件',1,0,1504970134,1,1),(2,1,'6','Nginx 跨域访问php  ','','Access-Control-Allow-Origin 错误',1,0,1504970371,1,0),(3,1,'10','Html 文字内容只显示一行','','&lt;ul&gt;&nbsp;&nbsp&nbsp;&nbsp&nbsp;&lt;li&gt;&lt;a&nbsphref=\"javascript:;\"&gt;餐馆&lt;/a&gt;&lt;/li&gt;&lt;/ul&gt;css:li{&nbsp;white-space:nowrap;&nbsp;&nbsp;overflow:hidden;&nbsp;text-overflow:ellipsis;}',1,0,1505020972,51,0);

/*Table structure for table `dh_article_blog` */

DROP TABLE IF EXISTS `dh_article_blog`;

CREATE TABLE `dh_article_blog` (
  `id` int(10) unsigned NOT NULL,
  `content` mediumtext COMMENT '博客内容',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `dh_article_blog` */

insert  into `dh_article_blog`(`id`,`content`) values (1,'<div>location ~* .(eot|ttf|woff|woff2|svg|otf)$ {</div><div>&nbsp; &nbsp; &nbsp; &nbsp; add_header Access-Control-Allow-Origin http://dist.denha.loc;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; #add_header Access-Control-Allow-Headers X-Requested-With;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; #add_header Access-Control-Allow-Credentials true;&nbsp;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; add_header Access-Control-Allow-Methods GET;</div><div>}</div>'),(2,'<div>location ~ .php(.*)$ {</div><div>&nbsp; &nbsp; &nbsp; &nbsp; add_header Access-Control-Allow-Origin http://dist.denha.loc;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; add_header Access-Control-Allow-Headers X-Requested-With;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; add_header Access-Control-Allow-Credentials true;&nbsp;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; add_header Access-Control-Allow-Methods GET,POST;</div><div>}</div>'),(3,'<div><div style=\"\"><font face=\"Microsoft YaHei\">&lt;ul&gt;</font></div><div style=\"\"><font face=\"Microsoft YaHei\">&nbsp; &nbsp; &nbsp;&lt;li&gt;&lt;a href=\"javascript:;\"&gt;餐馆&lt;/a&gt;&lt;/li&gt;</font></div><div style=\"\"><span style=\"font-family: &quot;Microsoft YaHei&quot;;\">&lt;/ul&gt;</span><br></div></div><span style=\"font-family: &quot;Microsoft YaHei&quot;;\"><div><span style=\"font-family: &quot;Microsoft YaHei&quot;;\"><br></span></div>css:</span><div>li{<br style=\"font-family: &quot;Microsoft YaHei&quot;;\"><span style=\"font-family: &quot;Microsoft YaHei&quot;;\">&nbsp;white-space:nowrap;&nbsp;</span><br style=\"font-family: &quot;Microsoft YaHei&quot;;\"><span style=\"font-family: &quot;Microsoft YaHei&quot;;\">&nbsp;overflow:hidden;</span><br style=\"font-family: &quot;Microsoft YaHei&quot;;\"><span style=\"font-family: &quot;Microsoft YaHei&quot;;\">&nbsp;text-overflow:ellipsis;</span></div><div><span style=\"font-family: &quot;Microsoft YaHei&quot;;\">}</span></div>');

/*Table structure for table `dh_banner` */

DROP TABLE IF EXISTS `dh_banner`;

CREATE TABLE `dh_banner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(25) DEFAULT NULL COMMENT '标题',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `dh_banner` */

insert  into `dh_banner`(`id`,`title`) values (1,'首页顶部广告');

/*Table structure for table `dh_banner_data` */

DROP TABLE IF EXISTS `dh_banner_data`;

CREATE TABLE `dh_banner_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `banner_id` int(11) DEFAULT '0' COMMENT 'banner表主键',
  `path` varchar(100) DEFAULT NULL COMMENT '图片地址',
  `description` varchar(500) DEFAULT NULL COMMENT '图片介绍',
  `sort` int(10) unsigned DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

/*Data for the table `dh_banner_data` */

insert  into `dh_banner_data`(`id`,`banner_id`,`path`,`description`,`sort`) values (1,1,'609b1db7577e074aabc497f297cb0e71.jpeg','这里是简介哦哦哦',0),(2,1,'4eb18891b7bff8e89da7bcda5e3b6741.jpeg','1111',0),(3,1,'43bc6adcffe0be147572626263455e2d.jpeg','2222',0);

/*Table structure for table `dh_category` */

DROP TABLE IF EXISTS `dh_category`;

CREATE TABLE `dh_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parentid` int(11) unsigned DEFAULT '0' COMMENT '父级id',
  `thumb` varchar(100) DEFAULT '' COMMENT '缩略图',
  `name` varchar(20) DEFAULT '' COMMENT '分类名称',
  `sort` int(10) unsigned DEFAULT '0' COMMENT '排序',
  `is_show` tinyint(1) unsigned DEFAULT '1' COMMENT '显示状态 1显示 0不显示',
  `created` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COMMENT='分类表\r\n';

/*Data for the table `dh_category` */

insert  into `dh_category`(`id`,`parentid`,`thumb`,`name`,`sort`,`is_show`,`created`) values (1,0,'','汽车品牌',0,1,0),(2,1,'','奥迪',1,1,0),(3,1,'3e4daf03cb497d10e0267b0f7b7de7df.png','大众',0,1,0),(4,0,'','服务类型',0,1,0),(5,4,'','汽车贴膜',0,1,0),(6,4,'','汽车维修',0,1,0),(7,4,'','汽车保养',0,1,0),(8,0,'','城市',0,1,0),(9,8,'','Toronto',0,1,0),(10,8,'','North-York',0,1,0),(11,8,'','Downtown',0,1,0),(12,8,'','Markham',0,1,0),(13,8,'','Vaughan',0,1,0),(14,8,'','Scarborough',0,1,0),(15,8,'','Brampton',0,1,0),(16,8,'','Mississauga',0,1,0),(17,8,'','Richmond-hill',0,1,0),(18,8,'','Newmarket',0,1,0),(19,0,'','店铺分类',0,1,0),(20,19,'','汽车贴膜',0,1,0),(21,19,'','汽车改装',0,1,0),(22,19,'','汽车清洗',0,1,0),(23,19,'','汽车售卖',0,1,0),(24,19,'','汽车保养',0,1,0);

/*Table structure for table `dh_chat_log` */

DROP TABLE IF EXISTS `dh_chat_log`;

CREATE TABLE `dh_chat_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned DEFAULT '0' COMMENT '发送yud',
  `to_uid` int(10) unsigned DEFAULT '0' COMMENT '接收uid',
  `content` varchar(300) DEFAULT '' COMMENT '消息内容',
  `is_reader` tinyint(1) unsigned DEFAULT '0' COMMENT '接收者是否已读 1已读 0未读',
  `created` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `to_uid` (`to_uid`),
  KEY `is_reader` (`is_reader`),
  KEY `created` (`created`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='聊天记录';

/*Data for the table `dh_chat_log` */

insert  into `dh_chat_log`(`id`,`uid`,`to_uid`,`content`,`is_reader`,`created`) values (1,2,3,'这是我的回复信息',1,1506585491),(2,2,3,'这是我的回复信息',1,1506585529);

/*Table structure for table `dh_circle` */

DROP TABLE IF EXISTS `dh_circle`;

CREATE TABLE `dh_circle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '类型 1汽车',
  `title` varchar(150) DEFAULT '' COMMENT '标题',
  `thumb` varchar(100) DEFAULT '' COMMENT '封面图片',
  `uid` int(10) unsigned DEFAULT '0' COMMENT '用户id',
  `description` mediumtext COMMENT '详情',
  `ablum` varchar(500) DEFAULT '' COMMENT '相册',
  `created` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `del_status` tinyint(1) DEFAULT '0' COMMENT '删除状态 1删除  0未删除',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态 1正常 0禁用',
  PRIMARY KEY (`id`),
  KEY `created` (`created`),
  KEY `del_status` (`del_status`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `dh_circle` */

insert  into `dh_circle`(`id`,`type`,`title`,`thumb`,`uid`,`description`,`ablum`,`created`,`del_status`,`status`) values (1,1,'','150632458755821.png',4,'','150632458755821.png,150632458712977.png',1506324587,0,1),(2,1,'','',4,'','',1506390246,0,1),(3,1,'','',4,'来点文字介绍了了了 ','',1506390269,0,1),(4,1,'','150639074685682.png',2,'我和你一样','150639074685682.png,150639074689516.png,150639074681938.png',1506390746,0,1),(5,1,'','150658772943247.jpeg',2,'不要说话','150658772943247.jpeg,150658772976354.jpeg,150658772972256.jpeg,150658772927363.jpeg',1506587729,0,1);

/*Table structure for table `dh_collection` */

DROP TABLE IF EXISTS `dh_collection`;

CREATE TABLE `dh_collection` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned DEFAULT '0' COMMENT '用户名',
  `type` tinyint(4) DEFAULT '0' COMMENT '类型 1汽车 2服务',
  `value` varchar(100) DEFAULT '0' COMMENT '对应值',
  `del_status` tinyint(1) DEFAULT '0' COMMENT '删除状态 1删除 0未删除',
  `created` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `del_status` (`del_status`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='收藏列表';

/*Data for the table `dh_collection` */

insert  into `dh_collection`(`id`,`uid`,`type`,`value`,`del_status`,`created`) values (1,1,1,'1',0,1505803330),(2,2,1,'8',1,1505900467),(3,2,1,'5',1,1506065736),(4,2,1,'5',1,1506067558),(5,2,1,'8',0,1506067564),(6,2,1,'5',0,1506067597),(7,2,1,'3',1,1506067600);

/*Table structure for table `dh_comment` */

DROP TABLE IF EXISTS `dh_comment`;

CREATE TABLE `dh_comment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '类型 1车友圈 2订单商品',
  `uid` int(11) unsigned DEFAULT '0' COMMENT '发布者uid',
  `parent_id` int(11) unsigned DEFAULT '0' COMMENT '父级id',
  `to_uid` int(11) unsigned DEFAULT '0' COMMENT '回复者uid',
  `content` varchar(300) DEFAULT '' COMMENT '评论内容',
  `del_status` tinyint(1) unsigned DEFAULT '0' COMMENT '删除状态 1删除 0未删除',
  `order_sn` varchar(18) DEFAULT '' COMMENT '订单编号',
  `goods_id` int(11) unsigned DEFAULT '0' COMMENT '商品id',
  `ablum` varchar(500) DEFAULT '' COMMENT '评价相册',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态 1开启 2关闭',
  `is_uid_reader` tinyint(1) unsigned DEFAULT '0' COMMENT '发布者阅读状态 1已读 0未读',
  `is_to_uid_reader` tinyint(1) unsigned DEFAULT '0' COMMENT '接受者阅读状态 1已读 0未读',
  `created` int(11) unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `uid` (`uid`),
  KEY `parent_id` (`parent_id`),
  KEY `goods_id` (`goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

/*Data for the table `dh_comment` */

insert  into `dh_comment`(`id`,`type`,`uid`,`parent_id`,`to_uid`,`content`,`del_status`,`order_sn`,`goods_id`,`ablum`,`status`,`is_uid_reader`,`is_to_uid_reader`,`created`) values (1,1,3,0,4,'这是我发布的第一天评论',0,'',1,'',1,0,0,1506327966),(2,1,3,0,4,'这是我发布的第一天评论',0,'',1,'',1,0,0,1506327999),(3,1,4,2,4,'这是我发布的第一天评论',0,'',1,'',1,0,0,1506328046),(4,1,3,2,4,'这是我发布的第一天评论',0,'',1,'',1,0,0,1506328046),(5,1,3,2,4,'这是我发布的第一天评论',0,'',1,'',1,0,0,1506328046),(6,1,3,2,4,'这是我发布的第一天评论',0,'',1,'',1,0,0,1506328046),(8,2,4,0,1,'啊啊啊啊啊',0,'172646797722115621',2,'',1,0,0,1506503620),(10,2,4,0,1,'啊啊啊啊啊',0,'172646797722115621',2,'',1,0,0,1506504617),(11,1,2,0,4,'这是我的评论',0,'',1,'',1,0,0,1506566061),(12,1,3,0,2,'这是第一条评论',0,'',4,'',1,0,0,1506580025),(13,1,2,12,3,'这是我的回复信息',0,'',4,'',1,0,0,1506583717),(14,1,2,4,3,'哈哈哈',0,'',1,'',1,0,0,1506584079),(15,1,2,0,2,'明年',0,'',4,'',1,0,0,1506584114),(16,1,2,4,3,'回复一次',0,'',1,'',1,0,0,1506584137),(17,1,3,4,2,'多试一下',0,'',1,'',1,0,0,1506586051),(18,1,3,4,2,'我要成功',0,'',1,'',1,0,0,1506586384),(19,1,2,12,3,'再试一次',0,'',4,'',1,0,0,1506586970),(20,1,2,12,3,'最后一个',0,'',4,'',1,0,0,1506587332),(21,1,2,0,2,'回复所有人',0,'',4,'',1,0,0,1506588087);

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

insert  into `dh_console_admin`(`id`,`consoleid`,`nickname`,`username`,`password`,`salt`,`mobile`,`status`,`group`,`create_ip`,`login_ip`,`created`,`login_time`) values (1,0,'四月','admin','8895c4947031a4019843c0d00fa303b1','50907','15923882847',1,1,'127.0.0.1','127.0.0.1',1502522576,1506491308),(4,0,'陈明江','cmj','96c76c67a66e92c1e90bce05ebec4b5d','34366','15923882847',1,1,'127.0.0.1','0',1502531990,0);

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
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;

/*Data for the table `dh_console_menus` */

insert  into `dh_console_menus`(`id`,`type`,`parentid`,`name`,`module`,`controller`,`action`,`icon`,`parameter`,`url`,`status`,`is_show`,`is_white`,`sort`,`del_status`,`created`) values (1,1,0,'系统管理','setting','menus','index','glyphicon glyphicon-triangle-right','','',1,1,0,0,0,1502445648),(2,1,1,'设置','setting','menus','index','glyphicon glyphicon-wrench','','/setting/menus/index',1,1,0,0,0,1502508402),(3,1,2,'配置菜单','setting','menus','index','','','/setting/menus/index',1,1,0,0,0,1502508459),(4,1,3,'添加/编辑菜单','setting','menus','edit','','','',1,0,0,0,0,1502440822),(5,1,3,'树状菜单列表','setting','menus','tree_list','','','',1,0,0,0,0,1502440812),(6,1,2,'管理员','setting','admin','index','','','/setting/admin/index',1,1,0,0,0,1502516144),(7,1,6,'管理员列表','setting','admin','index','','','/setting/admin/index',1,1,0,0,0,1502517276),(8,1,6,'管理员分组','setting','group','index','','','/setting/group/index',1,1,0,0,0,1502517279),(10,1,0,'网站管理','content','list','index','glyphicon glyphicon-triangle-right','','/content/list/index',1,1,0,0,0,1505529695),(11,1,17,'博客','content','blog','index','glyphicon glyphicon-book','','/content/blog/index',1,1,0,0,0,1505577819),(12,1,11,'文章列表','content','blog','index','','','/content/blog/index',1,1,0,0,0,1505577827),(13,1,17,'分类管理','content','category','lists','glyphicon glyphicon-book','','/content/category/lists',1,1,0,0,0,1505529783),(14,1,13,'分类列表','content','category','lists','','','/content/category/lists',1,1,0,0,0,1505462341),(15,1,17,'会员管理','content','user','lists','','','/content/user/lists',1,1,0,0,0,1505529776),(16,1,15,'会员列表','content','user','lists','','','/content/user/lists',1,1,0,0,0,1505529506),(17,1,10,'内容管理','content','category','lists','','','/content/category/lists',1,1,0,0,0,1505529767),(18,1,15,'积分规则','content','integral_rul','lists','','','/content/integral_rul/lists',1,1,0,0,0,1505783604),(19,1,17,'商品管理','content','car','lists','','','/content/car/lists',1,1,0,0,0,1505784921),(20,1,19,'汽车管理','content','car','lists','','','/content/car/lists',1,1,0,0,0,1505784943),(21,1,19,'服务管理','content','service','lists','','','/content/service/lists',1,1,0,0,0,1505784962),(22,1,17,'广告图片管理','content','banner','lists','','','/content/banner/lists',1,1,0,0,0,1505809594),(23,1,22,'广告分类','content','banner','lists','','','/content/banner/lists',1,1,0,0,0,1505810230),(24,1,17,'搜索管理','content','search','lists','','','/content/search/lists',1,1,0,0,0,1505873873),(25,1,24,'搜索记录','content','search','lists','','','/content/search/lists',1,1,0,0,0,1505873843),(26,1,24,'推荐列表','content','search','recommend_lists','','','/content/search/recommend_lists',1,1,0,0,0,1505873952),(27,1,24,'禁用列表','content','search','disable_lists','','','/content/search/disable_lists',1,1,0,0,0,1505874438),(28,1,15,'店铺管理','content','shop','lists','','','/content/shop/lists',1,1,0,0,0,1505958027),(29,1,17,'服务管理','content','orders','lists','','','/content/orders/lists',1,1,0,0,0,1506414050),(30,1,29,'订单列表','content','orders','lists','','','/content/orders/lists',1,1,0,0,0,1506413737),(31,1,29,'推荐列表','content','recommend','lists','','','/content/recommend/lists',1,1,0,0,0,1506414081),(32,1,17,'抵扣卷管理','content','coupon','lists','','','/content/coupon/lists',1,1,0,0,0,1506477699);

/*Table structure for table `dh_coupon` */

DROP TABLE IF EXISTS `dh_coupon`;

CREATE TABLE `dh_coupon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '类型 1满减 2折扣',
  `category` tinyint(1) unsigned NOT NULL COMMENT '所属分类',
  `uid` int(11) DEFAULT NULL COMMENT '发放者uid',
  `title` varchar(50) DEFAULT NULL COMMENT '抵扣卷名称',
  `full` decimal(10,2) DEFAULT '0.00' COMMENT '满多少钱',
  `less` decimal(10,2) DEFAULT '0.00' COMMENT '减少多少钱',
  `discount` float DEFAULT '0' COMMENT '折扣',
  `num` int(11) DEFAULT '0' COMMENT '发放数量',
  `start_time` int(11) DEFAULT '0' COMMENT '开始生效时间',
  `end_time` int(11) DEFAULT '0' COMMENT '结束生效时间',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态 1开启 0关闭',
  `remainder_num` int(11) DEFAULT '0' COMMENT '剩余领取数量',
  `del_status` tinyint(1) DEFAULT '0' COMMENT '删除状态 1删除 0未删除',
  `created` int(11) DEFAULT '0' COMMENT '创建时间',
  `is_exchange` tinyint(1) DEFAULT '1' COMMENT '是否允许积分兑换 1允许 0不允许',
  `version` int(11) DEFAULT '1' COMMENT '版本控制',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='抵扣卷模板';

/*Data for the table `dh_coupon` */

insert  into `dh_coupon`(`id`,`type`,`category`,`uid`,`title`,`full`,`less`,`discount`,`num`,`start_time`,`end_time`,`status`,`remainder_num`,`del_status`,`created`,`is_exchange`,`version`) values (2,1,23,1,'5折优惠卷','10.00','5.00',0,100,1505701881,1507564800,1,94,0,1506411977,1,12),(3,1,21,1,'5折优惠卷','1000.00','100.00',0,100,1505701881,1507564800,1,98,0,1506411977,1,9),(4,1,20,3,'满100减10','200.00','5.00',0,400,1509465600,1512057600,1,400,0,1506483325,1,1),(5,2,20,3,'这个是名称','0.00','0.00',8,55,1506787200,1509465600,1,300,0,1506483455,1,1),(6,1,21,1,'5折优惠卷','1000.00','100.00',0,100,1505701881,1507564800,1,100,0,1506491073,1,1),(7,1,21,1,'5折优惠卷','1000.00','100.00',0,100,1505701881,1507564800,1,100,0,1506491125,1,1),(8,1,23,3,'满100减5','100.00','5.00',0,200,1506528000,1506700800,1,200,0,1506562539,1,1);

/*Table structure for table `dh_coupon_log` */

DROP TABLE IF EXISTS `dh_coupon_log`;

CREATE TABLE `dh_coupon_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned DEFAULT '0' COMMENT '所属用户uid',
  `coupon_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '抵扣卷模板id',
  `order_sn` char(18) DEFAULT '0' COMMENT '使用的订单号',
  `use_time` int(11) unsigned DEFAULT '0' COMMENT '使用时间',
  `created` int(11) unsigned DEFAULT '0' COMMENT '领取时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `coupon_id` (`coupon_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COMMENT='抵扣卷列表';

/*Data for the table `dh_coupon_log` */

insert  into `dh_coupon_log`(`id`,`uid`,`coupon_id`,`order_sn`,`use_time`,`created`) values (14,4,2,'0',0,1506483306),(18,4,2,'0',0,1506483429),(22,4,2,'0',0,1506483621),(23,4,3,'0',0,1506483637),(24,4,2,'0',0,1506483638),(25,4,2,'172646797722115621',1506500098,1506483638),(26,4,2,'172646797722115621',1506499772,1506483639),(27,4,2,'172646797722115621',1506500328,1506483674),(28,4,2,'0',0,1506483675),(29,4,3,'0',0,1506483676),(30,4,3,'0',0,1506483677),(31,4,3,'0',0,1506483678),(33,4,3,'0',0,1506484219),(36,4,3,'0',0,1506503423);

/*Table structure for table `dh_enjoy` */

DROP TABLE IF EXISTS `dh_enjoy`;

CREATE TABLE `dh_enjoy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '类型 1车友圈',
  `value` varchar(10) DEFAULT '' COMMENT '对于值',
  `uid` int(10) unsigned DEFAULT '0' COMMENT '用户uid',
  `created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*Data for the table `dh_enjoy` */

insert  into `dh_enjoy`(`id`,`type`,`value`,`uid`,`created`) values (3,1,'2',2,1506577947),(4,1,'3',2,1506577953),(7,1,'4',2,1506587887);

/*Table structure for table `dh_footprints` */

DROP TABLE IF EXISTS `dh_footprints`;

CREATE TABLE `dh_footprints` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `type` tinyint(1) unsigned DEFAULT NULL COMMENT '类型 1汽车',
  `value` int(10) DEFAULT NULL COMMENT '浏览id记录',
  `value2` varchar(10) DEFAULT NULL COMMENT '记录其他信息',
  `del_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '删除状态 1删除 0未删除',
  `ip` varchar(18) DEFAULT NULL COMMENT 'IP',
  `created` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `created` (`created`),
  KEY `value2` (`value2`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='用户浏览记录';

/*Data for the table `dh_footprints` */

insert  into `dh_footprints`(`id`,`uid`,`type`,`value`,`value2`,`del_status`,`ip`,`created`) values (3,1,1,1,'1',0,'127.0.0.1',1505966052),(4,2,1,8,'3',0,'127.0.0.1',1506582970),(5,4,1,2,'1',0,'127.0.0.1',1506065062),(6,2,1,5,'3',0,'127.0.0.1',1506582973),(7,2,1,3,'3',0,'127.0.0.1',1506582976),(8,4,1,1,'1',0,'127.0.0.1',1506303637);

/*Table structure for table `dh_gift` */

DROP TABLE IF EXISTS `dh_gift`;

CREATE TABLE `dh_gift` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '类型 1抵扣卷',
  `uid` int(11) unsigned DEFAULT NULL COMMENT '接受者uid',
  `order_sn` char(18) DEFAULT '0' COMMENT '订单编号',
  `value` varbinary(10) DEFAULT '0' COMMENT '赠送类型',
  `status` tinyint(1) unsigned DEFAULT '0' COMMENT '状态 1领取 0未领取',
  `created` int(11) unsigned DEFAULT '0' COMMENT '赠送时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='赠送信息';

/*Data for the table `dh_gift` */

insert  into `dh_gift`(`id`,`type`,`uid`,`order_sn`,`value`,`status`,`created`) values (1,1,4,'172646797722115621','3',1,1506500328),(2,1,2,'172670845735945735','8',0,1506562670);

/*Table structure for table `dh_goods_ablum` */

DROP TABLE IF EXISTS `dh_goods_ablum`;

CREATE TABLE `dh_goods_ablum` (
  `goods_id` int(10) unsigned NOT NULL COMMENT '商品id',
  `type` tinyint(1) DEFAULT '1' COMMENT '1汽车信息',
  `path` varchar(100) NOT NULL DEFAULT '' COMMENT '图片地址',
  `description` varchar(500) DEFAULT '' COMMENT '图片描述',
  KEY `goods_id` (`goods_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `dh_goods_ablum` */

insert  into `dh_goods_ablum`(`goods_id`,`type`,`path`,`description`) values (8,1,'150587084778216.png','车辆左侧图片'),(7,1,'150580908094311.png','第一张图的介绍'),(7,1,'150580908088887.png','第二张图的介绍'),(7,1,'150580908030846.png','第三张图的介绍'),(8,1,'150587084722911.png','车辆右侧图片'),(8,1,'150587084720442.png','车辆全方位图片'),(9,1,'150596089940011.png',''),(9,1,'150596089953019.jpeg',''),(10,1,'150596096882383.png',''),(10,1,'150596096843093.jpeg',''),(11,1,'150596115599401.png',''),(11,1,'150596115513883.jpeg',''),(12,1,'150641899559070.png',''),(12,1,'150641899567442.png',''),(13,1,'150641904798483.png',''),(13,1,'150641904741132.png',''),(14,1,'150641910419409.png',''),(14,1,'150641910499508.png',''),(15,1,'150641947518717.png',''),(15,1,'150641947595036.png',''),(16,1,'',''),(17,1,'150642085914589.jpeg','第一张'),(17,1,'150642085933505.jpeg','第二张'),(17,1,'150642085934886.jpeg','第三张'),(18,1,'150647576846741.jpeg','第一张'),(18,1,'150647576885042.jpeg','第二张'),(18,1,'150647576860457.jpeg','第三张');

/*Table structure for table `dh_goods_car` */

DROP TABLE IF EXISTS `dh_goods_car`;

CREATE TABLE `dh_goods_car` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '类型 1个人 2商家',
  `uid` int(11) unsigned DEFAULT NULL COMMENT 'uid',
  `title` varchar(100) DEFAULT '' COMMENT '标题',
  `thumb` varchar(50) DEFAULT '' COMMENT '封面图片',
  `brand` int(11) unsigned DEFAULT '0' COMMENT '品牌',
  `style` varchar(10) DEFAULT '' COMMENT '款号',
  `produce_time` int(5) unsigned DEFAULT '0' COMMENT '生产日期',
  `model` varchar(10) DEFAULT '' COMMENT '车型',
  `buy_time` int(10) DEFAULT '0' COMMENT '购买时间戳',
  `mileage` float(10,1) unsigned DEFAULT '0.0' COMMENT '里程 万公里',
  `city` varchar(5) DEFAULT '' COMMENT '车牌城市',
  `gearbox` varchar(6) DEFAULT '' COMMENT '变数箱',
  `gases` varchar(5) DEFAULT '' COMMENT '排放标准',
  `displacement` varchar(10) DEFAULT '' COMMENT '排量',
  `model_remark` varchar(10) DEFAULT '' COMMENT '车型备注',
  `price` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '报价',
  `vin` varchar(20) DEFAULT '' COMMENT 'vin',
  `guarantee` varchar(10) DEFAULT '0' COMMENT '保障状态 1一年保修 2三次保养 3LEASE',
  `is_lease` tinyint(1) unsigned DEFAULT '1' COMMENT '是否转手 1是 2否',
  `mobile` char(11) DEFAULT '' COMMENT '联系电话',
  `weixin` varchar(20) DEFAULT '' COMMENT '微信号',
  `qq` varchar(20) DEFAULT '' COMMENT 'qq',
  `address` varchar(150) DEFAULT '' COMMENT '地址',
  `description` mediumtext COMMENT '简介',
  `banner` varchar(500) DEFAULT '' COMMENT 'banner图片',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态 1上架 2下架 3后台删除 4用户删除',
  `hot` int(11) unsigned DEFAULT '1' COMMENT '浏览次数',
  `is_recommend` tinyint(1) unsigned DEFAULT '0' COMMENT '首页平台推荐 1推荐 0不推荐',
  `is_urgency` tinyint(1) unsigned DEFAULT '0' COMMENT '是否急售 1是 0不是',
  `is_show` tinyint(1) DEFAULT '1' COMMENT '是否显示 1显示  0不显示',
  `created` int(11) unsigned DEFAULT '0' COMMENT '发布时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COMMENT='新车信息';

/*Data for the table `dh_goods_car` */

insert  into `dh_goods_car`(`id`,`type`,`uid`,`title`,`thumb`,`brand`,`style`,`produce_time`,`model`,`buy_time`,`mileage`,`city`,`gearbox`,`gases`,`displacement`,`model_remark`,`price`,`vin`,`guarantee`,`is_lease`,`mobile`,`weixin`,`qq`,`address`,`description`,`banner`,`status`,`hot`,`is_recommend`,`is_urgency`,`is_show`,`created`) values (1,2,1,'奥迪 2013 A6L 1.6L 纪念版2','150596096890834.jpeg',2,'A6L',2013,' 三厢',1420041600,1.2,'9','自动','国V','1.6L','纪念版2','18000.00','111111','0',0,'15923882847','weixin','qq','这里是测试地址信息的','这里是输入商品详情的地方','',1,5,0,0,1,1505701881),(2,2,1,'奥迪 2013 A6L 1.6L 纪念版2','150578956279606.png',1,'A6L',2013,' 三厢',1420041600,1.2,'17','自动','国V','1.6L','纪念版2','18.00','111111','0',0,'15923882847','weixin','qq','这里是测试地址信息的','这里是输入商品详情的地方11111','150578956279606.png,150578956240119.jpeg',1,24,0,0,1,1505701942),(3,2,3,'大众 2017 x5 2.0 很好','150596096890834.jpeg',3,'x5',2017,'2',1420041600,2.0,'14','2','2','2.0','很好','20.00','888888','0',1,'','','','','九成新，值得入手','',1,2,1,1,1,1505706388),(4,2,1,'汽车品牌 2013 A6L 1.6L 纪念版2','150596096890834.jpeg',1,'A6L',2013,' 三厢',1420041600,1.2,'14','自动','国V','1.6L','纪念版2','18.00','111111','0',0,'15923882847','weixin','qq','这里是测试地址信息的','这里是输入商品详情的地方','',1,1,0,0,1,1505706629),(5,2,3,'奥迪 2017 23 888 很好','150596096890834.jpeg',2,'23',2017,'1',1420041600,5.0,'14','2','1','888','很好','5.00','88888996','0',0,'','','','','很好的啦','',1,4,1,1,1,1505706882),(6,2,1,'汽车品牌 2013 A6L 1.6L 纪念版2','150596096890834.jpeg',1,'A6L',2013,' 三厢',1420041600,1.2,'14','自动','国V','1.6L','纪念版2','18.00','111111','0',0,'15923882847','weixin','qq','这里是测试地址信息的','这里是输入商品详情的地方11111','150580886964585.png,150580886964659.jpeg',1,3,0,0,1,1505808869),(7,2,1,'汽车品牌 2013 A6L 1.6L 纪念版2','150596096890834.jpeg',1,'A6L',2013,' 三厢',1420041600,1.2,'14','自动','国V','1.6L','纪念版2','18.00','111111','0',0,'15923882847','weixin','qq','这里是测试地址信息的','这里是输入商品详情的地方11111','150580908058688.png,150580908096283.jpeg',1,1,0,0,1,1505809080),(8,2,3,'奥迪 2017 x6 2.0 很好的汽车，九成新','150587084744692.png',2,'x6',2017,'5',1420041600,5.0,'14','1','1','2.0','很好的汽车，九成新','20.00','66666666','0',0,'','','','','很好哦，九成新，值得入手，不要犹豫','150587084744692.png,150587084733090.png',1,1,1,1,1,1505870847),(9,2,1,'汽车品牌 2013 A6L 1.6L 纪念版2','150596096890834.jpeg',1,'A6L',2013,' 三厢',2017,1.2,'5','自动','国V','1.6L','纪念版2','18.00','111111','0',0,'15923882847','weixin','qq','这里是测试地址信息的','这里是输入商品详情的地方11111','',1,1,0,0,1,1505960899),(10,2,1,'汽车品牌 2013 A6L 1.6L 纪念版2','150596096890834.jpeg',1,'A6L',2013,' 三厢',2017,1.2,'5','自动','国V','1.6L','纪念版2','18.00','111111','0',0,'15923882847','weixin','qq','这里是测试地址信息的','这里是输入商品详情的地方11111','150596096890834.jpeg,150596096837567.jpeg,150596096889744.png',1,1,0,0,1,1505960968),(11,2,1,'汽车品牌 2013 A6L 1.6L 纪念版2','150596115541618.jpeg',1,'A6L',2013,' 三厢',2017,1.2,'5','自动','国V','1.6L','纪念版2','18.00','111111','0',0,'15923882847','weixin','qq','这里是测试地址信息的','这里是输入商品详情的地方11111','150596115541618.jpeg,150596115599406.jpeg,150596115594979.png',1,1,0,0,1,1505961155),(12,1,4,'汽车品牌 2013 A6L 1.6L 纪念版2','150641899599456.png',1,'A6L',2013,' 三厢',2017,1.2,'5','自动','国V','1.6L','纪念版2','18.00','111111','0',0,'15923882847','weixin','qq','这里是测试地址信息的','这里是输入商品详情的地方11111','150641899599456.png,150641899599165.png,150641899581683.png',1,1,0,0,1,1506418995),(13,1,4,'汽车品牌 2013 A6L 1.6L 纪念版2','150641904739627.png',1,'A6L',2013,' 三厢',2017,1.2,'5','自动','国V','1.6L','纪念版2','18.00','111111','0',0,'15923882847','weixin','qq','这里是测试地址信息的','这里是输入商品详情的地方11111','150641904739627.png,150641904789466.png,150641904730885.png',1,1,0,0,1,1506419047),(14,1,4,'汽车品牌 2013 A6L 1.6L 纪念版2','150641910488810.png',1,'A6L',2013,' 三厢',2017,1.2,'5','自动','国V','1.6L','纪念版2','18.00','111111','0',0,'15923882847','weixin','qq','这里是测试地址信息的','这里是输入商品详情的地方11111','150641910488810.png,150641910457982.png,150641910411562.png',1,1,0,0,1,1506419104),(15,1,4,'汽车品牌 2013 A6L 1.6L 纪念版2','150641947515130.png',1,'A6L',2013,' 三厢',2017,1.2,'5','自动','国V','1.6L','纪念版2','18.00','111111','0',0,'15923882847','weixin','qq','这里是测试地址信息的','这里是输入商品详情的地方11111','150641947515130.png,150641947565011.png,150641947546598.png',1,1,0,0,1,1506419475),(16,1,2,'奥迪 2017 j6','150641999361907.jpeg',2,'j6',2017,'',1504195200,6.0,'4','','','','','13.00','','0',0,'','','','','','150641999361907.jpeg,150641999321747.jpeg,150641999369548.jpeg',1,1,0,0,1,1506419993),(17,1,2,'奥迪 2017 l6 6 备注','150642085932332.png',2,'l6',2017,'2',1504195200,6.0,'4','2','1','6','备注','13.00','kdkxjxj','0',0,'','','','','差个明模式我说问一下','150642085932332.png,150642085925946.png,150642085922650.png',1,1,0,0,1,1506420859),(18,1,2,'奥迪 2017 c6 6 备注啦啦','150647576838026.jpeg',2,'c6',2017,'1',1501516800,5.0,'2','2','1','6','备注啦啦','13.00','646797997','0',0,'','','','','详细备注，让客户更好了解车子','150647576838026.jpeg,150647576843596.jpeg,150647576844041.jpeg',1,1,0,0,1,1506475768);

/*Table structure for table `dh_goods_service` */

DROP TABLE IF EXISTS `dh_goods_service`;

CREATE TABLE `dh_goods_service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int(10) unsigned DEFAULT '0' COMMENT 'uid',
  `title` varchar(60) DEFAULT '' COMMENT '标题',
  `thumb` varchar(150) DEFAULT '' COMMENT '封面图',
  `type` tinyint(1) DEFAULT '0' COMMENT '服务类型',
  `price` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '价格',
  `ablum` varchar(500) DEFAULT '' COMMENT '相册',
  `description` mediumtext COMMENT '详情',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态 1上架 2下架 3后台删除 4用户删除',
  `hot` int(11) DEFAULT '0' COMMENT '浏览次数',
  `orders` int(11) DEFAULT '0' COMMENT '订单总量',
  `is_show` tinyint(1) DEFAULT '1' COMMENT '显示状态 1显示 0不显示',
  `del_status` tinyint(1) DEFAULT '0' COMMENT '删除状态 1删除 0未删除',
  `created` int(10) unsigned DEFAULT '0' COMMENT '发布时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='服务信息';

/*Data for the table `dh_goods_service` */

insert  into `dh_goods_service`(`id`,`uid`,`title`,`thumb`,`type`,`price`,`ablum`,`description`,`status`,`hot`,`orders`,`is_show`,`del_status`,`created`) values (2,1,'免费贴膜','150578927012952.jpeg',5,'9.90','150578927012952.jpeg,150578927046872.png,150578927019459.png','这里是文字内容介绍的地方哦哦哦哦哦哦哦哦哦',1,0,0,1,0,1505702940),(3,1,'免费贴膜','150578927012952.jpeg',5,'9.90','','这里是文字内容介绍的地方哦哦哦哦哦哦哦哦哦',1,0,0,1,0,1505703219),(4,1,'免费贴膜','150578927012952.jpeg',5,'10.00','150570322945362.png,150570322992738.jpeg,150570322914812.jpeg','这里是文字内容介绍的地方哦哦哦哦哦哦哦哦哦',1,0,0,1,0,1505703229),(5,1,'免费贴膜','150578927012952.jpeg',5,'10.00','150570424689878.png,150570424614177.jpeg,150570424697703.jpeg','这里是文字内容介绍的地方哦哦哦哦哦哦哦哦哦',1,0,0,1,0,1505704246),(6,1,'免费贴膜','150578927012952.jpeg',5,'10.00','150570433438039.png,150570433448718.jpeg,150570433473341.jpeg','这里是文字内容介绍的地方哦哦哦哦哦哦哦哦哦',1,0,0,1,0,1505704334),(7,1,'免费贴膜','150578927012952.jpeg',5,'10.00','150570437049342.png,150570437077162.jpeg,150570437034977.jpeg','这里是文字内容介绍的地方哦哦哦哦哦哦哦哦哦',1,0,0,1,0,1505704370),(8,1,'免费贴膜','150578927012952.jpeg',5,'10.00','150570449431456.png,150570449497171.jpeg,150570449483501.jpeg','这里是文字内容介绍的地方哦哦哦哦哦哦哦哦哦',1,0,0,1,0,1505704494),(10,1,'免费贴膜','150578927012952.jpeg',5,'9.90','150570605387503.png,150570605341278.jpeg,150570605343313.jpeg','这里是文字内容介绍的地方哦哦哦哦哦哦哦哦哦',1,0,0,1,0,1505706053),(11,3,'200','150578927012952.jpeg',1,'200.00','150571629162506.jpeg,150571629146095.jpeg','主要针对劳斯莱斯、布加迪等维修保养',1,0,0,1,0,1505716291);

/*Table structure for table `dh_help_car` */

DROP TABLE IF EXISTS `dh_help_car`;

CREATE TABLE `dh_help_car` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned DEFAULT '0' COMMENT '用户id',
  `brand` varchar(10) DEFAULT '' COMMENT '品牌',
  `price` varchar(10) DEFAULT '0' COMMENT '期望价格',
  `buy_time` varchar(10) DEFAULT '0' COMMENT '期望车龄',
  `mileage` varchar(10) DEFAULT '0' COMMENT '期望里程',
  `description` mediumtext COMMENT '详情',
  `created` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态 1申请 2失败 3完成',
  `recommend_id` varchar(300) DEFAULT '' COMMENT '推荐id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='帮我买车';

/*Data for the table `dh_help_car` */

insert  into `dh_help_car`(`id`,`uid`,`brand`,`price`,`buy_time`,`mileage`,`description`,`created`,`status`,`recommend_id`) values (2,1,'大众','10万以内','今年','23万公里内的','这里是简介信息',1505957035,1,NULL),(3,2,'奥迪','20万以上','一年两个月','','',1505964715,1,NULL);

/*Table structure for table `dh_help_service` */

DROP TABLE IF EXISTS `dh_help_service`;

CREATE TABLE `dh_help_service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned DEFAULT '0' COMMENT '申请uid',
  `sign` tinyint(1) unsigned DEFAULT '0' COMMENT '服务类型id',
  `price` varchar(30) DEFAULT '0' COMMENT '服务价格',
  `description` varchar(500) DEFAULT '' COMMENT '服务详情',
  `created` int(11) DEFAULT '0' COMMENT '发布时间',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态 1申请 2推荐完成',
  `del_status` tinyint(1) DEFAULT '0' COMMENT '删除状态 1已删除 0未删除',
  `recommend_id` varchar(300) DEFAULT '' COMMENT '推荐id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `dh_help_service` */

insert  into `dh_help_service`(`id`,`uid`,`sign`,`price`,`description`,`created`,`status`,`del_status`,`recommend_id`) values (1,4,1,'0-500元','这里是输入简介测试的地方',1506407793,1,0,'');

/*Table structure for table `dh_integral_log` */

DROP TABLE IF EXISTS `dh_integral_log`;

CREATE TABLE `dh_integral_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户uid',
  `value` int(11) DEFAULT NULL COMMENT '积分',
  `content` varchar(250) DEFAULT NULL COMMENT '操作内容',
  `created` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `content` (`content`,`created`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 COMMENT='积分详情';

/*Data for the table `dh_integral_log` */

insert  into `dh_integral_log`(`id`,`uid`,`value`,`content`,`created`) values (26,1,30,'每日签到',1505724836),(27,3,30,'每日签到',1505786396),(28,2,30,'每日签到',1505874198),(29,2,30,'每日签到',1506063422),(30,3,30,'每日签到',1506305320),(31,2,30,'每日签到',1506320372),(32,6,100,'注册赠送',1506395715),(33,4,-500,'领取汽车贴膜优惠卷',1506482204),(34,4,-500,'领取汽车贴膜优惠卷',1506482274),(35,4,-500,'领取汽车贴膜优惠卷',1506482397),(36,4,-500,'领取汽车贴膜优惠卷',1506482445),(37,4,-500,'领取汽车贴膜优惠卷',1506482927),(38,4,-500,'领取汽车贴膜优惠卷',1506483107),(39,4,-500,'领取汽车贴膜优惠卷',1506483145),(40,4,-500,'领取汽车贴膜优惠卷',1506483146),(41,4,-500,'领取汽车贴膜优惠卷',1506483164),(42,4,-500,'领取汽车贴膜优惠卷',1506483230),(43,4,-500,'领取汽车贴膜优惠卷',1506483306),(44,4,-500,'领取汽车贴膜优惠卷',1506483621),(45,4,-500,'领取汽车贴膜优惠卷',1506483637),(46,4,-500,'领取汽车贴膜优惠卷',1506483638),(47,4,-500,'领取汽车贴膜优惠卷',1506483638),(48,4,-500,'领取汽车贴膜优惠卷',1506483639),(49,4,-500,'领取汽车贴膜优惠卷',1506483674),(50,4,-500,'领取汽车贴膜优惠卷',1506483675),(51,4,-500,'领取汽车贴膜优惠卷',1506483676),(52,4,-500,'领取汽车贴膜优惠卷',1506483677),(53,4,-500,'领取汽车贴膜优惠卷',1506483678),(54,4,-500,'领取汽车贴膜优惠卷',1506483740),(55,4,-500,'领取汽车贴膜优惠卷',1506483972),(56,4,-500,'领取汽车贴膜优惠卷',1506484028),(57,4,-500,'领取汽车贴膜优惠卷',1506484043),(58,4,-500,'领取汽车贴膜优惠卷',1506484097),(59,4,-500,'领取汽车贴膜优惠卷',1506484219),(60,2,30,'每日签到',1506498598),(61,2,30,'每日签到',1506582983);

/*Table structure for table `dh_integral_rul` */

DROP TABLE IF EXISTS `dh_integral_rul`;

CREATE TABLE `dh_integral_rul` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则id',
  `value` int(11) DEFAULT NULL COMMENT '积分数',
  `content` varchar(350) DEFAULT NULL COMMENT '规则内容',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态 1开启 0关闭',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='积分规则';

/*Data for the table `dh_integral_rul` */

insert  into `dh_integral_rul`(`id`,`value`,`content`,`status`) values (1,100,'注册赠送',1),(2,30,'每日签到',1),(3,100,'分享赠送',1);

/*Table structure for table `dh_my_car` */

DROP TABLE IF EXISTS `dh_my_car`;

CREATE TABLE `dh_my_car` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned DEFAULT '0' COMMENT '用户uid',
  `ablum` varchar(60) DEFAULT '' COMMENT '头图',
  `brand` int(10) unsigned DEFAULT '0' COMMENT '品牌id',
  `style` varchar(10) DEFAULT '' COMMENT '款号',
  `produce_time` varchar(10) DEFAULT '' COMMENT '生成时间',
  `buy_time` int(10) unsigned DEFAULT '0' COMMENT '购买时间戳',
  `mileage` decimal(10,1) unsigned DEFAULT '0.0' COMMENT '里程数',
  `vin` varchar(60) DEFAULT '' COMMENT 'vin编码',
  `del_status` tinyint(1) DEFAULT '0' COMMENT '删除状态 1删除 0未删除',
  `model` varchar(10) DEFAULT '' COMMENT '车型',
  `created` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='我的车库';

/*Data for the table `dh_my_car` */

insert  into `dh_my_car`(`id`,`uid`,`ablum`,`brand`,`style`,`produce_time`,`buy_time`,`mileage`,`vin`,`del_status`,`model`,`created`) values (2,4,'150630642578788.png',21,'A9','2017',1000000,'0.0','100',0,'',1506306425),(3,4,'150630652116685.png',21,'A8','2017',1000000,'0.0','100',0,'',1506306521),(4,2,'150632597311131.jpeg',2,'j8','2016年09月',1504195200,'5.0','568856889',0,'',1506325973),(5,2,'150632771442590.jpeg',3,'j8','2016年',1504195200,'5.0','568856889',0,'三厢',1506327119);

/*Table structure for table `dh_orders` */

DROP TABLE IF EXISTS `dh_orders`;

CREATE TABLE `dh_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '类型 1汽车 2服务',
  `order_sn` char(18) NOT NULL DEFAULT '' COMMENT '订单编号',
  `uid` int(10) unsigned DEFAULT '0' COMMENT '买家id',
  `seller_uid` int(10) unsigned DEFAULT '0' COMMENT '卖家id',
  `message` varchar(300) DEFAULT '' COMMENT '买家留言',
  `seller_message` varchar(300) DEFAULT '' COMMENT '卖家留言',
  `order_status` tinyint(1) DEFAULT '1' COMMENT '订单状态 1待确认 2待完成 3已完成 4待评价 5已评价',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '审核状态 0代审核 1审核通过 2另设时间 3直接拒绝',
  `del_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '删除状态 0未删除 1删除',
  `del_uid` tinyint(1) DEFAULT '0' COMMENT '买家删除 1删除 0未删除',
  `del_seller` tinyint(1) DEFAULT '0' COMMENT '卖家删除 1删除 0未删除',
  `acount_original` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '订单金额',
  `acount` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '实付金额(订单金额 - 优惠金额 + 运费）',
  `coupon_price` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '优惠金额',
  `fare_price` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '运费',
  `is_modify` tinyint(1) unsigned DEFAULT '0' COMMENT '是否修改价格 1修改 0未修改',
  `is_temp` tinyint(1) DEFAULT '0' COMMENT '是否临时订单 1是 0否',
  `pass_time` int(10) unsigned DEFAULT '0' COMMENT '订单确认操作时间',
  `status_time` int(10) unsigned DEFAULT '0' COMMENT '订单审核操作时间',
  `close_time` int(11) unsigned DEFAULT '0' COMMENT '订单关闭操作时间',
  `success_time` int(11) unsigned DEFAULT '0' COMMENT '订单完成时间',
  `origin` tinyint(1) unsigned DEFAULT '0' COMMENT '来源 0未知 1安卓 2IOS 3PC 4微信 5WAP',
  `version` varchar(10) DEFAULT '0' COMMENT '版本号',
  `created` int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `order_sn` (`order_sn`),
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `order_status` (`order_status`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Data for the table `dh_orders` */

insert  into `dh_orders`(`id`,`type`,`order_sn`,`uid`,`seller_uid`,`message`,`seller_message`,`order_status`,`status`,`del_status`,`del_uid`,`del_seller`,`acount_original`,`acount`,`coupon_price`,`fare_price`,`is_modify`,`is_temp`,`pass_time`,`status_time`,`close_time`,`success_time`,`origin`,`version`,`created`) values (2,1,'172646797722115621',4,1,'','',5,1,0,0,0,'18.00','14.00','5.00','0.00',0,0,0,0,0,1506500328,0,'v1',0),(3,1,'172670367752827418',2,3,'希望可以准时','',3,1,0,0,0,'20.00','20000.00','0.00','0.00',0,0,1506307870,0,0,1506563853,0,'v1',0),(4,1,'172670845735945735',2,3,'','',3,1,0,0,0,'5.00','20000.00','0.00','0.00',0,0,1506328741,0,1506561709,1506562670,0,'v1',0),(7,2,'172672149529245387',4,1,'','',1,0,0,0,0,'9.90','9.90','0.00','0.00',0,0,0,0,0,0,0,'v1',0),(8,2,'172672149660368763',4,1,'','',1,0,0,0,0,'9.90','9.90','0.00','0.00',0,0,0,0,0,0,0,'v1',0),(9,1,'172672782593092734',2,3,'','没得原因',1,3,0,1,1,'20.00','20.00','0.00','0.00',0,0,0,0,0,0,0,'v1',0),(10,2,'172681425284278516',2,3,'','',2,1,0,0,0,'200.00','200.00','0.00','0.00',0,0,1506417063,0,0,0,0,'v1',0),(11,0,'172706472674515321',0,1,'','',1,3,0,0,0,'18.00','19.00','0.00','0.00',0,1,0,0,0,1506564726,0,'v1',1506564726);

/*Table structure for table `dh_orders_car` */

DROP TABLE IF EXISTS `dh_orders_car`;

CREATE TABLE `dh_orders_car` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_sn` char(18) NOT NULL COMMENT '订单编号',
  `ascription` tinyint(1) DEFAULT '1' COMMENT '归属 1个人 2店铺',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始预约时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束预约时间',
  `goods_id` int(10) unsigned DEFAULT '0' COMMENT '商品id',
  `title` varchar(150) NOT NULL DEFAULT '' COMMENT '商品标题',
  `thumb` varchar(60) DEFAULT '' COMMENT '商品图片',
  `produce_time` varchar(10) DEFAULT '' COMMENT '生成日期',
  `mileage` varchar(10) DEFAULT '' COMMENT '里程数',
  `price_original` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '商品原价',
  `price` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '商品先价',
  `is_modify` tinyint(1) unsigned DEFAULT '0' COMMENT '价格修改状态 1修改 0未修改',
  `coupon_id` int(11) DEFAULT '0' COMMENT '优惠卷id',
  PRIMARY KEY (`id`),
  KEY `order_sn` (`order_sn`),
  KEY `goods_id` (`goods_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `dh_orders_car` */

insert  into `dh_orders_car`(`id`,`order_sn`,`ascription`,`start_time`,`end_time`,`goods_id`,`title`,`thumb`,`produce_time`,`mileage`,`price_original`,`price`,`is_modify`,`coupon_id`) values (2,'172646797722115621',2,1506056400,1506074400,2,'奥迪 2013 A6L 1.6L 纪念版2','150578956279606.png','2013','1.2','18.00','18.00',0,0),(3,'172670367752827418',2,2017,2017,8,'奥迪 2017 x6 2.0 很好的汽车，九成新','150587084744692.png','2017','5.0','20.00','20.00',0,0),(4,'172670845735945735',2,2017,2017,5,'奥迪 2017 23 888 很好','150596096890834.jpeg','2017','5.0','5.00','5.00',0,0),(5,'172672782593092734',2,1504195200,1504195200,3,'大众 2017 x5 2.0 很好','150596096890834.jpeg','2017','2.0','20.00','20.00',0,0),(6,'172706472674515321',2,1505701881,1505701881,11,'汽车品牌 2013 A6L 1.6L 纪念版2','150596115541618.jpeg','2013','1.2','18.00','18.00',0,0);

/*Table structure for table `dh_orders_service` */

DROP TABLE IF EXISTS `dh_orders_service`;

CREATE TABLE `dh_orders_service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_sn` char(18) NOT NULL DEFAULT '' COMMENT '订单编号',
  `vin` varchar(60) DEFAULT '' COMMENT 'vin编号',
  `start_time` int(10) unsigned DEFAULT '0' COMMENT '开始预约时间',
  `end_time` int(10) unsigned DEFAULT '0' COMMENT '结束预约时间',
  `ablum` varchar(500) DEFAULT '' COMMENT '详情图',
  `brand` varchar(10) DEFAULT '' COMMENT '品牌文字',
  `style` varchar(10) DEFAULT '' COMMENT '款号文字',
  `produce_time` varchar(10) DEFAULT '' COMMENT '生产时间',
  `buy_time` int(10) unsigned DEFAULT '0' COMMENT '购买时间戳',
  `mileage` decimal(10,1) unsigned DEFAULT '0.0' COMMENT '里程数',
  `goods_id` int(11) DEFAULT NULL COMMENT '商品id',
  `price` decimal(10,2) DEFAULT NULL COMMENT '商品价格',
  `price_original` decimal(10,2) DEFAULT NULL COMMENT '商品原价',
  `title` varchar(50) DEFAULT NULL COMMENT '标题',
  `thumb` varchar(100) DEFAULT NULL COMMENT '封面图片',
  `model` varchar(10) DEFAULT NULL COMMENT '车型',
  PRIMARY KEY (`id`),
  KEY `order_sn` (`order_sn`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='订单服务详情';

/*Data for the table `dh_orders_service` */

insert  into `dh_orders_service`(`id`,`order_sn`,`vin`,`start_time`,`end_time`,`ablum`,`brand`,`style`,`produce_time`,`buy_time`,`mileage`,`goods_id`,`price`,`price_original`,`title`,`thumb`,`model`) values (1,'172672149529245387','',1506056400,1506074400,'150632149595531.png,150632149598385.png','汽车改装','A8','2017',1000000,'0.0',3,'9.90','9.90','免费贴膜','150578927012952.jpeg',NULL),(2,'172672149660368763','',1506056400,1506074400,'150632149622104.png,150632149619352.png','汽车改装','A8','2017',1000000,'0.0',3,'9.90','9.90','免费贴膜','150578927012952.jpeg',NULL),(3,'172681425284278516','',1506417900,1506421500,'150641425244255.jpeg,150641425271548.jpeg','大众','j8','2016年',1504195200,'5.0',11,'200.00','200.00','200','150578927012952.jpeg',NULL);

/*Table structure for table `dh_report_log` */

DROP TABLE IF EXISTS `dh_report_log`;

CREATE TABLE `dh_report_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '类型 1文章',
  `uid` int(11) DEFAULT '0' COMMENT '举报用户uid',
  `goods_id` int(10) unsigned DEFAULT '0' COMMENT 'id值',
  `created` int(11) DEFAULT '0' COMMENT '创建时间',
  `ip` varchar(20) DEFAULT '127.0.0.1' COMMENT 'ip地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='举报记录表';

/*Data for the table `dh_report_log` */

/*Table structure for table `dh_score` */

DROP TABLE IF EXISTS `dh_score`;

CREATE TABLE `dh_score` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '类型 1店铺',
  `uid` int(11) DEFAULT '0' COMMENT '打分用户uid',
  `value` varchar(10) DEFAULT NULL COMMENT '保存值',
  `score` int(10) unsigned DEFAULT NULL COMMENT '打分',
  `created` int(10) unsigned DEFAULT NULL COMMENT '打分时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='打分';

/*Data for the table `dh_score` */

insert  into `dh_score`(`id`,`type`,`uid`,`value`,`score`,`created`) values (1,1,4,'1',0,1506504617);

/*Table structure for table `dh_search_disable` */

DROP TABLE IF EXISTS `dh_search_disable`;

CREATE TABLE `dh_search_disable` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '类型 1汽车',
  `value` varchar(200) NOT NULL COMMENT '禁用搜索内容',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1开启 0关闭',
  PRIMARY KEY (`id`),
  KEY `value` (`value`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='搜索禁用';

/*Data for the table `dh_search_disable` */

insert  into `dh_search_disable`(`id`,`type`,`value`,`status`) values (1,1,'日',1),(2,1,'你妈',1);

/*Table structure for table `dh_search_log` */

DROP TABLE IF EXISTS `dh_search_log`;

CREATE TABLE `dh_search_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) DEFAULT '1' COMMENT '类型 1车型',
  `value` varchar(100) DEFAULT '' COMMENT '搜索内容',
  `uid` int(10) DEFAULT '0' COMMENT '用户uid',
  `created` int(11) DEFAULT '0' COMMENT '创建时间',
  `hot` int(11) DEFAULT '1' COMMENT '搜索次数',
  PRIMARY KEY (`id`),
  KEY `keyword` (`value`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='搜索记录';

/*Data for the table `dh_search_log` */

insert  into `dh_search_log`(`id`,`type`,`value`,`uid`,`created`,`hot`) values (2,1,'奥迪',0,1505875793,2),(3,1,'大众',0,1505876306,5),(4,1,'大众',1,1505876338,2),(5,1,'html',0,1506590163,1);

/*Table structure for table `dh_search_remmond` */

DROP TABLE IF EXISTS `dh_search_remmond`;

CREATE TABLE `dh_search_remmond` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL COMMENT '类型',
  `value` varchar(100) DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1开启 0关闭',
  `del_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '删除状态 1删除 0未删除',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `created` int(11) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `value` (`value`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='搜索推荐';

/*Data for the table `dh_search_remmond` */

insert  into `dh_search_remmond`(`id`,`type`,`value`,`status`,`del_status`,`sort`,`created`) values (3,1,'奥迪',1,0,0,1505886446),(4,1,'大众',1,0,0,1505888139),(5,1,'1111',1,0,0,1505889580),(6,1,'111',1,0,0,1505890077);

/*Table structure for table `dh_shop_hot_log` */

DROP TABLE IF EXISTS `dh_shop_hot_log`;

CREATE TABLE `dh_shop_hot_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '店铺uid',
  `num` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '访问次数',
  `time` varchar(10) NOT NULL DEFAULT '' COMMENT '当日访问记录',
  PRIMARY KEY (`id`),
  KEY `time` (`time`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `dh_shop_hot_log` */

insert  into `dh_shop_hot_log`(`id`,`uid`,`num`,`time`) values (1,1,2,'2017-09-21'),(2,2,22,'2017-09-22'),(3,4,2,'2017-09-22'),(4,4,1,'2017-09-25'),(5,2,4,'2017-09-25'),(6,2,3,'2017-09-28');

/*Table structure for table `dh_user` */

DROP TABLE IF EXISTS `dh_user`;

CREATE TABLE `dh_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `type` tinyint(1) unsigned DEFAULT '1' COMMENT '类型 1个人 2商家',
  `username` varchar(50) DEFAULT '' COMMENT '用户名',
  `nickname` varchar(20) DEFAULT '' COMMENT '昵称',
  `avatar` varchar(60) DEFAULT '' COMMENT '头像地址',
  `mobile` char(11) DEFAULT '' COMMENT '手机号',
  `mail` varchar(50) DEFAULT '' COMMENT '邮箱',
  `password` varchar(32) DEFAULT '' COMMENT '密码',
  `salt` char(5) DEFAULT '' COMMENT '随机码',
  `token` char(32) DEFAULT '',
  `time_out` int(10) unsigned DEFAULT '0' COMMENT 'token到期时间',
  `ip` varchar(18) DEFAULT '' COMMENT '注册ip',
  `integral` int(10) unsigned DEFAULT '0' COMMENT '积分',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态 1开启 2禁用',
  `del_status` tinyint(1) unsigned DEFAULT '0' COMMENT '删除状态 1删除 0未删除',
  `login_ip` char(18) DEFAULT '' COMMENT '最近一次登录ip',
  `login_time` int(10) unsigned DEFAULT '0' COMMENT '最近一次登录时间',
  `created` int(11) unsigned DEFAULT '0' COMMENT '注册时间',
  `is_message` tinyint(1) DEFAULT '1' COMMENT '锁屏新消息是否开启 1开启 0不开启',
  `version` int(11) DEFAULT '0' COMMENT '版本控制',
  `imei` varchar(300) DEFAULT '' COMMENT '设备编码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Data for the table `dh_user` */

insert  into `dh_user`(`id`,`type`,`username`,`nickname`,`avatar`,`mobile`,`mail`,`password`,`salt`,`token`,`time_out`,`ip`,`integral`,`status`,`del_status`,`login_ip`,`login_time`,`created`,`is_message`,`version`,`imei`) values (1,2,'cheng6251','cheng6251','150631954346095.jpeg','13425778542','','7df8faa9a5f71811689662131b7f9fe5','45452','11b141ff4386fb2b8062cad3df260a6d',1506759820,'127.0.0.1',100000,1,0,'127.0.0.1',1506304319,1505292648,1,0,''),(2,1,'weixuelin','魏雪林','150631954346095.jpeg','18523563220','6497646qw@163.com','f6b81aa9bcdeb6e01455a3d61e16f4d7','21749','d79d70cef43a49b8eb9793ba4706567c',1506762905,'127.0.0.1',100030,1,0,'127.0.0.1',1506586770,1505293289,1,0,''),(3,2,'kuangxin','kuangxin','150631954346095.jpeg','13896568031','','01bafc44de12208bb48680dca2ea4c16','12532','b1a0dbfc84932ac4bd33e33ffd108f12',1506759495,'127.0.0.1',100000,1,0,'127.0.0.1',1506585731,1505368006,1,0,''),(4,1,'四月个人用户','四月个人用户','150631954346095.jpeg','15923882847','','ffbb0d9f227b1d1df90513144b3b73ad','14526','1ad65d2f52e29826d23425578d724323',1506759009,'127.0.0.1',100000,1,0,'127.0.0.1',1506320682,1506063155,1,0,''),(5,1,'123456','123456','','15923882847','','509849f59071c6829ea94459620d9026','59117','',0,'127.0.0.1',100000,1,0,'',0,1506395554,1,0,''),(6,1,'1234567','1234567','','15923882847','','de7a8d21b859e33d6fbee46781a4e08d','50621','',0,'127.0.0.1',100000,1,0,'',0,1506395715,1,0,'');

/*Table structure for table `dh_user_message` */

DROP TABLE IF EXISTS `dh_user_message`;

CREATE TABLE `dh_user_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned DEFAULT '0' COMMENT '发送者uid 0系统消息',
  `to_uid` int(10) unsigned DEFAULT '0' COMMENT '接受信息用户id',
  `content` varchar(300) DEFAULT '' COMMENT '消息内容',
  `jump_app` varchar(300) DEFAULT '' COMMENT 'app原生跳转参数',
  `is_reader` tinyint(1) unsigned DEFAULT '0' COMMENT '读取状态 1已读 0未读',
  `del_status` tinyint(1) unsigned DEFAULT '0' COMMENT '删除状态 1删除 0未删除',
  `created` int(10) unsigned DEFAULT '0' COMMENT '发布时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`uid`,`to_uid`,`content`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `dh_user_message` */

insert  into `dh_user_message`(`id`,`uid`,`to_uid`,`content`,`jump_app`,`is_reader`,`del_status`,`created`) values (1,0,3,'恭喜你成为会员,祝你购车愉快','',0,0,1506395554),(2,0,4,'恭喜你成为会员,祝你购车愉快','',0,0,1506395715);

/*Table structure for table `dh_user_shop` */

DROP TABLE IF EXISTS `dh_user_shop`;

CREATE TABLE `dh_user_shop` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned DEFAULT '0' COMMENT '用户id',
  `goods_num` int(11) unsigned DEFAULT '0' COMMENT '服务类商品数量',
  `orders` int(11) DEFAULT NULL COMMENT '订单成功总量',
  `name` varchar(15) DEFAULT '' COMMENT '店铺名称',
  `avatar` varchar(20) DEFAULT '' COMMENT '店铺头像',
  `credit_level` tinyint(1) unsigned DEFAULT '0' COMMENT '平均信用等级 共50分 5星',
  `woker_time` varchar(100) DEFAULT '' COMMENT '工作时间',
  `address` varchar(150) DEFAULT '' COMMENT '地址',
  `category` varchar(20) DEFAULT '0' COMMENT '分类id',
  `ablum` varchar(300) DEFAULT '' COMMENT '店铺照片',
  `ide_ablum` varchar(300) DEFAULT '' COMMENT '认证照片',
  `is_ide` tinyint(1) unsigned DEFAULT '0' COMMENT '认证状态 0未认证 1已认证 2认证未通过',
  `is_message` tinyint(1) unsigned DEFAULT '1' COMMENT '锁屏新消息是否开启 1开启 0不开启',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '店铺状态 1开启 2关闭',
  `is_recommend` tinyint(1) unsigned DEFAULT '0' COMMENT '店铺推荐 1推荐 0未推荐',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `dh_user_shop` */

insert  into `dh_user_shop`(`id`,`uid`,`goods_num`,`orders`,`name`,`avatar`,`credit_level`,`woker_time`,`address`,`category`,`ablum`,`ide_ablum`,`is_ide`,`is_message`,`status`,`is_recommend`) values (1,1,8,NULL,'四月工作室','150537200968318.png',46,'9点-10点','这里是店铺地址信息','20,22','150536187327122.jpeg,150536187343181.png','150536187327122.jpeg,150536187343181.png',1,0,1,1),(2,3,1,NULL,'kuangxin','150588957988857.jpeg',49,'09:00-18:00','重庆市南岸区','20,22','150536187327122.jpeg,150536187343181.png','150588960214710.png',1,1,1,1);

/*Table structure for table `dh_visitor_comment` */

DROP TABLE IF EXISTS `dh_visitor_comment`;

CREATE TABLE `dh_visitor_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned DEFAULT NULL COMMENT '类型',
  `goods_id` int(10) unsigned DEFAULT NULL COMMENT '商品id',
  `parent_id` int(10) unsigned DEFAULT NULL COMMENT '上级id',
  `content` varchar(500) DEFAULT NULL COMMENT '评论内容',
  `nickname` varchar(10) DEFAULT NULL COMMENT '昵称',
  `mail` varchar(20) DEFAULT NULL COMMENT '邮箱',
  `ip` varchar(20) DEFAULT NULL COMMENT 'ip地址',
  `is_show` tinyint(1) unsigned DEFAULT '1' COMMENT '状态 1显示 0不显示',
  `created` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='游客评论';

/*Data for the table `dh_visitor_comment` */

insert  into `dh_visitor_comment`(`id`,`type`,`goods_id`,`parent_id`,`content`,`nickname`,`mail`,`ip`,`is_show`,`created`) values (1,1,3,0,'33333','111',NULL,'127.0.0.1',1,1506592498);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
