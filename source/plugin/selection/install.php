<?php
/* Function: 创建了4个表
 * 1	pre_selection 评选表。
 * 2	pre_selection_option 评选候选项。
 * 3	pre_selection_record 用户评选记录表。
 * Com.:
 * Author: yangyang
 * Date: 2010-7-19
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_selection;
CREATE TABLE pre_selection (
  selectionid mediumint(8) unsigned NOT NULL auto_increment,
  selectionname varchar(100) NOT NULL default '',
  selectiondescr text NOT NULL,
  selectiontimeflag tinyint(1) NOT NULL DEFAULT '0',
  selectionstartdate int(10) unsigned NOT NULL DEFAULT '0',
  selectionenddate int(10) unsigned NOT NULL DEFAULT '0',
  voteNum smallint(6) default '0',
  votecreatetime smallint(6) default '0',/*生成时间*/
  votecreatetype tinyint(1) default '0',/*生成类型，0，分钟，1，小时，2，天*/
  votebatchflag tinyint(1) default '0',/*投票是否可分批完成 0,不可，1可以*/
  voterepeatflag tinyint(1) default '0',/*选项可被重复投票 0，不可，1可以*/
  showvoteflag tinyint(1) default '0',/*显示选项已有票数 0，不可，1可以*/
  showrecordflag tinyint(1) default '0',/*无记名投票 0，不使用无记名，1使用无记名*/
  ordertype tinyint(1) NOT NULL DEFAULT '0',/*排序方式，0，不排序，1，以笔画排序，2，以拼音排序*/
  imgurl varchar(255) NOT NULL default '',
  fid smallint(6) default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  classid smallint(6) unsigned NOT NULL DEFAULT '0',
  moderated tinyint(1) NOT NULL DEFAULT '0',
  scored mediumint(6) NOT NULL DEFAULT '0',
  digest tinyint(1) NOT NULL DEFAULT '0',
  displayorder tinyint(1) NOT NULL DEFAULT '0',
  highlight tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (selectionid),
  KEY uid (uid),
  KEY fid (fid),
  KEY classid (classid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pre_selection_option;
CREATE TABLE pre_selection_option (
  optionid mediumint(8) NOT NULL auto_increment,
  optiondescr text NOT NULL,
  optionname varchar(100)binary NOT NULL default '',
  url varchar(255) NOT NULL default '',
  optionlimitid mediumint(8) NOT NULL DEFAULT '0',
  optionlimitname varchar(100) NOT NULL default '',
  selectionid mediumint(8) default '0',
  dateline int(10) unsigned NOT NULL default '0',
  ordernum mediumint(6) unsigned NOT NULL default '0',
  ordertype tinyint(1) NOT NULL DEFAULT '0',/*排序方式，0，不排序，1，以笔画排序，2，以拼音排序*/
  scored mediumint(6) NOT NULL DEFAULT '0',/*获得的投票数*/
  moderated tinyint(1) NOT NULL DEFAULT '0',
  fid smallint(6) default '0',
  PRIMARY KEY  (optionid),
  KEY selectionid (selectionid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pre_selection_record;
CREATE TABLE pre_selection_record (
  recordid mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  selectionid mediumint(8) unsigned NOT NULL DEFAULT '0',
  optionid mediumint(8) unsigned NOT NULL DEFAULT '0',
  optiondescr varchar(200) NOT NULL DEFAULT '',
  votenum mediumint(8) NOT NULL DEFAULT '1',
  uid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (recordid),
  KEY selectionid (selectionid),
  KEY optionid (optionid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


/**
*用户可以投票的数量
*/
DROP TABLE IF EXISTS pre_selection_user_vote_num;
CREATE TABLE pre_selection_user_vote_num (
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  selectionid mediumint(8) unsigned NOT NULL DEFAULT '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  num mediumint(8) unsigned NOT NULL DEFAULT '0',
  usednum mediumint(8) unsigned NOT NULL DEFAULT '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (id),
  KEY selectionid (selectionid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOF;

runquery($sql);

$finish = TRUE;
?>
