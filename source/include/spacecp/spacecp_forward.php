<?php

require_once libfile('function/feed');
require_once libfile('function/doc');
$feedid=$_G['gp_feedid'];
$diy=$_G['gp_diy'];
if(!$feedid){
	showmessage('sorry!');
}
$value=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where feedid=".$feedid);
	if($value['idtype']=='feed'){
		$prevalue=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where feedid=".$value['id']);
		$value['thisuid']=$value['uid'];
		$value['uid']=$prevalue['uid'];
		$value['thisusername']=user_get_user_name($value['thisuid']);
		if($prevalue[anonymity]==-1){
			$prevalue['username']='匿名';
		}elseif($prevalue[anonymity]){
			include_once libfile('function/repeats','plugin/repeats');
			$repeatsinfo=getforuminfo($prevalue[anonymity]);
			$prevalue['username']=$repeatsinfo[name];
		}else{
			$prevalue['username']=user_get_user_name($prevalue['uid']);
		}
		$value['username']=$prevalue['username'];
	}elseif($value['idtype']=='docid'&&$value['icon']=='doc'){
		$docarr=getFile($value[id]);
		$value['body_template'] = '<b>{subject}</b><br>{author}<br>{message}';
		$value['body_data'] = array(
			'subject' => "<a href=\"".$docarr[titlelink]."\">".$docarr[title]."</a>",
			'author' => "未知",
			'message' => cutstr($docarr['context'],150)
		);
		$value['body_data']=serialize(dstripslashes($value['body_data']));
		$value[image_1]=$docarr['imglink'];
		$value[image_1_link]=$docarr['titlelink'];
		$value[hash_data]='docid'.$value[id];
	}else{
		$value[username]=user_get_user_name($value['uid']);
	}
	

