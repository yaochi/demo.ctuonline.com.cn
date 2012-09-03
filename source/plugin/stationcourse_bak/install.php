<?php
/* Function: 创建了3个表
 * 1    station 岗位表
 * 2    station_course 岗位课程表
 * 3	user_courses 用户课程表。
 * Com.:
 * Author: qiaoyz
 * Date: 2011-4-8
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$station = DB :: table('station');
$courses = DB :: table('courses');
$station_course = DB :: table('station_course');
$user_station =DB :: table('user_station');


$sql = <<<EOF

DROP TABLE IF EXISTS $station;
CREATE TABLE $station (
  `id`  smallint(6) NOT NULL auto_increment,
  `fid` smallint(6) NOT NULL default '0',
  `type` tinyint(1) NOT NULL default '1',
  `name` varchar(40) NOT NULL default '',
  `parent_id`  smallint(6) NOT NULL,
  `parent_name` varchar(40) NOT NULL default '',
  `create_uid` mediumint(8) NOT NULL default '0',
  `create_time` int(10) NOT NULL default '0',
  `update_uid` mediumint(8) NOT NULL default '0',
  `update_time` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS $courses;
CREATE TABLE $courses (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `fid` smallint(6) NOT NULL default '0',
  `course_id` varchar(25) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `course_type` varchar(20) DEFAULT NULL,
  `course_url` varchar(255) NOT NULL default '',
  `introduction` varchar(1000) default '',
  `cai_type` varchar(10) DEFAULT NULL,
  `cai_sourse` varchar(255) DEFAULT NULL,
  `class_hour` tinyint(4) DEFAULT NULL,
  `average` float DEFAULT NULL,
  `recommend` tinyint(4) DEFAULT NULL,
  `upload_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`,`course_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS $station_course;
CREATE TABLE $station_course (
  `id`  smallint(6) NOT NULL auto_increment,
  `fid` smallint(6) NOT NULL default '0',
  `station_id`  smallint(6) NOT NULL,
  `station_name` varchar(40) NOT NULL default '',
  `course_id` varchar(40) NOT NULL default '',
  `course_name` varchar(255) NOT NULL default '',
  `create_uid` mediumint(8) NOT NULL default '0',
  `create_time` int(10) NOT NULL default '0',
  `update_uid` mediumint(8) NOT NULL default '0',
  `update_time` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS $user_station;
CREATE TABLE $user_station (
  `id`  smallint(6) NOT NULL auto_increment,
  `uid` mediumint(8) NOT NULL default '0',
  `username` varchar(15) NOT NULL default '',
  `fid` smallint(6) NOT NULL default '0',
  `station_id`  smallint(6) NOT NULL,
  `station_name` varchar(40) NOT NULL default '',
  `type` tinyint(1) NOT NULL default '1',
  `update_uid` mediumint(8) NOT NULL default '0',
  `update_time` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


EOF;

runquery($sql);

$finish = TRUE;
?>
