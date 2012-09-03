<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_lecturer;

DROP TABLE IF EXISTS pre_forum_forum_lecturer;

EOF;

runquery($sql);

$finish = TRUE;