<?php
/* Function: 
 * 1	group_doc 专区文档附加信息表。
 * Com.:
 * Author: wuhan
 * Date: 2010-8-4
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$table_album = DB :: table('group_doc');

$sql = <<<EOF

DROP TABLE IF EXISTS $table_album;
CREATE TABLE $table_album (
  `docid` mediumint(9) NOT NULL,
  `fid` smallint(6) NOT NULL default '0',
  `displayorder` tinyint(1) NOT NULL default '0',
  `highlight` tinyint(1) NOT NULL default '0',
  `digest` tinyint(1) NOT NULL default '0',
  `moderated` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`docid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

EOF;

runquery($sql);

$finish = TRUE;
?>
