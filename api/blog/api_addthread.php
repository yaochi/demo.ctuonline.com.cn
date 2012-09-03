<?php
/* Function: 发表专区话题接口
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */

require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_post.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_home.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uid=$_G['gp_uid'];
$fid=intval($_G['gp_fid']);
$title=$_G['gp_title'];
$content=$_G['gp_content'];
$attach=$_G['gp_attach'];
$fromwhere=$_G['gp_fromwhere'];

$count=DB::result_first("select count(*) from ".DB::table("forum_groupuser")." where fid=".$fid." and uid=".$uid);

if(!count){
	$res[success]="N";
}else{
	if(!$username){
		$userarr=getuserbyuid($uid);
		$username=$userarr[username];
	}
	if($attach){
		//文件保存目录路径
		$save_path =  dirname(dirname(dirname(__FILE__))).'/data/kindeditorattached';
		//文件保存目录URL
		$save_url = '/data/kindeditorattached';
		$save_path = realpath($save_path) . '/';
		$ext_arr = array(
			'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
		);
		
		if (empty($_FILES) === false) {
			//原文件名
			$file_name = $_FILES['file']['name'];
			//服务器上临时文件名
			$tmp_name = $_FILES['file']['tmp_name'];
			//文件大小
			$file_size = $_FILES['file']['size'];
			//检查文件名
			if (!$file_name) {
				$res[success]="N";
				$res[message]="请选择文件";
			}
			//检查目录
			if (@is_dir($save_path) === false) {
				$res[success]="N";
				$res[message]="上传目录不存在";
			}
			//检查目录写权限
			if (@is_writable($save_path) === false) {
				$res[success]="N";
				$res[message]="上传目录没有写权限";
			}
			//检查是否已上传
			if (@is_uploaded_file($tmp_name) === false) {
				$res[success]="N";
				$res[message]="临时文件可能不是上传文件";
			}
			//获得文件扩展名
			/*$temp_arr = explode(".", $file_name);
			$file_ext = array_pop($temp_arr);
			$file_ext = trim($file_ext);*/
			$file_ext = validatefile($tmp_name);
			//检查扩展名
			if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
				$res[success]="N";
				$res[message]="上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。";
			}
			//创建文件夹
			if ($dir_name !== '') {
				$save_path .= $dir_name . "/";
				$save_url .= $dir_name . "/";
				if (!file_exists($save_path)) {
					mkdir($save_path);
				}
			}
			if($uid){
				$save_path .= $uid. "/";
				$save_url .= $uid. "/";
				if (!file_exists($save_path)) {
					mkdir($save_path);
				}
			}
			$ymd = date("Ymd");
			$save_path .= $ymd . "/";
			$save_url .= $ymd . "/";
			if (!file_exists($save_path)) {
				mkdir($save_path);
			}
			//新文件名
			$new_file_name_prefix = date("YmdHis") . '_' . rand(10000, 99999) ;
			$new_file_name = $new_file_name_prefix  . '.' . $file_ext;
			
			//图片压缩
			if($file_ext=='jpg'){
				$img = @imagecreatefromjpeg($_FILES['file']['tmp_name']);
			}elseif($file_ext=='png'){
				$img = @imagecreatefrompng($_FILES['file']['tmp_name']);
			}elseif($file_ext=='gif'){
				$img = @imagecreatefromgif($_FILES['file']['tmp_name']);
			}else{
			}
		
			$maxwidth=500;
			$maxheight=120;
			$ratio=$maxheight/$maxwidth;
			
			$width = imageSX($img);
			$height = imageSY($img);
		
			if($height/$width>$ratio){
				$new_height_save = $maxheight;
				$new_width_save = intval($maxheight*$width/$height);
				$new_img_save=imagecreatetruecolor($new_width_save,$new_height_save);
			}else{
				$new_width_save=$maxwidth;
				$new_height_save=intval($maxwidth*$height/$width);
				$new_img_save=imagecreatetruecolor($new_width_save,$new_height_save);
			}
			 // 复制一份缩小后的图像
			imagecopyresampled($new_img_save,$img,0,0,0,0,$new_width_save,$new_height_save,$width,$height);
			
			$new_file_name_save=$new_file_name_prefix.'.thumb.'.$file_ext;
			$file_path_save = $save_path . $new_file_name_save;
		
			//保存图片 
			if($file_ext=='jpg'){
				imagejpeg($new_img_save,$file_path_save,100);
			}elseif($file_ext=='png'){
				imagepng($new_img_save ,$file_path_save);
			}elseif($file_ext=='gif'){
				imagegif($new_img_save ,$file_path_save);
			}else{
			}
		
			//手机端图片
			$maxwidth1=480;
			$maxheight1=800;
			$ratio1=$maxheight1/$maxwidth1;
			
			$width1 = imageSX($img);
			$height1 = imageSY($img);
		
			if($height1/$width1>$ratio1){
				$new_height_save1 = $maxheight1;
				$new_width_save1 = intval($maxheight1*$width1/$height1);
				$new_img_save1=imagecreatetruecolor($new_width_save1,$new_height_save1);
			}else{
				$new_width_save1=$maxwidth1;
				$new_height_save1=intval($maxwidth1*$height1/$width1);
				$new_img_save1=imagecreatetruecolor($new_width_save1,$new_height_save1);
			}
			 // 复制一份缩小后的图像
			imagecopyresampled($new_img_save1,$img,0,0,0,0,$new_width_save1,$new_height_save1,$width1,$height1);
			
			$new_file_name_save1=$new_file_name_prefix.'.'.$file_ext.".mobile.".$file_ext;
			$file_path_save1 = $save_path . $new_file_name_save1;
		
			//保存图片 
			if($file_ext=='jpg'){
				imagejpeg($new_img_save1,$file_path_save1,100);
			}elseif($file_ext=='png'){
				imagepng($new_img_save1 ,$file_path_save1);
			}elseif($file_ext=='gif'){
				imagegif($new_img_save1 ,$file_path_save1);
			}else{
			}	
			$save_url=$_G['config']['image']['url'].$save_url;
			//移动文件
			$file_path = $save_path . $new_file_name;
			if (move_uploaded_file($tmp_name, $file_path) === false) {
				$res[success]="N";
				$res[message]="上传文件失败";
			}
			@chmod($file_path, 0644);
			$file_url = $save_url . $new_file_name;
		}
		
	}
	if($file_url){
		$content=$content.'<br/><img src="'.$file_url.'" alt="" />';
		$feed['image_1']=$file_url;
		$feed['image_1_link']="forum.php?mod=viewthread&tid=".$tid;
	}
	
	DB::query("INSERT INTO ".DB::table('forum_thread')." (fid, author, authorid, subject, dateline, lastpost, lastposter,  isgroup) VALUES ('$fid', '$username', '$uid', '$title', '$_G[timestamp]', '$_G[timestamp]', '$username', '1')");
	$tid = DB::insert_id();
	$pid = insertpost(array(
		'fid' => $fid,
		'tid' => $tid,
		'first' => '1',
		'author' => $username,
		'authorid' => $uid,
		'subject' => $title,
		'dateline' => $_G['timestamp'],
		'message' => $content,
		'htmlon'=>'1',
		'bbcodeoff' => '0',
		'smileyoff' => '-1',
		'parseurloff' => 0,
		'attachment' => '0'
	));
	 post_forum_inc_threads($fid);
	 
	$feed['icon'] = 'thread';
	$feed['title_template'] = '{actor} 发表了新话题';
	$feed['body_template'] = '<b>{subject}</b><br />{message}';
	$feed['body_data'] = array(
		'subject' => "<a href=\"forum.php?mod=viewthread&tid=$tid\">$title</a>",
		'message' => messagecutstr($content, 150)
	);
	$feed['id'] = $tid;
	$feed['idtype'] = 'tid';
	$feed['fid']=$fid;
	$feed['uid']=$uid;
	$feed['username']=user_get_user_name($uid);
	$feed['dateline'] = $_G['timestamp'];
	$feed['title_data']['hash_data'] = "tid{$tid}";
	$feed['hash_data'] = empty($feed['title_data']['hash_data'])?'':$feed['title_data']['hash_data'];
	$feed['title_data'] = serialize(dstripslashes($feed['title_data']));
	$feed['body_data'] = serialize(dstripslashes($feed['body_data']));

	$feed = daddslashes($feed);
	DB::insert("home_feed", $feed);
	DB::query("update ".DB::TABLE("common_member_status")." set blogs=blogs+1 where uid=".$uid);
	 if($tid&&$pid){
	 	$res[success]="Y";
		$res[message]="成功！";
	 }else{
	 	$res[success]="N";
		$res[message]="失败！";
	 }
}
echo json_encode($res);
?>