<?php
/* Function: 社区中动态接口
 * Com.:
 * Author: yangyang
 * Date: 2011-3-22 17:09:34
 */
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/function/function_feed.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/function/function_home.php';
$discuz = & discuz_core::instance();
$discuz->init();
	$query=DB::query("SELECT * FROM ".DB::Table("home_feed")." where icon='thread' or icon='activitys' or icon='poll' or icon='questionary' or icon='doing' or icon='blog' or icon='doc' and idtype!='feed' order by dateline desc limit 0,20");
	while($value=DB::fetch($query)){
		$feed=array();
		$value[username]=user_get_user_name($value[uid]);
		$value=mkfeed($value);
		$feed[uid]=$value[uid];
		$feed[username]=$value[username];
		$feed[dateline]=$value[dateline];
		$feed[type]=$value[icon];
		$feed[fid]=$value[fid];
		$feed[feedid]=$value[feedid];
		if($value[icon]!='doing'&&$value[icon]!='doc'&&$value[icon]!='questionary'){
			$title_template=explode('</a>',$value[title_template]);
			$feed[action]=trim($title_template[1]);
			$feed[title]=strip_tags($value[body_data][subject]);
			$allurl=substr($value[body_data][subject],9,strcspn($value[body_data][subject],'>')-10);
			if(substr($allurl,0,4)!=='http'){
				$siteurl=substr($_G[siteurl],0,strripos($_G[siteurl],'api'));
				$url=$siteurl.$allurl;
			}else{
				$url=$allurl;
			}
			$feed[url]=$url;
			if($value[icon]!='poll'&&$value[icon]!='blog'){
				$feed[message]=$value[body_data][message];
			}elseif($value[icon]=='blog'){
				$feed[message]=getstr($value[body_data][summary],0,1,1,0,0,-1);
			}
		}elseif($value[icon]=='doc'){
			//print_r($value);
			$feed[action]='上传了新文档';
			$title_template=explode('上传了[',$value[body_data][body]);
			$feed[url]=substr($title_template[1],9,strcspn($title_template[1],'>')-10);
			$feed[title]=substr(strip_tags($title_template[1]),0,strcspn(strip_tags($title_template[1]),']'));
		}elseif($value[icon]=='questionary'){
			$siteurl=substr($_G[siteurl],0,strripos($_G[siteurl],'api'));
			$feed[action]='发表了新问卷';
			$feed[title]=strip_tags($value[title_data][questname]);
			$feed[url]=$siteurl.substr($value[title_data][questname],9,strcspn($value[title_data][questname],'>')-10);
		}else{
			$feed[title]=$value[title_data][message];
		}
		$feedlist[]=$feed;
	}
	echo json_encode($feedlist);


?>