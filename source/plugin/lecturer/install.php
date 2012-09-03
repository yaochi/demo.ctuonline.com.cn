<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_lecturer;
CREATE TABLE IF NOT EXISTS `pre_lecturer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '讲师表自增ID',
  `lecid` int(10) DEFAULT NULL COMMENT '内部讲师的uid',
  `name` varchar(255) NOT NULL COMMENT '讲师名称',
  `ischeck` tinyint(1) NOT NULL DEFAULT '2' COMMENT '审核状态：1-已审核，2-未审核',
  `isapprove` tinyint(1) NOT NULL DEFAULT '2' COMMENT '认证状态：1-已认证，2-未认证',
  `gender` tinyint(1) NOT NULL COMMENT '讲师性别：0-保密，1-男，2-女',
  `evaluation` varchar(255) DEFAULT NULL COMMENT '讲师评价',
  `rank` varchar(255) DEFAULT NULL COMMENT '讲师级别：内部讲师（1-集团，2-省级，3-地市级，4-其他），外部讲师（1-客座教授，2-荣誉教授，3-专家教授，4-讲师）',
  `orgid` int(10) NOT NULL COMMENT '机构ID',
  `orgname` varchar(255) NOT NULL COMMENT '机构名称',
  `position` varchar(255) DEFAULT NULL COMMENT '岗位',
  `tel` varchar(255) DEFAULT NULL COMMENT '联系电话',
  `email` varchar(255) DEFAULT NULL COMMENT '电子邮箱',
  `iscollegelec` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-学院讲师，2-非学院讲师',
  `isinnerlec` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-内部讲师，2-外部讲师',
  `experience` varchar(255) DEFAULT NULL COMMENT '工作经历',
  `coursexperience` varchar(255) DEFAULT NULL COMMENT '授课经验',
  `teachdirection` varchar(255) DEFAULT NULL COMMENT '授课方向：1-领导力发展与管理类，2-营销类，3-技术类，4-通用类',
  `imgurl` varchar(255) DEFAULT NULL COMMENT '头像',
  `about` varchar(255) DEFAULT NULL COMMENT '讲师介绍',
  `courses` TEXT COMMENT '已授课程',
  `fid` int(10) DEFAULT NULL COMMENT '专区讲师所在专区的ID',
  `fname` varchar(255) DEFAULT NULL COMMENT '专区讲师所在专区名',
  `uid` int(10) DEFAULT NULL COMMENT '创建讲师的用户的ID',
  `dateline` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
)ENGINE = InnoDB;

DROP TABLE IF EXISTS pre_forum_forum_lecturer;
CREATE TABLE pre_forum_forum_lecturer (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `lecid` INTEGER UNSIGNED NOT NULL,
  `lecname` VARCHAR(255) NOT NULL,
  `imgurl` VARCHAR(255),
  `fid` INTEGER UNSIGNED NOT NULL,
  `fname` VARCHAR(255) NOT NULL,
  `about` VARCHAR(255) NOT NULL,
  `dateline` INTEGER UNSIGNED NOT NULL,
  `displayorder` tinyint(1) NOT NULL DEFAULT '0',
  `highlight` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
)ENGINE = InnoDB;

EOF;

runquery($sql);

$finish = TRUE;

?>