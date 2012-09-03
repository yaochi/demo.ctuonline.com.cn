<?php
/* 
 * 1	pre_authenticated_users 认证用户表
 * Com.:
 * Author: yangyang
 * Date: 2012-7-11 
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_authenticated_users;
CREATE TABLE pre_authenticated_users (
  id int(10) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  realname varchar(40) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY uid (uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


EOF;

runquery($sql);

$finish = TRUE;
?>
