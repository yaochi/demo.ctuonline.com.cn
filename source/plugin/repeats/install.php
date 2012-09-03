<?php
/* Function: 创建了2个表
 * 1	pre_forum_repeats 专区马甲表。
 * 2	pre_repeats_relation 马甲关系表。
 * Com.:
 * Author: yangyang
 * Date: 2012-1-10 
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_forum_repeats;
CREATE TABLE pre_forum_repeats (
  id mediumint(8) unsigned NOT NULL auto_increment,
  fid int(10) unsigned NOT NULL default '0',
  name varchar(200) NOT NULL DEFAULT '',
  dateline int(10) NOT NULL default '0',
  updateline int(10) NOT NULL default '0',
  switch tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY fid (fid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pre_repeats_relation;
CREATE TABLE pre_repeats_relation (
  id mediumint(8) unsigned NOT NULL auto_increment,
  repeatsid mediumint(8) unsigned NOT NULL default '0',
  fid int(10) unsigned NOT NULL default '0',
  uid int(10) unsigned NOT NULL DEFAULT '0',
  realname varchar(255) NOT NULL ,
  dateline int(10) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY repeatsid (repeatsid),
  KEY uid (uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


EOF;

runquery($sql);

$finish = TRUE;
?>
