<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_resourcelist;
CREATE TABLE IF NOT EXISTS `pre_resourcelist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '资源表自增ID',
  `resourceid` int(10) DEFAULT NULL COMMENT '知识中心资源id',
  `title` varchar(255) NOT NULL COMMENT '资源名称',
  `type` varchar(255) NOT NULL COMMENT '资源类型：课程，文档，案例',
  `typeid` tinyint(1) NOT NULL COMMENT '资源类型ID：4-课程，1-文档，2-案例',
  `kcategoryid` int(10) NOT NULL COMMENT '知识分类编号',
  `kcategoryname` varchar(255) DEFAULT NULL COMMENT '知识分类名称',
  `fcategoryid` int(10) NOT NULL COMMENT '专区分类编号',
  `fcategoryname` varchar(255) DEFAULT NULL COMMENT '专区分类名称',
  `imglink` varchar(255) DEFAULT NULL COMMENT '资源缩略图URL',
  `titlelink` varchar(255) DEFAULT NULL COMMENT '资源URL',
  `fixobject` varchar(255) DEFAULT NULL COMMENT '适用对象',
  `keyword` varchar(255) DEFAULT NULL COMMENT '关键字',
  `orgname` varchar(255) DEFAULT NULL COMMENT '上传机构名称',
  `about` varchar(255) DEFAULT NULL COMMENT '课程介绍',
  `fid` int(10) DEFAULT NULL COMMENT '创建专区资源的专区id',
  `fname` varchar(255) DEFAULT NULL COMMENT '创建专区资源的专区名称',
  `uid` int(10) DEFAULT NULL COMMENT '创建专区资源的用户的ID',
  `uploaddate` int(10) unsigned NOT NULL COMMENT '上传时间',
  `dateline` int(10) unsigned NOT NULL COMMENT '创建时间',
  `displayorder` tinyint(1) NOT NULL DEFAULT '0' COMMENT '置顶',
  `digest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '精华',
  `highlight` tinyint(1) NOT NULL DEFAULT '0' COMMENT '高亮',
  `moderated` tinyint(1) NOT NULL DEFAULT '0' COMMENT '',
  `readnum` int(10) NOT NULL DEFAULT '0' COMMENT '阅读次数',
  `sharenum` int(10) NOT NULL DEFAULT '0' COMMENT '分享次数',
  `favoritenum` int(10) NOT NULL DEFAULT '0' COMMENT '收藏次数',
  `commentnum` int(10) NOT NULL DEFAULT '0' COMMENT '评论次数',
  `downloadnum` int(10) NOT NULL DEFAULT '0' COMMENT '下载次数',
  `averagescore` varchar(255) DEFAULT NULL COMMENT '平均分',
  PRIMARY KEY (`id`)
)ENGINE = InnoDB;

EOF;

runquery($sql);

$finish = TRUE;

?>