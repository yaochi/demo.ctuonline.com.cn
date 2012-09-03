<?php
/* Function:
 * Com.:
 * Author: wuhan
 * Date: 2010-8-4
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$table_album = DB :: table('group_doc');

$sql = <<<EOF

DROP TABLE IF EXISTS $table_album;

EOF;

runquery($sql);

$finish = TRUE;
?>
