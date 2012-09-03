<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_notice;

DROP TABLE IF EXISTS pre_notice_type;

EOF;

runquery($sql);

$finish = TRUE;