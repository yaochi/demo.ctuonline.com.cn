<?php
/* Function: 
 * Com.:
 * Author: qiaoyz
 * Date: 2011-5-9
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$sql = <<<EOF

DROP TABLE IF EXISTS `pre_shresourcelist`;


EOF;

runquery($sql);

$finish = TRUE;
?>