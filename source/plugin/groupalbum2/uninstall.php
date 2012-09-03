<?php
/* Function:
 * Com.:
 * Author: wuhan
 * Date: 2010-7-12
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$table_album = DB :: table('group_album');
$table_pic = DB :: table('group_pic');
$table_picfield = DB :: table('group_picfield');

$sql = <<<EOF

DROP TABLE IF EXISTS $table_album;
DROP TABLE IF EXISTS $table_pic;
DROP TABLE IF EXISTS $table_picfield;

EOF;

runquery($sql);

$finish = TRUE;
?>