if(submitcheck("forwardsubmit")||'osm'=='forwardsubmit'){
	$anonymity=$_POST[anonymity];
	$atjson=$_POST['atjson'];
	if(!$anonymity){
		$anonymity=$_G[member][repeatsstatus];
	}
	if($anonymity>0){
		include_once libfile('function/repeats','plugin/repeats');
		$repeatsinfo=getforuminfo($anonymity);
		$fid=$repeatsinfo['fid'];
	}
	if($atjson){
		$atarr=parseat1($_G['gp_general'],$_G[uid],$atjson);
	}else{
		$atarr=parseat($_G['gp_general'],$_G[uid]);
	}
	$message=$atarr[message];
		if($atarr['atfids']){
				for($i=0;$i<count($atarr['atfids']);$i++){
					group_add_empirical_by_setting($_G['uid'],$atarr['atfids'][$i], 'at_group', $atarr['atfids'][$i]);
				}
				$sharetofids=",".implode(',',$atarr['atfids']).",";
			}
	
	if($value['idtype']=='feed'){
		$feedid1=$feedid;
		$feedid=$value['id'];
		$value['uid']=$value['olduid'];
		$value['username']=$value['oldusername'];
		$value['dateline']=$value['olddateline'];
	}
	if(!$message){
		if($value['idtype']=='blogid'){
			$message='转发记录';
		}elseif($value['idtype']=='albumid'){
			$message='转发图片';
		}elseif($value['idtype']=='docid'){
			$message='转发文档';
		}elseif($value['idtype']=='feed'){
			$message='转发内容';
		}elseif($value['idtype']=='flash'){
			$message='转发视频';
		}elseif($value['idtype']=='gliveid'){
			$message='转发直播';
		}elseif($value['idtype']=='gpicid'){
			$message='转发专区图片';
		}elseif($value['idtype']=='group'){
			$message='转发专区';
		}elseif($value['idtype']=='link'){
			$message='转发分享';
		}elseif($value['idtype']=='activityid'){
			$message='转发活动';
		}elseif($value['idtype']=='music'){
			$message='转发音乐';
		}elseif($value['idtype']=='noticeid'){
			$message='转发通知公告';
		}elseif($value['idtype']=='nektid'){
			$message='转发你我课堂';
		}elseif($value['idtype']=='noticeid'){
			$message='转发通知公告';
		}elseif($value['idtype']=='pic'){
			$message='转发图片';
		}elseif($value['idtype']=='picid'){
			$message='转发图片';
		}elseif($value['idtype']=='questid'){
			$message='转发问卷';
		}elseif($value['idtype']=='question'){
			$message='转发提问';
		}elseif($value['idtype']=='resourceid'){
			$message='转发资源';
		}elseif($value['idtype']=='selectionid'){
			$message='转发评选';
		}elseif($value['idtype']=='thread'){
			$message='转发话题';
		}elseif($value['idtype']=='video'){
			$message='转发视频';
		}elseif($value['idtype']=='poll'){
			$message='转发投票';
		}else{
			$message='转发内容';
		}
	}
	
	$feedarr = array(
			'appid' => '',
			'icon' => $value['icon'],
			'uid' => $_G['uid'],
			'username' => $space['username'],
			'dateline' => $_G['timestamp'],
			'hash_data'=>$value['hash_data'],
			'title_template' => $value['title_template'],
			'title_data' => $value['title_data'],
			'body_template' => $value['body_template'],
			'body_data' => $value['body_data'],
			'body_general'=>$message,
			'image_1' => $value['image_1'],
			'image_1_link' => $value['image_1_link'],
			'image_2' => $value['image_2'],
			'image_2_link' => $value['image_2_link'],
			'image_3' => $value['image_3'],
			'image_3_link' => $value['image_3_link'],
			'image_4' =>$value['image_4'] ,
			'image_4_link' =>$value['image_4_link'],
			'image_5' =>$value['image_5'] ,
			'image_5_link' =>$value['image_5_link'],
			'target_ids'=>$value['target_ids'],
			'id' => $feedid,
			'idtype' => 'feed',
			'olduid'=>$value['uid'],
			'oldusername'=>$value['username'],
			'olddateline'=>$value['dateline'],
			'fid'=>$fid,
			'sharetofids'=>$sharetofids,
			'anonymity'=>$anonymity
		);
		$feedarr=daddslashes($feedarr);
	DB::insert('home_feed', $feedarr);
	$newfeedid=DB::insert_id();
	if($anonymity=='0'){
		DB::query("update ".DB::TABLE("common_member_status")." set blogs=blogs+1 where uid=".$_G[uid]);
	}
	parsetag($title,$feedarr['body_general'],'feed',$newfeedid);
	if($anonymity!=-1&&$feedarr[olduid]){
	notification_add($feedarr[olduid],"zq_at",'<a href="home.php?mod=space&uid='.$_G[uid].'" target="_block">“'.$_G[myself][common_member_profile][$_G[uid]][realname].'”</a> 刚刚<a href="home.php?view=atme">转发了你的内容</a>', array(), 0);
	atrecord(array($feedarr[olduid]),$newfeedid);
	}
	if($atarr[atuids]){
		foreach(array_keys($atarr[atuids]) as $uidkey){
			notification_add($atarr[atuids][$uidkey],"zq_at",'<a href="home.php?mod=space&uid='.$_G[uid].'" target="_block">“'.$_G[myself][common_member_profile][$_G[uid]][realname].'”</a> 在其<a href="home.php?view=atme">转发的内容</a>中提到了您，赶快去看看吧', array(), 0);
			atrecord(array($atarr[atuids][$uidkey]),$newfeedid);
		}
	}
	
	DB::query("update ".DB::TABLE("home_feed")." set sharetimes=sharetimes+1 where feedid=".$feedid );
	if($feedid1){
		DB::query("update ".DB::TABLE("home_feed")." set sharetimes=sharetimes+1 where feedid=".$feedid1 );
	}
	
	if($value['idtype']=='feed'){
		if($_G['gp_thiscomment']){
			DB::insert('home_comment', array('uid'=>$value['thisuid'],
				'id'=>$feedid1,
				'idtype'=>'feed',
				'authorid' =>$_G['uid'],
				'author' => $_G['username'],
				'dateline'=>time(),
				'message'=>$message,
				'anonymity'=>$anonymity
			));
			$thiscid=DB::insert_id();
			DB::query("update ".DB::TABLE("home_feed")." set commenttimes=commenttimes+1 where feedid=".$feedid1);
		}
		if($_G['gp_precomment']){
			if($prevalue[icon]=='thread' || $prevalue[icon]=='poll' || $prevalue[icon]=='reward'){
				$precid = insertpost(array(
					'fid' => $prevalue[fid],
					'tid' => $prevalue[id],
					'first' => '0',
					'author' => $_G['username'],
					'authorid' => $_G['uid'],
					'dateline' => $_G['timestamp'],
					'message' => $message,
					'useip' => $_G['clientip'],
					'invisible' => 0,
					'anonymous' => 0,
					'usesig' => 0,
					'htmlon' =>1,
					'bbcodeoff' => -1,
					'smileyoff' => -1,
					'parseurloff' =>0,
					'attachment' => '0',
					'feedid'=>$feedid,
					'anonymity'=>$anonymity,
				));
			}else{
				DB::insert('home_comment', array('uid'=>$value['uid'],
					'id'=>$feedid,
					'idtype'=>'feed',
					'authorid' =>$_G['uid'],
					'author' => $_G['username'],
					'dateline'=>time(),
					'message'=>$message,
					'anonymity'=>$anonymity
				));
				$precid=DB::insert_id();
			}
			DB::query("update ".DB::TABLE("home_feed")." set commenttimes=commenttimes+1 where feedid=".$feedid);
		}
		showmessage('do_success', dreferer(), array('curcid' => $thiscid,'curfeedid' => $feedid1,'precid'=>$precid,'prefeedid'=>$feedid,'message'=>$message,'anonymity'=>$anonymity));
	}else{
		if($_G['gp_thiscomment']){
			if($value[icon]=='thread' || $value[icon]=='poll' || $value[icon]=='reward'){
				$thiscid = insertpost(array(
					'fid' => $value[fid],
					'tid' => $value[id],
					'first' => '0',
					'author' => $_G['username'],
					'authorid' => $_G['uid'],
					'dateline' => $_G['timestamp'],
					'message' => $message,
					'useip' => $_G['clientip'],
					'invisible' => 0,
					'anonymous' => 0,
					'usesig' => 0,
					'htmlon' =>1,
					'bbcodeoff' => -1,
					'smileyoff' => -1,
					'parseurloff' =>0,
					'attachment' => '0',
					'feedid'=>$feedid,
					'anonymity'=>$anonymity,
				));
			}else{
				DB::insert('home_comment', array('uid'=>$value['uid'],
					'id'=>$feedid,
					'idtype'=>'feed',
					'authorid' =>$_G['uid'],
					'author' => $_G['username'],
					'dateline'=>time(),
					'message'=>$message,
					'anonymity'=>$anonymity
				));
				$thiscid=DB::insert_id();
			}
			DB::query("update ".DB::TABLE("home_feed")." set commenttimes=commenttimes+1 where feedid=".$feedid);
		}
		showmessage('do_success', dreferer(), array('curcid' => $thiscid,'curfeedid' => $feedid,'message'=>$message,'anonymity'=>$anonymity));
	}

	//showmessage('do_success', dreferer(), array('thiscid' => $thiscid,'thisfeedid' => $feedid1,'precid'=>$precid,'prefeedid'=>$feedid,'message'=>$message,'anonymity'=>$anonymity));
	
}else{
	if($value[anonymity]&&$value[idtype]!='feed'){
		if($value[anonymity]==-1){
			$value['username']='匿名';
		}else{
			include_once libfile('function/repeats','plugin/repeats');
			$repeatsinfo=getforuminfo($value[anonymity]);
			$value['username']=$repeatsinfo[name];
		}
	}elseif($value[anonymity]&&$value[idtype]=='feed'){
		if($value[anonymity]==-1){
			$value['thisusername']='匿名';
		}else{
			include_once libfile('function/repeats','plugin/repeats');
			$repeatsinfo=getforuminfo($value[anonymity]);
			$value['thisusername']=$repeatsinfo[name];
		}
	}else{
		if($prevalue[anonymity]=='0'){
			$value['username']=user_get_user_name($value['uid']);
		}
	}

	$value[body_general]=getstr($value[body_general],0,1,1,0,0,-1);
	$value=mkfeed($value);
	include template('home/spacecp_forward');
}

?>