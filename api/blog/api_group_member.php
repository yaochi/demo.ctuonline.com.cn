<?php
/* Function: 某个好友分组下的好友
 * Com.:
 * Author: yangyang
 * Date: 2011-12-2
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_space.php';
$discuz = & discuz_core::instance();

$discuz->init();
$uid=$_G['gp_uid'];
$gid=$_G['gp_gid'];
$num=empty($_G['gp_num'])?0:$_G['gp_num'];
$shownum=empty($_G['gp_shownum'])?10:$_G['gp_shownum'];


if($uid && ($gid||$gid==0)){
	if($gid==-1){
		$wheresql=" and gids='' ";
	}else{
		$wheresql=" and gids like '%,".$gid.",%' ";
	}
	if($shownum==-1){
		$query=DB::query("select * from ".DB::TABLE("home_friend")." where uid=".$uid." and (type=1 or type=3) ".$wheresql." order by dateline ");
	}else{
		$query=DB::query("select * from ".DB::TABLE("home_friend")." where uid=".$uid." and (type=1 or type=3) ".$wheresql." order by dateline limit $num,$shownum ");
	}
	while($value=DB::fetch($query)){
		$newvalue[friendID]=$value[fuid];
		$newvalue[friendName]=user_get_user_name($value[fuid]);
		$newvalue[imgurl]=useravatar($value[fuid]);
		$fuidarr[]=$value[fuid];
		$list[]=$newvalue;
	}

	if($fuidarr){
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
	}

}
$res['list']=$list;
echo json_encode($res);
?>