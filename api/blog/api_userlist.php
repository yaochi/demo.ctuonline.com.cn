<?php
/* Function: 我关注人的，我的粉丝，我的好友
 * Com.:
 * Author: yangyang
 * Date: 2011-10-25
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_core.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uid=$_G['gp_uid'];
$type=empty($_G['gp_type'])?1:$_G['gp_type'];


if($uid && $type){
	$query=DB::query("select * from ".DB::TABLE("home_friend")." where (type='".$type."' or type=3) and uid=".$uid);
	while($value=DB::fetch($query)){
		$newvalue[friendID]=$value[fuid];
		$newvalue[friendName]=user_get_user_name($value[fuid]);
		$newvalue[imageurl]=useravatar($value[fuid]);
		$newvalue[fans]=0;
		$fuidarr[]=$value[fuid];
		$list[]=$newvalue;
	}
	
	if($fuidarr){
		$query=DB::query("select uid,fans from ".DB::TABLE("common_member_status")." where uid in (".implode(',',$fuidarr).")");
		while($fvalue=DB::fetch($query)){
			for($i=0;$i<count($list);$i++){
				if($list[$i][friendID]==$fvalue[uid]){
					$list[$i][fans]=$fvalue[fans];
				}
			}
		}
	}

}
$res['list']=$list;
echo json_encode($res);
?>