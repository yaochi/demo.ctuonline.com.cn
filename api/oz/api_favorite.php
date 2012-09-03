<?php
/* Function:
 * Com.:
 * Author: wuhan
 * Date: 2010-8-10
 */
if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}

include_once libfile("function/doc");
include_once libfile("function/spacecp");

$id = empty ($_GET['id']) ? 0 : intval($_GET['id']);
//$_GET['type'] = 'doc';
$_GET['type']=$_GET['type']?$_GET['type']:'doc';

require_once dirname(dirname(dirname(__FILE__))).'/source/include/spacecp/spacecp_favorite.php';
?>
