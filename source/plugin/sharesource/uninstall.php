<?php
/* Function: 删除1个表
 * Com.:
 * Author: qiaoyz
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$table = DB :: table('sharesource');

$sql = <<<EOF

DROP TABLE IF EXISTS $table;

EOF;

runquery($sql);
$finish = TRUE;