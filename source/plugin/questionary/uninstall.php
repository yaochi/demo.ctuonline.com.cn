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

DROP TABLE IF EXISTS pre_questionary;
DROP TABLE IF EXISTS pre_questionary_question;
DROP TABLE IF EXISTS pre_questionary_questionoption;
DROP TABLE IF EXISTS pre_questionary_questionchoicers;

EOF;

runquery($sql);

$finish = TRUE;
?>
