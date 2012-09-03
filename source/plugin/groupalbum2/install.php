<?php
/* Function: 创建了3个表
 * 1	group_album 专区相册表。
 * 2	group_pic 专区相册图片表。
 * 3	group_picfield 专区相册图片附加信息表。
 * Com.:
 * Author: wuhan
 * Date: 2010-7-16
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$table_album = DB :: table('group_album');
$table_pic = DB :: table('group_pic');
$table_picfield = DB :: table('group_picfield');

$sql = <<<EOF

DROP TABLE IF EXISTS $table_album;
CREATE TABLE $table_album (
  `albumid` mediumint(8) unsigned NOT NULL auto_increment,
  `albumname` varchar(50) NOT NULL default '',
  `catid` smallint(6) unsigned NOT NULL default '0',
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `username` varchar(15) NOT NULL default '',
  `dateline` int(10) unsigned NOT NULL default '0',
  `updatetime` int(10) unsigned NOT NULL default '0',
  `picnum` smallint(6) unsigned NOT NULL default '0',
  `pic` varchar(60) NOT NULL default '',
  `picflag` tinyint(1) NOT NULL default '0',
  `friend` tinyint(1) NOT NULL default '0',
  `password` varchar(10) NOT NULL default '',
  `target_ids` text NOT NULL,
  `fid` smallint(6) default '0',
  PRIMARY KEY  (`albumid`),
  KEY `uid` (`uid`,`updatetime`),
  KEY `updatetime` (`updatetime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS $table_pic;
CREATE TABLE $table_pic (
  `picid` mediumint(8) NOT NULL auto_increment,
  `albumid` mediumint(8) unsigned NOT NULL default '0',
  `uid` mediumint(8) unsigned NOT NULL default '0',
  `username` varchar(15) NOT NULL default '',
  `dateline` int(10) unsigned NOT NULL default '0',
  `postip` varchar(255) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `type` varchar(255) NOT NULL default '',
  `size` int(10) unsigned NOT NULL default '0',
  `filepath` varchar(255) NOT NULL default '',
  `thumb` tinyint(1) NOT NULL default '0',
  `remote` tinyint(1) NOT NULL default '0',
  `hot` mediumint(8) unsigned NOT NULL default '0',
  `magicframe` tinyint(6) NOT NULL default '0',
  `click1` smallint(6) NOT NULL default '0',
  `click2` smallint(6) NOT NULL default '0',
  `click3` smallint(6) NOT NULL default '0',
  `click4` smallint(6) NOT NULL default '0',
  `click5` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`picid`),
  KEY `albumid` (`albumid`,`dateline`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS $table_picfield;
CREATE TABLE $table_picfield (
  `picid` mediumint(8) unsigned NOT NULL default '0',
  `hotuser` text NOT NULL,
  PRIMARY KEY  (`picid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOF;

runquery($sql);

$finish = TRUE;
?>
