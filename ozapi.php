<?php
/* Function: 知识中心接口
 * Com.:
 * Author: wuhan
 * Date: 2010-8-10
 */
//TODO 混合SSO登录
define('APPTYPEID', 1);

require_once (dirname(__FILE__)).'/source/class/class_core.php';
require_once (dirname(__FILE__)).'/source/function/function_core.php';
require_once (dirname(__FILE__)).'/source/function/function_home.php';

$discuz = & discuz_core::instance();
$discuz->init();

$space = array();

$ac = getgpc('ac');
if(!in_array($ac, array('favorite', 'comment', 'share', 'click', 'score', 'redirect'))) {
	echo 'false';
	exit();
}

$type = empty($_GET['type'])?'':trim($_GET['type']);
if($type == 'file'){
	$idtype = 'docid';
	$_GET['idtype'] = $idtype;
}

require_once './api/oz/api_'.$ac.'.php';
?>