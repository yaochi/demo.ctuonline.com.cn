<?php
/*
 * Com.:
 * Author: qiaoyz
 * Date: 2012-8-1
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$station = DB :: table('sc_station');
$ustation = DB :: table('sc_ustation');
$relation = DB :: table('sc_relation');
$record = DB :: table('sc_record');

$sql = <<<EOF
DROP TABLE IF EXISTS $station;
CREATE TABLE $station (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(50) NOT NULL,
	`pname` VARCHAR(255) NOT NULL,
	`level` INT(8) NOT NULL,
	`parent_id` INT(11) NOT NULL,
	`status` INT(8) NOT NULL DEFAULT 0,
	`num` INT(8)  NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS $ustation;
CREATE TABLE $ustation (
	`uid` INT(11) NOT NULL,
	`regname` VARCHAR(20) NOT NULL,
	`station_id` INT(11) NOT NULL,
	`status` INT(8) NOT NULL DEFAULT 0,
	PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS $relation;
CREATE TABLE $relation (
	`station_id` INT(11) NOT NULL,
	`coursecode` VARCHAR(30) NOT NULL,
	`coursename` VARCHAR(255),
	`sequence` INT(8) NOT NULL,
	PRIMARY KEY (`station_id`,`coursecode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS $record;
CREATE TABLE $record (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`uid` INT(11) NOT NULL,
	`username` VARCHAR(50) NOT NULL,
	`realname` VARCHAR(50) NOT NULL,
	`status` INT(8) NOT NULL,
	`dateline` INT(11) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOF;

runquery($sql);

$finish = TRUE;
?>
