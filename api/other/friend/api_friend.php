<?php


/*
 * Created on 2012-4-24
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require dirname(dirname(dirname(dirname(__FILE__)))) . '/source/class/class_core.php';
$discuz = & discuz_core :: instance();
$discuz->init();
global $_G;
$action = $_G['gp_ac'];
if ($action) {
	$action ();
}
/*
* 根据uid查找自己的好友
*
* */
function ufriend() {
	global $_G;
	$per = $_G['gp_per'] ? $_G['gp_per'] : 10;
	$uid = $_G['gp_uid'];
	$res[user]=array();
	if (!$uid) {
	  $frienddata =  array();
	  $resobj[user]=$frienddata;
      $resobj[user]=(object)$resobj;
      $jsondata=json_encode($resobj[user]);
      echo $jsondata;
	  exit ();
	} else {
       $iconurl= $_G[config][media][url];
       $uservalue = DB :: query("select distinct fuid ,fusername  from pre_home_friend where uid in(select fuid from pre_home_friend where uid=".$uid." and type in(3,1)) and type in(3,1) and fuid not in (select fuid from pre_home_friend where uid=".$uid." and type in(3,1)) limit 0,".$per);//关注人的列表
		if ($uservalue == false) {
			$frienddata = array();
		}else{
		while ($value = DB :: fetch($uservalue)) {
			$arr[uid] = $value['fuid'];
			$arr[username]=user_get_user_name($value['fuid']);
			$arr[iconurl]=$iconurl.useravatar($value['fuid']);
			$arraydata[]=$arr;
		}
           foreach($arraydata as $arr){
           	$newdata[uid]=$arr[uid];
           	$newdata[username]=user_get_user_name($arr[uid]);
			$newdata[iconurl]=$iconurl.useravatar($arr[uid]);
			$sql="select distinct fuid from pre_home_friend where uid=".$newdata[uid]."  and type in(3,1) and fuid in(select fuid from pre_home_friend where uid=".$uid."  and type in(3,1)) and fuid!=".$newdata[uid];
		$count=DB :: result_first("select count(distinct fuid) from pre_home_friend where uid=".$newdata[uid]." and type in(3,1) and fuid in(select fuid from pre_home_friend where uid=".$uid."  and type in(3,1)) and fuid!=".$newdata[uid]);
		if($count>3){
             $sql=$sql." limit 0,3";
		}
            $tjquery=DB::query($sql);
              while ($tjvalue=DB::fetch($tjquery)) {
                     $tjfriend[uid]=$tjvalue[fuid];
                     $tjfriend[username]=user_get_user_name($tjvalue[fuid]);
                     $tjdata[]=$tjfriend;
			}
		   $newdata[indirectnum]=$count;
           $newdata[indirectlist]=$tjdata;
           $frienddata[]=$newdata;
           unset($tjdata);
           }
		}
      $resobj[user]=$frienddata;
      $resobj[user]=(object)$resobj;
      $jsondata=json_encode($resobj[user]);
      echo $jsondata;
	  exit ();
	}
}
function openFileAPI($url) {
	$opts = array (
		'http' => array (
			'method' => 'GET',
			'timeout' => 300000,
		)
	);
	$context = @ stream_context_create($opts);
	$result = file_get_contents($url, false, $context);
	return $result;
}

function dfriends() {
	global $_G;
	$per = $_G['gp_per'] ? $_G['gp_per'] : 10;
	$uid = $_G['gp_uid'];
	require_once dirname(dirname(dirname(dirname(__FILE__)))) . "/source/api/lt_org/esn.php";
	$esnuserstr = new ESN();
	//查找好友
	$userarr = array ();
	$iconurl= $_G[config][media][url];
	if(!$uid){
		$frienddata=array();
		 $resobj[user]=$frienddata;
      $resobj[user]=(object)$resobj;
      $jsondata=json_encode($resobj[user]);
      echo $jsondata;
	  exit ();
	}else{
	$info = DB :: query("select distinct fuid from pre_home_friend where uid=" . $uid . " and type in(3,1)");
	if ($info == false) {
		$userarr[] = '';
	} else {
		while ($infovalue = DB :: fetch($info)) {
			$obj[] = $infovalue;
		}
			$data = $esnuserstr->getuserOrg($uid,$per);
			if ($data) {
				for ($i = 0; $i < sizeof($data); $i++) {
					if (!in_array($data[$i][uid], $obj)) {
						 array_push($userarr, $data[$i][uid]);
					}
				}
			}
		for ($i = 0; $i < sizeof($userarr); $i++) {
			$newdata[uid]=$userarr[$i];
           	$newdata[username]=user_get_user_name($userarr[$i]);
			$newdata[iconurl]=$iconurl.useravatar($userarr[$i]);
			$sql="select distinct fuid from pre_home_friend where uid=".$userarr[$i]."  and type in(3,1) and fuid in(select fuid from pre_home_friend where uid=".$uid."  and type in(3,1)) and fuid !=$userarr[$i]";
			$count=DB::result_first("select count(distinct fuid) from pre_home_friend where uid=".$userarr[$i]." and type in(3,1) and fuid in(select fuid from pre_home_friend where uid=".$uid."  and type in(3,1)) and fuid!=$userarr[$i]");
			if($count>3){
              $sql=$sql." limit 0,3";
			}
			$tjarrqury=DB :: query($sql);
			while($tjvalue=DB::fetch($tjarrqury)){
				$tjvdata[uid]=$tjvalue[fuid];
				$tjvdata[username]=user_get_user_name($tjvalue[fuid]);
				$tjarr[]=$tjvdata;
			}
			$newdata[indirectnum]=count($tjarr);
			$newdata[indirectlist]=$tjarr;
			$frienddata[]=$newdata;
			unset($tjarr);
		}
	}
		$resobj[user]=$frienddata;
		$resobj[user]=(object)$resobj;
        $jsondata=json_encode($resobj[user]);
        echo $jsondata;
		exit ();
	}
}
?>
