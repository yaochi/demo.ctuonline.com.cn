<?php
/* Function:
 * Com.:
 * Author: yangyang
 * Date: 2012-1-10 
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_forum_repeats;
DROP TABLE IF EXISTS pre_repeats_relation;



EOF;

runquery($sql);

$finish = TRUE;
?>
