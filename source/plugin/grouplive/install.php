<?php
/* Function: 创建了1个表
 * 1	group_live 专区直播表。
 * Com.:
 * Author: wuhan
 * Date: 2010-7-23
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$table_live = DB :: table('group_live');

$sql = <<<EOF

DROP TABLE IF EXISTS $table_live;
CREATE TABLE $table_live (
  `liveid` mediumint(8) NOT NULL auto_increment,
  `fid` smallint(6) NOT NULL default '0',
  `uid` mediumint(8) NOT NULL default '0',
  `username` varchar(15) NOT NULL default '',
  `type` tinyint(1) NOT NULL default '0',
  `subject` varchar(80) NOT NULL default '',
  `starttime` int(10) NOT NULL default '0',
  `endtime` int(10) NOT NULL default '0',
  `url` varchar(255) NOT NULL default '',
  `firstman_ids` text NOT NULL,
  `secondman_ids` text NOT NULL,
  `guest_ids` text NOT NULL,
  `dateline` int(10) NOT NULL default '0',
  `viewnum` mediumint(8) NOT NULL default '0',
  `playnum` mediumint(8) NOT NULL default '0',
  `displayorder` tinyint(1) NOT NULL default '0',
  `highlight` tinyint(1) NOT NULL default '0',
  `digest` tinyint(1) NOT NULL default '0',
  `lastpost` int(10) NOT NULL default '0',
  `moderated` tinyint(1) NOT NULL,
  `friend` tinyint(1) NOT NULL default '0',
  `password` varchar(10) NOT NULL default '',
  `target_ids` text NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `hot` mediumint(8) NOT NULL default '0',
  `typeid` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`liveid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

EOF;

runquery($sql);

$finish = TRUE;
?>
