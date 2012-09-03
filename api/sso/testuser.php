<?php
/* Function:
 * Com.:
 * Author: wuhan
 * Date: 2010-8-6
 */
function HTTP_POST($url,$post_data){
	$URL_Info=parse_url($url);

	$request.="POST ".$URL_Info["path"]." HTTP/1.1\r\n";
	$request.="Host: ".$URL_Info["host"]."\r\n";
	$request.="Content-type: application/x-www-form-urlencoded\r\n";
	$request.="Content-length: ".strlen($post_data)."\r\n";
	$request.="Accept: */*\r\n";
	$request.="Connection: close\r\n";
	$request.="\r\n";
	$request.=$post_data."\r\n\r\n";
	$fp = fsockopen($URL_Info["host"],$URL_Info["port"]);
	fwrite($fp, $request);
	while(!feof($fp)) {
		$result .= fgets($fp, 1024);
	}
	fclose($fp);

	return $result;
}

require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();

$uid = $_G['gp_uid'];

$nowtime = "";
$method = "create";
$v = 1;
$code = md5($uid.$method.$v.$nowtime);

$userinfo = array();
$userinfo['username'] = $_G['gp_username'];
$userinfo['password'] = $_G['gp_password'];
$userinfo['realname'] = $_G['gp_realname'];
$userinfo['email'] = $_G['gp_email'];

//$userinfo = base64_encode("{\"username\":\"$username\", \"realname\":\"$realname\", \"email\":\"$email\", \"password\":\"$password\"}");
//$re = HTTP_POST("http://localhost:80/discuzx2/api/sso/user.php", "uid=$uid&nowtime=$nowtime&method=$method&v=$v&securecode=$code&userinfo=$userinfo");

require './user.php';

$user = new User();

$re = $user->create($uid, $userinfo);

echo json_encode($re);
?>
