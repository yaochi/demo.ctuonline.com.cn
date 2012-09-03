<?php
/* Function: 创建了2个表
 * 1	pre_extraresource 外部资源表。
 * 2    pre_extrastar    外部资源评选表
 * Com.:
 * Author: yangyang
 * Date: 2011-7-21
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_extra_resource;
CREATE TABLE pre_extra_resource (
  id int(10) unsigned NOT NULL auto_increment,
  resourceid int(10) unsigned NOT NULL,
  name varchar(100) NOT NULL default '' COMMENT '名称',
  type varchar(100) NOT NULL COMMENT '类型',
  totalstars float(8) NOT NULL DEFAULT '0' COMMENT '总评分',
  released tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否发布，0-未发布，1-发布',
  sugestuid int(10) unsigned NOT NULL default '0' COMMENT '推荐人id',
  sugestusername varchar(15) NOT NULL COMMENT '推荐人姓名',
  sugestorgid int(10) unsigned NOT NULL default '0' COMMENT '推荐单位id',
  sugestorgname varchar(255) NOT NULL COMMENT '推荐单位名称',
  sugestdateline int(10) unsigned NOT NULL default '0' COMMENT '推荐时间',
  fid mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (id),
  KEY sugestuid (sugestuid),
  KEY fid (fid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pre_extra_class;
CREATE TABLE pre_extra_class (
  id int(10) unsigned NOT NULL auto_increment,
  name varchar(100) NOT NULL default '' COMMENT '名称',
  descr text NOT NULL COMMENT '简介',
  uploadfilename varchar(255) NOT NULL COMMENT '大纲名称',
  uploadfile varchar(255) NOT NULL COMMENT '大纲地址',
  classification varchar(255) DEFAULT NULL COMMENT '课程分类：1-管理类，2-营销类，3-专业类，4-通用类',
  relationorgname text NOT NULL default '' COMMENT '所在机构名称',
  totalstars float(8) NOT NULL DEFAULT '0' COMMENT '总评分',
  starsone float(8) NOT NULL DEFAULT '0' COMMENT '第一项评分',
  starstwo float(8) NOT NULL DEFAULT '0' COMMENT '第二项评分',
  starsthree float(8) NOT NULL DEFAULT '0' COMMENT '第三项评分',
  released tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否发布，0-未发布，1-发布',
  sugestuid int(10) unsigned NOT NULL default '0' COMMENT '推荐人id',
  sugestusername varchar(15) NOT NULL COMMENT '推荐人姓名',
  sugestorgid int(10) unsigned NOT NULL default '0' COMMENT '推荐单位id',
  sugestorgname varchar(255) NOT NULL COMMENT '推荐单位名称',
  viewtimes int(10) unsigned NOT NULL default '0' COMMENT '查看次数',
  sugestdateline int(10) unsigned NOT NULL default '0' COMMENT '推荐时间',
  releaseddateline int(10) unsigned NOT NULL default '0' COMMENT '发布时间',
  fid mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (id),
  KEY sugestuid (sugestuid),
  KEY fid (fid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pre_extra_org;
CREATE TABLE pre_extra_org (
  id int(10) unsigned NOT NULL auto_increment,
  name varchar(100) NOT NULL default '' COMMENT '名称',
  descr text NOT NULL COMMENT '简介',
  uploadfile varchar(255) NOT NULL COMMENT '机构LOGO地址',
  totalstars float(8) NOT NULL DEFAULT '0' COMMENT '总评分',
  starsone float(8) NOT NULL DEFAULT '0' COMMENT '第一项评分',
  starstwo float(8) NOT NULL DEFAULT '0' COMMENT '第二项评分',
  starsthree float(8) NOT NULL DEFAULT '0' COMMENT '第三项评分',
  released tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否发布，0-未发布，1-发布',
  sugestuid int(10) unsigned NOT NULL default '0' COMMENT '推荐人id',
  sugestusername varchar(15) NOT NULL COMMENT '推荐人姓名',
  sugestorgid int(10) unsigned NOT NULL default '0' COMMENT '推荐单位id',
  sugestorgname varchar(255) NOT NULL COMMENT '推荐单位名称',
  sugestdateline int(10) unsigned NOT NULL default '0' COMMENT '推荐时间',
  releaseddateline int(10) unsigned NOT NULL default '0' COMMENT '发布时间',
  fid mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (id),
  KEY sugestuid (sugestuid),
  KEY fid (fid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS pre_extra_lecture;
CREATE TABLE pre_extra_lecture (
  id int(10) unsigned NOT NULL auto_increment,
  oldlecid int(10) unsigned NOT NULL default '0' comment '在讲师库的id',
  name varchar(100) NOT NULL default '' COMMENT '讲师姓名',
  innerlecid int(10) unsigned NOT NULL COMMENT '内部讲师id',
  descr text NOT NULL COMMENT '讲师背景',
  uploadfile varchar(255) NOT NULL COMMENT '讲师照片地址',
  trainingexperince text NOT NULL default '' COMMENT '讲师培训经历',
  trainingtrait text NOT NULL default '' COMMENT '讲师培训特点',
  teachdirection varchar(255) DEFAULT NULL COMMENT '授课方向：1-领导力发展与管理类，2-营销类，3-技术类',
  minfee int(10) unsigned NOT NULL default '0' comment '授课费用下限',
  maxfee int(10) unsigned NOT NULL default '0' comment '授课费用上限',
  telephone varchar(255) DEFAULT NULL COMMENT '联系电话',
  email varchar(255) DEFAULT NULL COMMENT '电子邮箱',
  isinnerlec tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-内部讲师，2-外部讲师',
  gender tinyint(1) NOT NULL DEFAULT '0' COMMENT '性别：0-男，1-女',
  totalstars float(8) NOT NULL DEFAULT '0' COMMENT '总评分',
  starsone float(8) NOT NULL DEFAULT '0' COMMENT '第一项评分',
  starstwo float(8) NOT NULL DEFAULT '0' COMMENT '第二项评分',
  starsthree float(8) NOT NULL DEFAULT '0' COMMENT '第三项评分',
  relationorgname text NOT NULL default '' COMMENT '所在机构名称',
  released tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否发布，0-未发布，1-发布',
  sugestuid int(10) unsigned NOT NULL default '0' COMMENT '推荐人id',
  sugestusername varchar(15) NOT NULL COMMENT '推荐人姓名',
  sugestorgid int(10) unsigned NOT NULL default '0' COMMENT '推荐单位id',
  sugestorgname varchar(255) NOT NULL COMMENT '推荐单位名称',
  sugestdateline int(10) unsigned NOT NULL default '0' COMMENT '推荐时间',
  releaseddateline int(10) unsigned NOT NULL default '0' COMMENT '发布时间',
  fid mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY  (id),
  KEY sugestuid (sugestuid),
  KEY fid (fid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pre_extra_relationship;
CREATE TABLE pre_extra_relationship (
  id int(10) unsigned NOT NULL auto_increment,
  orgid int(10) unsigned NOT NULL default '0' COMMENT '机构id',
  orgname varchar(100) NOT NULL default '' COMMENT '机构名称',
  orglog varchar(255) NOT NULL COMMENT '机构logo地址',
  orgstars float(8) NOT NULL DEFAULT '0' COMMENT '机构评分',
  lecid int(10) unsigned NOT NULL default '0' COMMENT '讲师id',
  lecname varchar(100) NOT NULL default '' COMMENT '讲师姓名',
  lecorg varchar(255) NOT NULL default '' COMMENT '讲师所在机构',
  lecstars float(8) NOT NULL DEFAULT '0' COMMENT '讲师评分',
  classid int(10) unsigned NOT NULL default '0' COMMENT '课程id',
  classname varchar(100) NOT NULL default '' COMMENT '课程姓名',
  classstars float(8) NOT NULL DEFAULT '0' COMMENT '课程评分',
  dateline int(10) unsigned NOT NULL default '0' COMMENT '时间',
  PRIMARY KEY  (id),
  KEY orgid (orgid),
  KEY lecid (lecid),
  KEY classid (classid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS pre_extrastar;
CREATE TABLE pre_extrastar (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `extraid` int(10) unsigned NOT NULL,
  `extratype` varchar(20) NOT NULL,
  totalstars float(8) NOT NULL DEFAULT '0' COMMENT '总评分',
  starsone float(8) NOT NULL DEFAULT '0' COMMENT '第一项评分',
  starstwo float(8) NOT NULL DEFAULT '0' COMMENT '第二项评分',
  starsthree float(8) NOT NULL DEFAULT '0' COMMENT '第三项评分',
  `uid` int(10) unsigned NOT NULL,
   dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`id`),
   KEY extraid (extraid),
   KEY uid (uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


EOF;

runquery($sql);

$finish = TRUE;
?>
