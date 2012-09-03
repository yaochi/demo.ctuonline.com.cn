<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$suggestbox = DB :: table('suggestbox');

$sql = <<<EOF

DROP TABLE IF EXISTS $suggestbox;

EOF;

runquery($sql);

$finish = TRUE;