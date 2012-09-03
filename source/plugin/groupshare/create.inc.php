<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");
function index(){
	global  $_G;
	require_once libfile('function/group');
	$anonymity=$_G[member][repeatsstatus];
	$type = empty($_GET['type'])?'':$_GET['type'];
	$confirm=empty($_POST['confirm'])?'no':$_POST['confirm'];
	if(submitcheck('sharesubmit')) {
		if($type == 'link'  && "sure" == $confirm) {
			$link = dhtmlspecialchars(trim($_POST['link']));
			if($link) {
				if(!preg_match("/^(http|ftp|https|mms)\:\/\/.{4,300}$/i", $link)) $link = '';
			}
			if(empty($link)) {
				showmessage('url_incorrect_format');
			}
			$arr['title_template'] = lang('spacecp', 'share_link');
			$arr['body_template'] = '{link}';

			$link_text = sub_url($link, 45);

			$arr['body_data'] = array('link'=>"<a href=\"$link\" target=\"_blank\">$link_text</a>", 'data'=>$link);
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
				$arr['title_template'] = lang('spacecp', 'share_video');
				$type = 'video';
				$arr['body_data']['flashvar'] = $flashvar['flv'];
				$arr['body_data']['host'] = 'flash';
				$arr['body_data']['imgurl'] = $flashvar['imgurl'];
			}
			//mp3分享
			if(preg_match("/\.(mp3|wma)$/i", $link)) {
				$arr['title_template'] = lang('spacecp', 'share_music');
				$arr['body_data']['musicvar'] = $link;
				$type = 'music';
			}
			//flash分享
			if(preg_match("/\.swf$/i", $link)) {
				$arr['title_template'] = lang('spacecp', 'share_flash');
				$arr['body_data']['flashaddr'] = $link;
				$type = 'flash';
			}
		}

		//分享网址、视频、音频、flash
		if(("link"==$type || "video"==$type || "music"==$type || "flash"==$type) &&  "sure" == $confirm){
			
			$arr['type'] = $type;
			$arr['uid'] = $_G['uid'];
			$arr['username'] = user_get_user_name($_G['uid']);
			$arr['dateline'] = $_G['timestamp'];
			$arr['status'] = 1;
			$arr['fid'] = $_G[fid];
			$arr['body_general'] = getstr($_POST['general'], 150, 1, 1, 1, 1);
			//保存分享基本信息
			$tempArry=$arr['body_data'];
			$arr['body_data'] = serialize($arr['body_data']);
			$arr['anonymity']=$anonymity;
			$setarr = daddslashes($arr);
			$sid = DB::insert('group_share', $setarr, 1);
			
		}
		showmessage('创建成功',"forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=groupshare&plugin_op=groupmenu");
	}
}


?>
