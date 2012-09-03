<?php
/* Function: 判断用户是否是好友
 * Com.:
 * Author: yangyang
 * Date: 2010-10-08
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();
//$method=strtolower($_SERVER["REQUEST_METHOD"]);

$uid = empty ($_GET['uid']) ? 0 : intval($_GET['uid']);
$fuid = empty ($_GET['fuid']) ? 0 : intval($_GET['fuid']);
$isfriend=false;
if($uid!='0' && $fuid!='0'){
	$query = DB :: query("SELECT count(1) as c FROM " . DB :: table('home_friend') . " WHERE uid='$uid' and fuid='$fuid'");
	$count = DB::fetch($query);
    $count = $count["c"];
	if($count){
		$isfriend=true;
	}elseif($uid==$fuid){
		$isfriend=true;
	}
}
echo json_encode($isfriend);

?>
