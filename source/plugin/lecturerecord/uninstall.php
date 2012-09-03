<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$sql = <<<EOF
DROP TABLE IF EXISTS pre_lecture_record;
EOF;

runquery($sql);

$finish = TRUE;