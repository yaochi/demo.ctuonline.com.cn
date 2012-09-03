<?php
/* 
 * Add by lujianqing 20101013
 * and open the template in the editor.
 */

define('APPTYPEID', 9);
define('CURSCRIPT', 'help');

require_once './source/class/class_core.php';
require_once './source/function/function_home.php';

$discuz = & discuz_core::instance();
$discuz->init();

if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}
$choose = $_GET['id'];
if(!$choose){
	$choose = 'wdjs';
}
include template('platfrommanage/platfrommanage');
?>