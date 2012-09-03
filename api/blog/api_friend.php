<?php


/*
 * Created on 2012-4-24
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require dirname(dirname(dirname(__FILE__))) . '/source/class/class_core.php';
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
	$rand_num = $_G['gp_size'] ? $_G['gp_size'] : 10;
	$uid = $_G['gp_uid'];
	if (!$uid) {
		$frienddata = array ();
		$resobj[data] = $frienddata;
		$resobj[data] = (object) $resobj;
		$jsondata = json_encode($resobj[data]);
		echo $jsondata;
		exit ();
	} else {
		$iconurl = $_G[config][image][url]."/";
		if($_G[config]['memory']['redis']['on']){
			$redis = new Redis();
			$redis->connect($_G[config]['memory']['redis']['server'],$_G[config]['memory']['redis']['port']);
			$interestarr=$redis->hmget($uid,array('interestpl'));
			$arraydata=unserialize($interestarr['interestpl']);
		}else{
			$uservalue = DB :: query("select distinct fuid  from pre_home_friend where uid in(select fuid from pre_home_friend where uid=" . $uid . " and type in(3,1)) and type in(3,1) and fuid not in (select fuid from pre_home_friend where uid=" . $uid . " and type in(3,1)) and fuid!=$uid"); //关注人的列表
			if ($uservalue == false) {
				$frienddata = array ();
			} else {
				while ($value = DB :: fetch($uservalue)) {
					//$arr[uid] = $value['fuid'];
					//$arr[username] = user_get_user_name($value['fuid']);
					//$arr[iconurl] = $iconurl . useravatar($value['fuid']);
					$arraydata[] = $value['fuid'];
				}
			}
		}
		    if (count($arraydata) != 0) {
              if(count($arraydata)>$rand_num){
					$rand_num=$rand_num;
                } else {
            		$rand_num=count($arraydata)-1;
				}

            $inputarr=array_rand($arraydata,$rand_num);
			for ($i = 0; $i < count($inputarr); $i++) {
				$newdata[uid] = $arraydata[$inputarr[$i]];
				$newdata[username] = user_get_user_name($arraydata[$inputarr[$i]]);
				$newdata[iconurl] = $iconurl . useravatar($arraydata[$inputarr[$i]]);
				$sql = "select distinct fuid from pre_home_friend where uid=" . $arraydata[$inputarr[$i]] . "  and type in(3,2) and fuid in(select fuid from pre_home_friend where uid=" . $uid . "  and type in(3,2)) and fuid not in (".$arraydata[$inputarr[$i]].",$uid)";
				$count = DB :: result_first("select count(distinct fuid) from pre_home_friend where uid=" . $arraydata[$inputarr[$i]] . " and type in(3,2) and fuid in(select fuid from pre_home_friend where uid=" . $uid . "  and type in(3,2)) and fuid not in (".$arraydata[$inputarr[$i]].",$uid)");
				if ($count > 3) {
					$sql = $sql . " limit 0,3";
				}
				$tjquery = DB :: query($sql);
				while ($tjvalue = DB :: fetch($tjquery)) {
					$tjfriend[uid] = $tjvalue[fuid];
					$tjfriend[username] = user_get_user_name($tjvalue[fuid]);
					$tjdata[] = $tjfriend;
				}
				$newdata[indirectnum] = $count;
				$newdata[indirectlist] = $tjdata;
				$frienddata[] = $newdata;
				unset ($tjdata);
			}
		} else {
			$frienddata = array ();
		}
		$resobj[data] = $frienddata;
		$resobj[data] = (object) $resobj;
		$jsondata = json_encode($resobj[data]);
		echo $jsondata;
		exit ();
	}
}

function dfriend() {
	global $_G;
	$ip = $_G[config][esn][ip];
	$dbuser = $_G[config][esn][dbuser];
	$dbpasswd = $_G[config][esn][dbpasswd];
	$rand_num = $_G['gp_size'] ? $_G['gp_size'] : 10;
	$uid = $_G['gp_uid'];
	require_once dirname(dirname(dirname(__FILE__))) . "/source/api/lt_org/esn.php";
	$esnuserstr = new ESN();
	//查找好友
	$userarr = array ();
	$iconurl = $_G[config][image][url]."/";
	if (!$uid) {
		$frienddata = array ();
		$resobj[data] = $frienddata;
		$resobj[data] = (object) $resobj;
		$jsondata = json_encode($resobj[data]);
		echo $jsondata;
		exit ();
	} else {
		$info = DB :: query("select distinct fuid from pre_home_friend where uid=" . $uid . " and type in(3,1)");
		if ($info == false) {
			$userarr[] = '';
		} else {
			while ($infovalue = DB :: fetch($info)) {
				$obj[] = $infovalue;
			}
			if (count($obj) != 0) {
					for ($i = 0; $i < sizeof($obj); $i++) {
						if($i==sizeof($obj)-1){
					   $str.=$obj[$i][fuid];
						}else{
						$str.=$obj[$i][fuid].",";
						}
					}
					if($str){
						$str=$str.",".$uid;
					}else{
						$str=$uid;
					}
				$userarr = $esnuserstr->getuserOrg($ip, $dbuser, $dbpasswd, $uid,$str);
				if(count($userarr)>$rand_num){
                $rand_num=$rand_num;
                }else{
            	$rand_num=count($userarr)-1;
                }
                $inputarr=array_rand($userarr,$rand_num);
				for ($i = 0; $i < sizeof($inputarr); $i++) {
					$newdata[uid] = $userarr[$inputarr[$i]][uid];
					$newdata[username] = user_get_user_name($userarr[$inputarr[$i]][uid]);
					$newdata[iconurl] = $iconurl . useravatar($userarr[$inputarr[$i]][uid]);
					$sql = "select distinct fuid from pre_home_friend where uid=" . $userarr[$inputarr[$i]][uid] . "  and type in(3,2) and fuid in(select fuid from pre_home_friend where uid=" . $uid . "  and type in(3,2)) and fuid not in (".$userarr[$inputarr[$i]][uid].",$uid)";
					$count = DB :: result_first("select count(distinct fuid) from pre_home_friend where uid=" . $userarr[$inputarr[$i]][uid] . " and type in(3,2) and fuid in(select fuid from pre_home_friend where uid=" . $uid . "  and type in(3,2)) and fuid not in (".$userarr[$inputarr[$i]][uid].",$uid)");
					if ($count > 3) {
						$sql = $sql . " limit 0,3";
					}
					$tjarrqury = DB :: query($sql);
					while ($tjvalue = DB :: fetch($tjarrqury)) {
						$tjvdata[uid] = $tjvalue[fuid];
						$tjvdata[username] = user_get_user_name($tjvalue[fuid]);
						$tjarr[] = $tjvdata;
					}
					$newdata[indirectnum] = $count;
					$newdata[indirectlist] = $tjarr;
					$frienddata[] = $newdata;
					unset ($tjarr);
				}
				for ($i = 0; $i < sizeof($frienddata); $i++) {
					$firnd[uid] = $frienddata[$i][uid];
					$firnd[username] =$frienddata[$i][username];
					$firnd[iconurl] = $frienddata[$i][iconurl];
					$firnd[indirectnum] = $frienddata[$i][indirectnum];
					$firnd[indirectlist] = $frienddata[$i][indirectlist];
					$friendarr[] = $firnd;
				}
			} else {
				$friendarr = array ();
			}
		}
		$resobj[data] = $friendarr;
		$resobj[data] = (object) $resobj;
		$jsondata = json_encode($resobj[data]);
		echo $jsondata;
		exit ();
	}
}

function multi_array_sort($multi_array, $sort_key, $sort = SORT_DESC) {
	if (is_array($multi_array)) {
		foreach ($multi_array as $row_array) {
			if (is_array($row_array)) {
				$key_array[] = $row_array[$sort_key];
			} else {
				return false;
			}
		}
	} else {
		return false;
	}
	array_multisort($key_array, $sort, $multi_array);
	return $multi_array;
}
?>
