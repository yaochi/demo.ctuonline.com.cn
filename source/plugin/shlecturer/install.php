<?php
/* Function: 创建了3个表
 * 1	pre_shlecture 上海专区讲师表。
 * 2	pre_shlecture_direct 讲师授课记录表。
 * 3	pre_shlecture_stars 讲师好评记录表。
 * Com.:
 * Author: yangyang
 * Date: 2010-5-16
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_shlecture;
CREATE TABLE IF NOT EXISTS `pre_shlecture` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '讲师表自增ID',
  `tempusername` varchar(255) NOT NULL COMMENT '讲师账号',
  `name` varchar(255) NOT NULL COMMENT '讲师名称',
  `gender` tinyint(1) NOT NULL COMMENT '讲师性别：0-保密，1-男，2-女',
  `rank` varchar(255) DEFAULT NULL COMMENT '讲师级别：内部讲师（1-集团，2-公司级，3-直属单位级，4-其他），外部讲师（1-客座教授，2-荣誉教授，3-专家教授，4-讲师）',
  `orgname` varchar(255) NOT NULL COMMENT '单位名称',
  `tel` varchar(255) DEFAULT NULL COMMENT '联系电话',
  `email` varchar(255) DEFAULT NULL COMMENT '电子邮箱',
  `isinnerlec` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-内部讲师，2-外部讲师',
  `coursexperience` TEXT COMMENT '授课经验',
  `imgurl` varchar(255) DEFAULT NULL COMMENT '头像',
  `bginfo` varchar(255) DEFAULT NULL COMMENT '背景介绍',
  `uid` int(10) DEFAULT NULL COMMENT '创建讲师的用户的ID',
  `dateline` int(10) unsigned NOT NULL COMMENT '创建时间',
  `stars` float(8) NOT NULL DEFAULT '0' COMMENT '星级',
  `perspecial` varchar(255) NOT NULL COMMENT '个人特色',
  PRIMARY KEY (`id`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pre_shlecture_direct;
CREATE TABLE pre_shlecture_direct (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lecid` int(10) unsigned NOT NULL,
  `teachdirection` varchar(255) DEFAULT NULL COMMENT '授课方向：1-市场类，2-销售与服务类，3-产品类，4-企业信息化类，5-维护与服务支撑类，6-网发建设类，7-综合类',
  `courses` TEXT COMMENT '已授课程',
  PRIMARY KEY (`id`),
   KEY lecid (lecid)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pre_shlecture_stars;
CREATE TABLE pre_shlecture_stars (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lecid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
   KEY lecid (lecid),
   KEY uid (uid)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOF;

runquery($sql);

$finish = TRUE;

?>