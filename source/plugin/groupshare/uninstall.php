<?php
/* Function:
 * Com.:
 * Author: yangyang
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_group_share;

EOF;

runquery($sql);

$finish = TRUE;
?>
