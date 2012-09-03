<?php
/* Function: 创建了1个表
 * 1    sharesource 课程分享表
 * Com.:
 * Author: qiaoyz
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$table = DB :: table('sharesource');
$table2 = DB :: table('share_province');

$sql = <<<EOF

DROP TABLE IF EXISTS $table;
CREATE TABLE $table (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `regname` varchar(20) NOT NULL DEFAULT '',
  `realname` varchar(30) NOT NULL DEFAULT '',
  `contactor` varchar(30) NOT NULL DEFAULT '',
  `contactway` varchar(100) NOT NULL DEFAULT '',
  `province` varchar(40) NOT NULL DEFAULT '',
  `dept` varchar(60) NOT NULL DEFAULT '',
  `companyname` varchar(60) NOT NULL DEFAULT '',
  `companyway` varchar(60) NOT NULL DEFAULT '',
  `trainobj` varchar(60) NOT NULL DEFAULT '',
  `coursename` varchar(255) NOT NULL DEFAULT '',
  `category` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `lecturer` varchar(80) NOT NULL DEFAULT '',
  `lecturerinfo` varchar(100) NOT NULL DEFAULT '',
  `traintime` varchar(100) NOT NULL DEFAULT '',
  `advantage` varchar(900) NOT NULL DEFAULT '',
  `defect` varchar(900) NOT NULL DEFAULT '',
  `degree` float DEFAULT '0',
  `exp` varchar(900) NOT NULL DEFAULT '',
  `viewnum` int(11) NOT NULL DEFAULT '0',
  `commentnum` int(11) NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `lastuid` int(11) NOT NULL DEFAULT '0',
  `lastname` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS $table2;
CREATE TABLE $table2 (
  `uid` int(11) NOT NULL DEFAULT '0',
  `regname` varchar(20) NOT NULL DEFAULT '',
  `realname` varchar(30) NOT NULL DEFAULT '',
  `province` varchar(40) NOT NULL DEFAULT '',
  `num` int(11) NOT NULL DEFAULT '0',
   PRIMARY KEY (`regname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOF;
runquery($sql);
$finish = TRUE;
?>