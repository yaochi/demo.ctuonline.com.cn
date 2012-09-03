<?php
/* Function: 创建了4个表
 * 1	pre_questionary 专区问卷表。
 * 2	pre_questionary_question 专区问卷问题表。
 * 3	pre_questionary_questionoption 专区问题选项表。
 * 4    pre_questionary_questionchoicers 专区用户答题记录表。
 * 5    pre_questionary_class   问卷分类表。
 * Com.:
 * Author: yangyang
 * Date: 2010-7-19
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_questionary;
CREATE TABLE pre_questionary (
  questid mediumint(8) unsigned NOT NULL auto_increment,
  questname varchar(100) NOT NULL default '',
  questdescr text NOT NULL,
  visible tinyint(1) NOT NULL DEFAULT '0',
  fid smallint(6) default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  joiner  mediumint(8) unsigned NOT NULL default '0',
  classid smallint(6) unsigned NOT NULL DEFAULT '0',
  digest tinyint(1) NOT NULL DEFAULT '0',
  displayorder tinyint(1) NOT NULL DEFAULT '0',
  highlight tinyint(1) NOT NULL DEFAULT '0',
  moderated tinyint(1) NOT NULL DEFAULT '0',
  scored tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (questid),
  KEY uid (uid),
  KEY fid (fid),
  KEY classid (classid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pre_questionary_question;
CREATE TABLE pre_questionary_question (
  questionid mediumint(8) NOT NULL auto_increment,
  question text NOT NULL,
  questiondescr text NOT NULL,
  multiple tinyint(1) NOT NULL DEFAULT '0',
  maxchoices tinyint(3) unsigned NOT NULL DEFAULT '0',
  questid mediumint(8) default '0',
  PRIMARY KEY  (questionid),
  KEY questid (questid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pre_questionary_questionoption;
CREATE TABLE pre_questionary_questionoption (
  qoptionid int(10) unsigned NOT NULL AUTO_INCREMENT,
  questionid mediumint(8) unsigned NOT NULL DEFAULT '0',
  questionoption varchar(200) NOT NULL DEFAULT '',
  descr varchar(200) NOT NULL DEFAULT '',
  weight int(10) NOT NULL DEFAULT '0',
  choices mediumint(8) unsigned NOT NULL DEFAULT '0',
  choicerids mediumtext NOT NULL,
  questid mediumint(8) default '0',
  PRIMARY KEY (qoptionid),
  KEY questionid (questionid,weight)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pre_questionary_questionchoicers;
CREATE TABLE pre_questionary_questionchoicers (
  questid mediumint(8) unsigned NOT NULL DEFAULT '0',
  questionid mediumint(8) unsigned NOT NULL DEFAULT '0',
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  username varchar(15) NOT NULL DEFAULT '',
  options text NOT NULL,
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  KEY questid (questid),
  KEY questionid(questionid),
  KEY uid (uid,dateline)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pre_questionary_class;
CREATE TABLE pre_questionary_class(
	classid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  	classname char(40) NOT NULL DEFAULT '',
  	uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  	dateline int(10) unsigned NOT NULL DEFAULT '0',
	fid smallint(6) default '0',
  	PRIMARY KEY (classid),
  	KEY fid (fid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


EOF;

runquery($sql);

$finish = TRUE;
?>
