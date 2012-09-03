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
	$request.="Content-type: application/x-www-form-urlencoded; charset=utf-8\r\n";
	$request.="Content-length: ".strlen($post_data)."\r\n";
	$request.="Accept: */*\r\n";
	$request.="Connection: close\r\n";
	$request.="\r\n";
	$request.=$post_data."\r\n\r\n";
	
	echo $request;
	
	$fp = fsockopen($URL_Info["host"],$URL_Info["port"]);
	fwrite($fp, $request);
	while(!feof($fp)) {
		$result .= fgets($fp, 1024);
	}
	fclose($fp);

	return $result;
}

$uid = 1;
$nowtime = "";
$method = "add";
$v = 1;
$code = md5($uid.$method.$v.$nowtime);
$info = base64_encode("{\"title\":\"文档上传 \", \"body\":\"吴寒上传了文档 ESN模块分配\", \"id\":\"76504\"}");

//$info = base64_encode("{\"zoneid\":\"39\", \"op_setting\":\"doc_read\"}");

$re = HTTP_POST("http://localhost:80/discuzx2/api/oz/docfeed.php", "uid=$uid&nowtime=$nowtime&method=$method&v=$v&securecode=$code&info=$info");

//$re = HTTP_POST("http://localhost:80/discuzx2/api/oz/empirical.php", "uid=$uid&nowtime=$nowtime&method=$method&v=$v&securecode=$code&info=$info");

echo $re;


?>
