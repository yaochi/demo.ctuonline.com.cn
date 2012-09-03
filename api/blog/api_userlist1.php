<?php
/* Function: 我关注人的，我的粉丝，我的好友
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_space.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uid=$_G['gp_uid'];
$type=empty($_G['gp_type'])?1:$_G['gp_type'];
$num=empty($_G['gp_num'])?0:$_G['gp_num'];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];

if($uid && $type){
	if($shownum==-1){
		$query=DB::query("select * from ".DB::TABLE("home_friend")." where (type='".$type."' or type=3) and uid=".$uid." order by dateline ");
	}else{
		$query=DB::query("select * from ".DB::TABLE("home_friend")." where (type='".$type."' or type=3) and uid=".$uid." order by dateline limit $num,$shownum");
	}
	while($value=DB::fetch($query)){
		$newvalue[friendID]=$value[fuid];
		$newvalue[friendName]=user_get_user_name($value[fuid]);
		$newvalue[imageurl]=useravatar($value[fuid]);
		$newvalue[fans]='';
		$newvalue[blogs]='';
		$newvalue[follows]='';
		$newvalue[friends]='';
		$newvalue[info]='';
		$newvalue[com]='';
		//$fuidarr[]=$value[fuid];
		$list[]=$newvalue;
	}

	/*if($fuidarr){
		$query=DB::query("SELECT * FROM ".DB::table(common_member_status)." cms,".DB::table('common_member_profile')." profile  WHERE cms.uid=profile.uid and cms.uid in (".implode(',',$fuidarr).")");
		while($fvalue=DB::fetch($query)){
			for($i=0;$i<count($list);$i++){
				if($list[$i][friendID]==$fvalue[uid]){
					$list[$i][fans]=$fvalue[fans];
					$list[$i][blogs]=$fvalue[blogs];
					$list[$i][follows]=$fvalue[follow];
					$list[$i][friends]=$fvalue[friends];
					$list[$i][info]=$fvalue[bio];
					$reg=getuserbyuid($fvalue[uid]);
					$gro=getprogroup($reg[username]);
					$list[$i][com]=$gro[groupname];
				}
			}
		}
	}*/

}
$res['users']=$list;
echo json_encode($res);
?>