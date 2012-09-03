<?php
/* Function:
 * Com.:
 * Author: yangyang
 * Date:2011-3-22 
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_synchro_cert_info;
CREATE TABLE pre_synchro_cert_info (
  id mediumint(8) unsigned NOT NULL auto_increment,
  Certificate_id varchar(32)  NOT NULL default '',
  Certificate_no varchar(30) NOT NULL default '',
  Certificate_type  tinyint(1) NOT NULL DEFAULT '0',
  User_id mediumint(10) NOT NULL DEFAULT '0',
  Regname varchar(20) NOT NULL DEFAULT '',
  TC_name varchar(70) NOT NULL DEFAULT '',
  Create_date int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY User_id (User_id),
  KEY Certificate_id (Certificate_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


EOF;

runquery($sql);

$finish = TRUE;
?>
