<?php
/* Function:
 * Com.:
 * Author: yangyang
 * Date:2011-3-22 
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS pre_synchro_cert_info;


EOF;

runquery($sql);

$finish = TRUE;
?>
