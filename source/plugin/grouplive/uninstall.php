<?php
/* Function:
 * Com.:
 * Author: wuhan
 * Date: 2010-7-23
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$table_live = DB :: table('group_live');

$sql = <<<EOF

DROP TABLE IF EXISTS $table_live;

EOF;

runquery($sql);

$finish = TRUE;
?>
