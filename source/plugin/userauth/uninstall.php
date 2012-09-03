<?php
/* Function:
 * Com.:
 * Author: yangyang
 * Date: 2012-7-11
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_authenticated_users;
EOF;

runquery($sql);

$finish = TRUE;
?>
