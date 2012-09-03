<?php
/* Function:
 * Com.:
 * Author: SK
 * Date: 2010-9-13
 */
$method = strtolower($_SERVER["REQUEST_METHOD"]);

if($method=="post"){
	require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
	$discuz = & discuz_core::instance();
	$discuz->init();
	require libfile("function/log");
	$username = $_POST["username"];
	$fid = $_POST["fid"];
	$method = $_POST["method"];
	if (!$method) {
		$method = 'usergroup';
	}
	
	if(empty($username)){
		$result = array('success' => false, 'groupname' => '', 'message' => base64_encode('用户不正确'));
		echo json_encode($result);
		common_log_create("usergroup-api", serialize($result));
		exit;
	}elseif(empty($fid)){
		$result = array('success' => false, 'groupname' => '', 'message' => base64_encode('专区不正确'));
		echo json_encode($result);
		common_log_create("usergroup-api", serialize($result));
		exit;
	}else{
		//执行数据库操作
		require_once libfile('function/group');
		$fname = get_groupname_by_fid($fid);
		switch($method){
			case 'usergroup':
				if (check_user_group($username, $fid)) {
					$result = array('success' => true, 'groupname' => base64_encode($fname), 'message' => base64_encode('该用户属于此专区'));
				} else {
					$result = array('success' => false, 'groupname' => base64_encode($fname), 'message' => base64_encode('该用户不属于此专区'));
				}
				break;
			default:
				$result = array('success' => false, 'groupname' => base64_encode($fname), 'message' => base64_encode('未指定的操作!'));
		}
		echo json_encode($result);
		common_log_create("usergroup-api", serialize($result));
	}
}
?>
