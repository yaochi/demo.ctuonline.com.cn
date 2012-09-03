<?php
/* Function: 创建了3个表
 * 1    exam 有奖问答表
 * 2    exam_question 试题表
 * 3	exam_answer 学员答题表
 * Com.:
 * Author: qiaoyz
 * Date: 2012-2-28
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$exam = DB :: table('exam');
$exam_question = DB :: table('exam_question');
$exam_answer = DB :: table('exam_answer');

$sql = <<<EOF

DROP TABLE IF EXISTS $exam;
CREATE TABLE $exam (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `num` int(11) NOT NULL DEFAULT '0',
  `addnum` int(11) NOT NULL DEFAULT '0',
  `rightnum` int(11) NOT NULL DEFAULT '0',
  `status` int(10) NOT NULL DEFAULT '0',
  `starttime` int(10) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `creator` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS $exam_question;
CREATE TABLE $exam_question (
  `eid` int(11) NOT NULL DEFAULT '0',
  `tid` int(11) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL DEFAULT '0',
  `title` varchar(500) NOT NULL DEFAULT '',
  `option` varchar(1000) NOT NULL DEFAULT '',
  `answer` varchar(20) NOT NULL DEFAULT '',
  `rightnum` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eid`,`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS $exam_answer;
CREATE TABLE $exam_answer (
  `eid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `realname` varchar(255) NOT NULL DEFAULT '',
  `answers` varchar(255) NOT NULL DEFAULT '',
  `rightnum` int(11) NOT NULL DEFAULT '0',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `dateline` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`eid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

EOF;
runquery($sql);
$finish = TRUE;
?>