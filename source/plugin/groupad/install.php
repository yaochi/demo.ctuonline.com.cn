<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: install.php 8889 2010-04-23 07:48:22Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_groupad;
CREATE TABLE IF NOT EXISTS pre_groupad (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  display_order tinyint(4) NOT NULL DEFAULT '0',
  is_display tinyint(1) NOT NULL DEFAULT '1',
  title varchar(255) NOT NULL,
  ad_type int(11) DEFAULT '0',
  media_dir varchar(255) DEFAULT NULL,
  media_url varchar(255) DEFAULT NULL,
  media_style enum('text','image','flash') NOT NULL,
  content text,
  width smallint(6) NOT NULL,
  height smallint(6) NOT NULL,
  uid mediumint(8) unsigned NOT NULL,
  group_id mediumint(8) unsigned NOT NULL,
  update_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY IDX_AD_GID_CATAGORY (group_id,ad_type)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

EOF;

runquery($sql);

$finish = TRUE;

?>