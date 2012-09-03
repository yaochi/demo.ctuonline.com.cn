<?php
/* Function:@提示
 * Com.:
 * Author: yangyang
 * Date: 2011-10-8 
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/api/sphinx/sphinxapi.php';
$discuz = & discuz_core::instance();

$discuz->init();
$name=$_G['gp_name'];
$uid=$_G['gp_uid'];
$num=empty($_G['gp_num'])?0:$_G['gp_num'];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];

if($name){
	$flag=1;
	if($_G[config]['sphinx']['used']){
		$followresult=sphinxfollowdata($name,$uid,$num,$shownum);
		if($followresult[matches]){
			for($i=0; $i<count($followresult[matches]); $i++){
				$fuidarr[]=$followresult[matches][$i]['id'];
			}
		}
	}else{
	//查找follow用户是否超过shownum
		$followquery=DB::query("SELECT main.fuid AS uid,realname,note,userprovince FROM ".DB::table('home_friend')." main INNER JOIN ".DB::table('common_member_profile')." profile ON profile.uid=main.fuid WHERE main.uid='".$uid."' and (type='1' or type='3') AND (profile.realname LIKE '%".$name."%' or profile.spell LIKE '%".$name."%' or main.note LIKE '%".$name."%' ) ORDER BY main.num DESC, main.dateline DESC limit $num,$shownum");
	}
	$followusers=array();
	while($uservalue=DB::fetch($followquery)){
		$user[type]='user';
		$user[uid]=$uservalue[uid];
		$user[icon]=useravatar($uservalue[uid]);
		$user[name]=$uservalue[realname];
		$user[note]=$uservalue[note];
		$user[userprovince]=$uservalue[userprovince];
		$user[followed]='1';
		$followusers[$uservalue[uid]]=$user;
	}
	$follownum=count($followusers);
	if($follownum==$shownum){
		$users=$followusers;
		$flag=0;
	}
	//查找加入分组是否超过shownum
	$forumflag=1;
	$joinquery=DB::query("SELECT ff.fid,ff.name,fff.description,fff.icon,fff.membernum from ".DB::TABLE('forum_forum')." ff,".DB::table('forum_forumfield')." fff,".DB::TABLE("forum_groupuser")." fg where ff.fid=fff.fid and ff.fid=fg.fid and fg.uid=$uid and  ff.type='sub' and (ff.name like '%".$name."%' or ff.spell like '%".$name."%')  order by ff.fid desc limit $num,$shownum ");
	$joinforums=array();
	while($joinvalue=DB::fetch($joinquery)){
		$joinvalue[type]='group';
		$joinvalue[joined]='1';
		if(!$joinvalue[icon]){
			$joinvalue[icon]='static/image/images/def_group.png';
		}else{
			$joinvalue[icon]='data/attachment/group/'.$joinvalue[icon];
		}
		$joinforums[$joinvalue[fid]]=$joinvalue;
	}
	$joinnum=count($joinforums);
	if($joinnum==$shownum){
		$forums=$joinforums;
		$forumflag=0;
	}

	if($_G[config]['sphinx']['used'] && $_G[config]['memory']['redis']['on']){
		if($flag){
			$userresult=sphinxuserdata($name,$num,$shownum);
			if($userresult[matches]){
				$users=redisuserdata($userresult[matches]);
			}
		}
		if($forumflag){
			$forumresult=sphinxforumdata($name,$num,$shownum);
			if($forumresult[matches]){
				$forums=redisforumdata($forumresult[matches]);
			}
		}
	}elseif($_G[config]['sphinx']['used']){
		if($flag){
			$userresult=sphinxuserdata($name,$num,$shownum);
			if($userresult[matches]){
				for($i=0; $i<count($userresult[matches]); $i++){
					$uidarr[]=$userresult[matches][$i]['id'];
				}
				$query=DB::query("SELECT cm.uid,username,realname,userprovince FROM ".DB::table("common_member")." cm,".DB::table('common_member_profile')." profile  WHERE cm.uid=profile.uid and cm.uid in (".implode(',',$uidarr).") ORDER BY cm.uid asc limit $num,$shownum");
				while($uservalue=DB::fetch($query)){
					$uservalue[name]=$uservalue[realname];
					$uservalue[icon]=useravatar($uservalue[uid]);
					$uservalue[type]="user";
					$uservalue[userprovince]=$uservalue[userprovince];
					$uservalue[followed]='0';
					$users[$uservalue[uid]]=$uservalue;
				}
			}
		}
		if($forumflag){
			$forumresult=sphinxforumdata($name,$num,$shownum);
			if($forumresult[matches]){
				for($i=0; $i<count($forumresult[matches]); $i++){
					$fidarr[]=$forumresult[matches][$i]['id'];
				}
				$query=DB::query("select ff.fid,ff.name,fff.description,fff.icon,fff.membernum from ".DB::TABLE('forum_forum')." ff,".DB::table('forum_forumfield')." fff where ff.fid in (".implode(',',$fidarr).") and ff.type='sub' and ff.fid=fff.fid order by ff.fid asc limit $num,$shownum");
				while($forumvalue=DB::fetch($query)){
					$forumvalue[type]='group';
					if(!$forumvalue[icon]){
						$forumvalue[icon]='static/image/images/def_group.png';
					}else{
						$forumvalue[icon]='data/attachment/group/'.$forumvalue[icon];
					}
					$forumvalue[joined]='0';
					$forums[$forumvalue[fid]]=$forumvalue;
				}
			}
		}
	}else{
		if($flag){
			$membercount=DB::result_first("SELECT count(*) FROM ".DB::table(common_member)." cm,".DB::table('common_member_profile')." profile  WHERE cm.uid=profile.uid and ( profile.realname LIKE '%".$name."%' or profile.spell LIKE '%".$name."%' )");
			if($membercount){
				$query=DB::query("SELECT cm.uid,username,realname,userprovince  FROM ".DB::table(common_member)." cm,".DB::table('common_member_profile')." profile  WHERE cm.uid=profile.uid and ( profile.realname LIKE '%".$name."%' or profile.spell LIKE '%".$name."%' ) ORDER BY cm.uid asc limit $num,$shownum");
				while($uservalue=DB::fetch($query)){
					$uservalue[name]=$uservalue[realname];
					$uservalue[icon]=useravatar($uservalue[uid]);
					$uservalue[type]="user";
					$uservalue[userprovince]=$uservalue[userprovince];
					$uservalue[followed]='0';
					$users[$uservalue[uid]]=$uservalue;
				}
			}
		}
		if($forumflag){
			$forumcount=DB::result_first("select count(*) from ".DB::TABLE('forum_forum')." ff,".DB::table('forum_forumfield')." fff where (ff.name like '%".$name."%' or  ff.spell like '%".$name."%') and ff.type='sub' and ff.fid=fff.fid");
			if($forumcount){
				$query=DB::query("select ff.fid,ff.name,fff.description,fff.icon,fff.membernum from ".DB::TABLE('forum_forum')." ff,".DB::table('forum_forumfield')." fff where (ff.name like '%".$name."%'  or ff.spell like '%".$name."%' ) and ff.type='sub' and ff.fid=fff.fid and fff.jointype=0 order by ff.fid asc limit $num,$shownum");
				while($forumvalue=DB::fetch($query)){
					$forumvalue[type]='group';
					if(!$forumvalue[icon]){
						$forumvalue[icon]='static/image/images/def_group.png';
					}else{
						$forumvalue[icon]='data/attachment/group/'.$forumvalue[icon];
					}
					$forumvalue[joined]='0';
					$forums[$forumvalue[fid]]=$forumvalue;
				}
			}
		}
	}
	if($flag){
		$finalarray=array_merge($followusers,$users);
		$keysarray=array_unique(array_merge(array_keys($followusers),array_keys($users)));
		$users=array();
		foreach($keysarray as $key=>$value){
			$users[$value]=$finalarray[$key];
		}	
	}
	$provicearray=json_decode(getprovincebyuids(implode(',',array_keys($users))),true);
	foreach($users as $key=>$value){
		$users[$key][userprovince]=empty($provicearray[$key][groupName])?"中国电信":$provicearray[$key][groupName];
	}
	if($forumflag){
		$forumfinalarray=array_merge($joinforums,$forums);
		$forumkeysarray=array_unique(array_merge(array_keys($joinforums),array_keys($forums)));
		$forums=array();
		foreach($forumkeysarray as $key=>$value){
			$forums[$value]=$forumfinalarray[$key];
		}	
	}
	if(count($users)>$shownum/2 && count($forums)>$shownum/2){
		$users=array_splice($users,0,$shownum/2);
		$forums=array_splice($forums,0,$shownum/2);
	}elseif(count($users)>$shownum/2 && count($forums)<$shownum/2){
		$users=array_splice($users,0,$shownum-count($forums));
		$forums=array_splice($forums,0,count($forums));
	}elseif(count($users)<$shownum/2 && count($forums)>$shownum/2){
		$users=array_splice($users,0,count($users));
		$forums=array_splice($forums,0,$shownum-count($users));
	}else{
		$users=array_splice($users,0,count($users));
		$forums=array_splice($forums,0,count($forums));
	}
	$res[keyword]=$name;
	if(count($users)){
		$res[member]=$users;
	}
	if(count($forums)){
		$res[group]=$forums;
	}

}

echo json_encode($res);


function sphinxuserdata($name,$page,$pageSize){
	global $_G;
	$host =$_G[config]['sphinx']['hostname'];
	$port = $_G[config]['sphinx']['server_port'];
	$index = "article";
	$page=empty($page)?1:$page;
	$pageSize=empty($pageSize)?20:$pageSize;

	$sc = new SphinxClient ();
	$sc->SetServer($host,$port);
	$sc->SetLimits(($page-1)*$pageSize, $pageSize);
	$sc->SetMatchMode ( SPH_MATCH_EXTENDED );
	$sc->SetArrayResult ( true );
	$result = $sc->Query ( $name, $index );
	return $result;

}

function sphinxfollowdata($name,$uid,$page,$pageSize){
	global $_G;
	$host =$_G[config]['sphinx']['hostname'];
	$port = $_G[config]['sphinx']['server_port'];
	$index = "friend";
	$page=empty($page)?1:$page;
	$pageSize=empty($pageSize)?20:$pageSize;

	$sc = new SphinxClient ();
	$sc->SetServer($host,$port);
	$sc->SetLimits(($page-1)*$pageSize, $pageSize);
	$sc->SetMatchMode ( SPH_MATCH_EXTENDED );
	$sc->SetArrayResult ( true );
	$sc->SetFilter('uid',array($uid));
	$sc->SetFilter('type',array('1','3'));
	$result = $sc->Query ( $name, $index );
	return $result;

}

function sphinxforumdata($name,$page,$pageSize){
	global $_G;
	$host =$_G[config]['sphinx']['hostname'];
	$port = $_G[config]['sphinx']['server_port'];
	$index = "forum";
	$page=empty($page)?1:$page;
	$pageSize=empty($pageSize)?20:$pageSize;

	$sc = new SphinxClient ();
	$sc->SetServer($host,$port);
	$sc->SetLimits(($page-1)*$pageSize, $pageSize);
	$sc->SetMatchMode ( SPH_MATCH_EXTENDED );
	$sc->SetArrayResult ( true );
	$sc->SetFilter( 'jointype', array(0) );
	$result = $sc->Query ( $name, $index );

	return $result;

}
function redisuserdata($keysarr=array()){
	global $_G;
	$redis = new Redis();
	$redis->connect($_G[config]['memory']['redis']['server'],$_G[config]['memory']['redis']['port']);
	for($i=0; $i<count($keysarr); $i++){
		$list[$keysarr[$i]['id']]=$redis->hmget($keysarr[$i]['id'],array('uid','username','realname','userprovince'));
		$list[$keysarr[$i]['id']][name]=$list[$keysarr[$i]['id']][realname];
		$list[$keysarr[$i]['id']][userprovince]=$list[$keysarr[$i]['id']][userprovince];
		$list[$keysarr[$i]['id']][icon]=useravatar($list[$keysarr[$i]['id']][uid]);
		$list[$keysarr[$i]['id']][type]="user";
		$list[$keysarr[$i]['id']][followed]="0";
	}
	return $list;
}

function redisforumdata($keysarr=array()){
	global $_G;
	$redis = new Redis();
	$redis->connect($_G[config]['memory']['redis']['server'],$_G[config]['memory']['redis']['port']);
	$redis->select(1);
	for($i=0; $i<count($keysarr); $i++){
		$list[$keysarr[$i]['id']]=$redis->hmget($keysarr[$i]['id'],array('fid','name','description','icon','membernum'));
		$list[$keysarr[$i]['id']][type]="group";
		$list[$keysarr[$i]['id']][joined]='0';
	}
	return $list;
}
?>