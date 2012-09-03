<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_notice;
CREATE TABLE pre_notice (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT,
  `imgurl` VARCHAR(255) DEFAULT NULL,
  `status` INTEGER UNSIGNED NOT NULL,
  `create_time` INTEGER UNSIGNED NOT NULL,
  `update_time` INTEGER UNSIGNED NOT NULL,
  `group_id` INTEGER UNSIGNED NOT NULL,
  `category_id` INTEGER UNSIGNED NOT NULL,
  `uid` INTEGER UNSIGNED NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  `displayorder` tinyint(1) NOT NULL DEFAULT '0',
  `digest` tinyint(1) NOT NULL DEFAULT '0',
  `highlight` tinyint(1) NOT NULL DEFAULT '0',
  `moderated` tinyint(1) NOT NULL DEFAULT '0',
  `viewnum` INTEGER UNSIGNED NOT NULL DEFAULT '0',
  `replynum` INTEGER UNSIGNED NOT NULL DEFAULT '0',
  `repliesdisplayoff` tinyint(1) NOT NULL DEFAULT '0',
  `repliesoff` tinyint(1) NOT NULL DEFAULT '0',
  `ip` VARCHAR(20),
  `click1` smallint(6) NOT NULL default '0',
  `click2` smallint(6) NOT NULL default '0',
  `click3` smallint(6) NOT NULL default '0',
  `click4` smallint(6) NOT NULL default '0',
  `click5` smallint(6) NOT NULL default '0',
  PRIMARY KEY (`id`)
)ENGINE = InnoDB;

DROP TABLE IF EXISTS pre_notice_type;
CREATE TABLE pre_notice_type (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `group_id` INTEGER UNSIGNED NOT NULL,
  `create_time` INTEGER UNSIGNED NOT NULL,
  `uid` INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
)ENGINE = InnoDB;

DROP TABLE IF EXISTS pre_notice_userstands;
CREATE TABLE pre_notice_userstands (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `nid` INTEGER UNSIGNED NOT NULL,
  `click1` tinyint(1) NOT NULL DEFAULT '0',
  `click2` tinyint(1) NOT NULL DEFAULT '0',
  `click3` tinyint(1) NOT NULL DEFAULT '0',
  `click4` tinyint(1) NOT NULL DEFAULT '0',
  `click5` tinyint(1) NOT NULL DEFAULT '0',
  `create_time` INTEGER UNSIGNED NOT NULL,
  `uid` INTEGER UNSIGNED NOT NULL,
  `username` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
)ENGINE = InnoDB;

EOF;

runquery($sql);

$finish = TRUE;

?>