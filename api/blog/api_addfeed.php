<?php
/* Function: 添加动态接口
 * Com.:
 * Author: yy
 * Date: 2012-6-21 
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_feed.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_home.php';
$discuz = & discuz_core::instance();
	
$discuz->init();
$uid=$_G["gp_uid"];
$type=$_G["gp_type"];
$source=$_G["gp_source"];
$message=$_G["gp_message"];
$fid=$_G["gp_fid"];
$title=$_G["gp_title"];
$images=$_G["gp_images"];
$imageslink=$_G["gp_imageslink"];
$anonymity=$_G["gp_anonymous"];
$mediaurl=$_G["gp_multimedia"];

if($uid && $source && $message && $type){
	$imagearr=array();
	$imagelinkarr=array();
	$author=user_get_user_name($uid);
	if($anonymity){
		$body_data['author']="<a href=\"home.php?mod=space&uid=-1\">匿名</a>";
	}else{
		$body_data['author']="<a href=\"home.php?mod=space&uid=$uid\">$author</a>";
	}
	if($title){
		$body_data['title'] = $title;
	}
	$body_data['message'] = getstr($message, 0, 1, 1, 0, 0, -1);
	if($images && $imageslink){
		$imagearr=explode(',',$images);
		$imagelinkarr=explode(',',$imageslink);
	}
	if($mediaurl){
		$link = dhtmlspecialchars(trim($mediaurl));
			if($link) {
				if(!preg_match("/^(http|ftp|https|mms)\:\/\/.{4,300}$/i", $link)) $link = '';
			}
			if(empty($link)) {
			}else{
				$parseLink = parse_url($link);
				require_once libfile('function/discuzcode');
				$flashvar = parseflv($link);
				if(empty($flashvar) && preg_match("/\.flv$/i", $link)) {
					$flashvar = array(
						'flv' => IMGDIR.'/flvplayer.swf?&autostart=true&file='.urlencode($link),
						'imgurl' => ''
					);
				}
				//视频分享
				if(!empty($flashvar)) {
					$body_data['flashvar'] = $flashvar['flv'];
					$body_data['host'] = 'flash';
					$body_data['imgurl'] = $flashvar['imgurl'];
				}
				//mp3分享
				if(preg_match("/\.(mp3|wma)$/i", $link)) {
					$body_data['musicvar'] = $link;
				}
				//flash分享
				if(preg_match("/\.swf$/i", $link)) {
					$body_data['flashaddr'] = $link;
				}
				
			}
	}

	$return=feed_add($type,'',array(),'',$body_data,'',$imagearr,$imagelinkarr,'','',1,0,0,$type,$uid,$author,$fid,array(),'',0,0,$anonymity,$source);
	if($return){
		$res["code"]='0';
		$res["errorcode"]="11000";
	}
}else{
	$res["code"]='1';
	$res["errorcode"]="91000";
}

echo json_encode($res);
?>