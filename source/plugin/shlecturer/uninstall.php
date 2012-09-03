<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_shlecture;

DROP TABLE IF EXISTS pre_shlecture_direct;
DROP TABLE IF EXISTS pre_shlecture_stars;

EOF;

runquery($sql);

$finish = TRUE;