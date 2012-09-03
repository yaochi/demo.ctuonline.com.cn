<?php
/* Function:
 * Com.:
 * Author: yangyang
 * Date: 2010-7-19
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_selection;
DROP TABLE IF EXISTS pre_selection_option;
DROP TABLE IF EXISTS pre_selection_record;
DROP TABLE IF EXISTS pre_selection_user_vote_num;

EOF;

runquery($sql);

$finish = TRUE;
?>
