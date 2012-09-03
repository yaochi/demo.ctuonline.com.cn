<?php
/*
 * Com.:
 * Author: yangyang
 * Date: 2011-7-21
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_extra_class;
DROP TABLE IF EXISTS pre_extra_org;
DROP TABLE IF EXISTS pre_extra_lecture;
DROP TABLE IF EXISTS pre_extra_relationship;
DROP TABLE IF EXISTS pre_extrastar;

EOF;

runquery($sql);

$finish = TRUE;
?>
