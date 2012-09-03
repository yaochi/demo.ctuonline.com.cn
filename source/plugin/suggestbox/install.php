<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$integral =DB :: table('integral');
$suggestbox =DB :: table('suggestbox');
$respond =DB :: table('respond');


$sql = <<<EOF

DROP TABLE IF EXISTS $suggestbox;
CREATE TABLE $suggestbox (
  `suggestId`  int NOT NULL auto_increment,
   `uid`    int  NOT NULL default '0',
   `name` varchar(40) NOT NULL default '',
  `regname` char(15) NOT NULL default '',
  `deptname` varchar(40)  default '',
  `corpname` varchar(40)  default '',
  `telephone` varchar(40)  default '',
  `duty` varchar(40)  default '',
  `suggest` varchar(500) NOT NULL default '',
  `createdate` int(10) NOT NULL default '0',
   `passdate` int(10) NOT NULL default '0',
  `fid` int(10) NOT NULL default '0',
  `status` int  NOT NULL default '0',
  `views` int  NOT NULL default '0',
  `respond` int  NOT NULL default '0',
  `review` text,
  PRIMARY KEY  (`suggestId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


EOF;

runquery($sql);

$finish = TRUE;

?>