<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$learning_excitation =DB :: table('learning_excitation');

$sql1 = <<<EOF

DROP TABLE IF EXISTS $learning_excitation;
CREATE TABLE $learning_excitation (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `subusername` char(15) NOT NULL DEFAULT '',
  `subrealname` varchar(255) NOT NULL DEFAULT '',
  `subdeptname` varchar(255) NOT NULL DEFAULT '',
  `subcompanyname` varchar(255) NOT NULL DEFAULT '',
  `subtel` char(15) NOT NULL DEFAULT '',
  `subPost` varchar(255) NOT NULL DEFAULT '',
  `learnsource` text NOT NULL,
  `learnHarvest` text NOT NULL,
  `learnaction` text NOT NULL,
  `learnachievements` text NOT NULL,
  `learnname` varchar(255) NOT NULL DEFAULT '',
  `Witnessrealname` varchar(255) NOT NULL DEFAULT '',
  `Witnessusername` char(15) NOT NULL DEFAULT '',
  `Witnessdeptname` varchar(255) NOT NULL DEFAULT '',
  `Witnesscompanyname` varchar(255) NOT NULL DEFAULT '',
  `Witnesstel` char(15) NOT NULL DEFAULT '',
  `WitnessPost` varchar(255) NOT NULL DEFAULT '',
  `subdateline` int(10) unsigned NOT NULL DEFAULT '0',
  `examinestatus` int(10) unsigned NOT NULL DEFAULT '1',
  `examinedateline` int(10) unsigned NOT NULL DEFAULT '0',
  `fid` int(11) NOT NULL DEFAULT '0',
  `pageviews` int  default '0',
  `confidenceindex` int NOT null default '0',
  `remindstat` int not null default '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



EOF;

runquery($sql1);



$learn_credit =DB :: table('learn_credit');
$sql2 = <<<EOF
DROP TABLE IF EXISTS $learn_credit;
CREATE TABLE $learn_credit (
  `uid` int(11) NOT NULL DEFAULT '0',
  `username` varchar(255) NOT NULL DEFAULT '',
  `realname` char(15) NOT NULL DEFAULT '',
  `totalcredit` int(11) NOT NULL DEFAULT '0',
  `exchangecredit` int(11) NOT NULL DEFAULT '0',
  `company` varchar(255) NOT NULL DEFAULT '',
  `fid` int(11) NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOF;

runquery($sql2);


$opinion_reply =DB :: table('opinion_reply');
$sql3 = <<<EOF
DROP TABLE IF EXISTS $opinion_reply;
CREATE TABLE $opinion_reply (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `username` varchar(255) NOT NULL DEFAULT '',
  `realname` varchar(255) NOT NULL DEFAULT '',
  `replmessage` text,
  `replydateline` int(11) NOT NULL DEFAULT '0',
  `authorer` varchar(255) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  `learnmvid` int(11) NOT NULL DEFAULT '0',
  `optionid` int(11) NOT NULL DEFAULT '0',
  `authoreruid` int(11) NOT NULL DEFAULT '0',
  `tap` int(11) NOT NULL DEFAULT '0',
  `fid` int(11) NOT NULL DEFAULT '0',
  `isadmin` int not null default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOF;

runquery($sql3);


$record =DB :: table('learncredit_record');
$sql4 = <<<EOF

DROP TABLE IF EXISTS $record;
CREATE TABLE $record (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `username` varchar(255) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `mode` int(11) NOT NULL DEFAULT '0',
  `objectid` int(11) NOT NULL DEFAULT '0',
  `credit` int(11) NOT NULL DEFAULT '0',
  `fid` int(11) NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOF;
runquery($sql4);


$harvestoption =DB :: table('larnsouce_harvestoption');
$sql5 = <<<EOF

DROP TABLE IF EXISTS $harvestoption;
create table $harvestoption
(
id int not null auto_increment,
optionmessage text,
type int  not null default '0',
learnid int  not null default '0',
authorer char(15) not null default '',
athoreruid int not null default '0',
dataline int not null default '0',
status int not null default '0',
primary key (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOF;
runquery($sql5);

$learn_attachment =DB :: table('learn_attachment');
$sql6 = <<<EOF
DROP TABLE IF EXISTS $learn_attachment;
CREATE TABLE $learn_attachment (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `width` int(11) NOT NULL DEFAULT '0',
  `dateline` int(11) NOT NULL DEFAULT '0',
  `filename` char(100) NOT NULL DEFAULT '',
  `filetype` char(50) NOT NULL DEFAULT '',
  `filesize` int(11) NOT NULL DEFAULT '0',
  `attachment` char(100) NOT NULL DEFAULT '',
  `downloads` int(11) NOT NULL DEFAULT '0',
  `isimage` int(11) NOT NULL DEFAULT '0',
  `thumb` int(11) NOT NULL DEFAULT '0',
  `remote` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `learid` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
EOF;
runquery($sql6);
$finish = TRUE;

?>