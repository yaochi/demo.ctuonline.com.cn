<?php
/*
 * Com.:
 * Author: qiaoyz
 * Date: 2012-8-1
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$station = DB :: table('sc_station');
$ustation = DB :: table('sc_ustation');
$relation = DB :: table('sc_relation');
$record = DB :: table('sc_record');

$sql = <<<EOF

DROP TABLE IF EXISTS $station;
DROP TABLE IF EXISTS $ustation;
DROP TABLE IF EXISTS $relation;
DROP TABLE IF EXISTS $record;

EOF;

runquery($sql);

$finish = TRUE;
?>
