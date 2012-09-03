
<?php
define('UC_API', strtolower(($_SERVER['HTTPS'] == 'on' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'))));


/**
 * 根据UID和size获取头像的位置
 * @param $uid
 * @param $size
 * @param $random
 * @param $type
 * @param $check
 */
function get_user_image($uid, $size, $random = '', $type = '',$check = ''){
	$avatar = 'uc_server/data/avatar/'.get_avatar($uid, $size, $type);
	
	if(file_exists(dirname(dirname(dirname(__FILE__))).'/'.$avatar)) {
		if($check) {
			echo 1;
			exit;
		}
		$random = !empty($random) ? rand(1000, 9999) : '';
		$avatar_url = empty($random) ? $avatar : $avatar.'?random='.$random;
	} else {
		if($check) {
			echo 0;
			exit;
		}
		$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
		$avatar_url = 'uc_server/images/noavatar_'.$size.'.gif';
	}

//	if(empty($random)) {
//		header("HTTP/1.1 301 Moved Permanently");
//		header("Last-Modified:".date('r'));
//		header("Expires: ".date('r', time() + 86400));
//	}
	
    return UC_API."/".$avatar_url;
}

function get_avatar($uid, $size = 'middle', $type = '') {
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$typeadd = $type == 'real' ? '_real' : '';
	return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
}

?>
