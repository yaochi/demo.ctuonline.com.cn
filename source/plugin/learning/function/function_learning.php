<?php


/*
 * Created on 2011-11-30
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}
define('DISCUZ_CORE_FUNCTION', true);

require_once libfile('function/discuzcode');
function getlearnExcitation($examinestatus, $addurl,$addsql) {
	global $_G;
	$username = getusername($_G[uid]);
	$perpage = 20;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page -1) * $perpage;
	$sql = "SELECT * FROM " . DB :: table('learning_excitation') . " where examinestatus=3 ".$addsql."  LIMIT $start,$perpage";
	$getcount = DB :: result_first("SELECT count(*) FROM " . DB :: table('learning_excitation') . " WHERE fid=" . $_G['fid'] . " and examinestatus=3 ".$addsql);
	$info = DB :: query($sql);
	$learnexcit = array ();
	if ($info == False) {
		return 0;
	} else {
		while ($value = DB :: fetch($info)) {
			$learnexcit[] = $value;
		}
	}
	$url = "forum.php?mod=" . $_G['mod'] . "&action=plugin&fid=" . $_G['fid'] . "&plugin_name=learning&plugin_op=groupmenu" . $addurl;
	if ($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}
	return array (
		"multipage" => $multipage,
		"learnexcit" => $learnexcit,
		'mod' => $_G['mod'],
		"getcount" => $getcount
	);
}
function changeusername($username) {
	$sql = "select count(*) as count from " . DB :: table("common_member") . " where username = '" . $username."'";
	$info = DB :: query($sql);
	$value = DB :: fetch($info);
	return  $value[count];
}
function getlearnmv($learnid) {
	global $_G;
	$sql = "select * from " . DB :: table("learning_excitation") . " where id = " . $learnid;
	$info = DB :: query($sql);
	if ($info == false) {
		return 0;
	} else {
		while ($value = DB :: fetch($info)) {
			$obj[] = $value;
		}
		return $obj;
	}
}

function getlearning($learnid) {
	global $_G;
	$sql = "select * from " . DB :: table("learning_excitation") . " where id = " . $learnid;
	$info = DB :: query($sql);
	if ($info == false) {
		return 0;
	} else {
		$value = DB :: fetch($info);
		return $value;
	}
}

function changelearn($learid, $learnsource, $learnHarvest, $learnaction, $learnachievements) {
	global $_G;
	$info = DB :: query("SELECT count(*) as count FROM pre_learning_excitation where id=" . $learid);
	$value = DB :: fetch($info);
	if ($value[count] != 0) {
		DB :: query("UPDATE pre_learning_excitation set learnsource='" . $learnsource . "',learnHarvest='" . $learnHarvest . "',learnaction='" . $learnaction . "',learnachievements='" . $learnachievements . "' where id=" . $learid);
		return 1;
	}
	return 0;
}
function edit($learnid) {
	global $_G;
	$sql = "select * from " . DB :: table("learning_excitation") . " where id = " . $learnid;
	$query = DB :: query($sql);
	if ($query == false) {
		return 0;
	} else {
		while ($value = DB :: fetch($query)) {
			$obj[] = $value;
		}
		return $obj;
	}
}
function changreleaselearn($learid, $subusername, $witnessusername) {
	global $_G;
	$info = DB :: query("SELECT count(*) as count FROM pre_learning_excitation where id=" . $learid);
	$value = DB :: fetch($info);
	if ($value[count] != 0) {
		DB :: query("UPDATE pre_learning_excitation set examinestatus=3,examinedateline=" . time() . " where id=" . $learid);
		return 1;
	}
	return 0;
}
function learnoption($learid,$type,$optionmessage,$status){
	global $_G;
	$dataline=dgmdate(time());
	$realname=getrealname($_G[uid]);

  DB::insert("larnsouce_harvestoption",array(
  "learnid"=>$learid,
  "optionmessage"=>$optionmessage,
  "type"=>$type,
  "authorer"=>$realname,
  "dataline"=>time(),
  "athoreruid"=>$_G[uid],
  "status"=>$status
  ));

  $id=DB::insert_id();
  if($id)
   return $id;
}


function createoptionreply($uid, $subusername, $subrealname, $learid, $autherid, $authorname, $type, $tap, $replymessage) {
	global $_G;
	DB :: insert("opinion_reply", array (
		'uid' => $uid,
		'username' => $subusername,
		'realname' => $subrealname,
		'replmessage' => $replymessage,
	'replydateline' => time(), 'authorer' => $authorname, 'type' => 2, 'learnmvid' => $learid, 'authoreruid' => $_G[uid], 'tap' => $tap));
	$id = DB :: insert_id();
	return $id;
}
function viewreply($learnid,$tap) {
	global $_G;
	$perpage = 20;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page -1) * $perpage;

	if ($tap == 1) {
		$sql = "select * from pre_opinion_reply where learnmvid=" . $learnid." and tap=1" ;
	}
	if ($tap == 2) {
		$sql = "select * from pre_opinion_reply where learnmvid=" . $learnid . " and tap=2 ";
	}
	$query = DB :: query($sql);
	if ($query == false) {
		return 0;
	} else {
		while ($value = DB :: fetch($query)) {
			$obj[] = $value;
		}
		return $obj;
	}
}
function viewreplynum($learnid, $uid) {
	$sql = "select count(*) as num from pre_opinion_reply where learnmvid=" . $learnid ;
	$query = DB :: query($sql);
	$value = DB :: fetch($query);
	return $value[num];
}
function getwitnessuid($username) {
	global $_G;
	$sql = "select uid as uid from pre_common_member where username = '" . $username . "'";
	$query = DB :: query($sql);
	$uid = DB :: fetch($query);
	return $uid[uid];
}
function getusername($uid) {
	global $_G;
	$sql = "select  username from " . DB :: table("common_member") . " where uid = " . $uid;
	$query = DB :: query($sql);
	$username = DB :: fetch($query);
	return $username[username];
}
function getrealname($uid) {
	global $_G;
	$sql = "select realname from pre_common_member_profile where uid=" . $uid;
	$query = DB :: query($sql);
	$username = DB :: fetch($query);
	return $username[realname];
}
function getusenews() {
	global $_G;
	$obj=array();
	$sql = "select cm.username as username,cmf.realname as realname from pre_common_member  cm , pre_common_member_profile cmf where cm.uid=" . $_G[uid] . " and cmf.uid=" . $_G[uid];
	$query = DB :: query($sql);
	$info = DB :: fetch($query);
    return $info;

}
function publishnolear(){
	global $_G;
	$sql="select * from pre_learning_excitation where uid=".$_G[uid]." and examinestatus=1";
	$query = DB :: query($sql);
    $numsql="select count(*) as  count from pre_learning_excitation where uid=".$_G[uid]." and examinestatus=1";
    $numquery = DB :: query($numsql);
    $info = DB :: fetch($numquery);
    if($query==false){
     return false;
    }else {
		while ($value = DB :: fetch($query)) {
              $obj[]=$value;
		}
	}
   return array("value"=>$obj,"learnnum"=>$info[count]);
}
function learingoption($learnid,$type){
	global $_G;
    $sql="select * from pre_larnsouce_harvestoption where learnid=".$learnid." and type=".$type;
    $query=DB::query($sql);
    if($query==false){
    	return 0;
    }else {
		while ($value =DB :: fetch($query)) {
         $obj[] = $value;
		}
	}
	return $obj;
}

/**
 * 掉接口根据regname获取用户所在省公司
 */
//function openFileAPIcompany($url) {
//	$opts = array (
//		'http' => array (
//			'method' => 'GET',
//			'timeout' => 300000,
//		)
//	);
//	$context = @ stream_context_create($opts);
//	$result = file_get_contents($url, false, $context);
//	return $result;
//}

function getprovince($regname){
	global $_G;
	$FILE_SEARCH_PAGE = "http://" . $_G[config][expert][activeurl];
	$FILE_SEARCH_PAGE .= "/api/user/getuserprovinceorg.do?regname=" . $regname;
	$str1 = openFileAPIcompany($FILE_SEARCH_PAGE);
	$filejson = json_decode($str1, true);
	if($filejson==null)	return "";
	if(strlen($filejson[groupname])>=18){
		if(substr($filejson[groupname],0,12)=='中国电信')
			return substr($filejson[groupname],12);
		return substr($filejson[groupname],0,18);
	}
	return $filejson[groupname];
}


/**
 * 学习积分操作
 *
 *用户信息 uid-用户ID,regname-用户网大帐号,realname-用户真实姓名
 *type 1-学习激励 2-意见箱 3-学习力评估 4-有奖问卷
 *mode 1-新建 2-审核通过 3-证明奖励 4-奖励 5-其他
 *credit 积分>0
 *company 所在公司
 *optionid 如学习激励ID
 *
 *积分表pre_learn_credit,积分记录表pre_learncredit_record
 *
 *调用接口根据regname获取所在省公司getprovince($regname)
 *根据uid获取realname-user_get_user_name
 */
function op_learncredit($uid,$regname,$realname,$type,$mode,$optionid,$credit,$company){
	$info=DB :: fetch(DB :: query("select count(*) as count from pre_learn_credit where username='".$regname."'"));
	if($info[count]==0){
		//查找用户所在省公司
		$province=getprovince($regname);
		if($province!="") $company=$province;

		//查找用户真实姓名
		if($mode==3){
			$realname=user_get_user_name($uid);
		}
		$learn_credit=array('uid' => $uid,'username' => $regname,'realname' => $realname,'totalcredit' => $credit,'exchangecredit' => $credit,'company' => $company);
		DB :: insert("learn_credit", $learn_credit);
	} else {
		$up_sql="update pre_learn_credit set totalcredit=totalcredit+".$credit.",exchangecredit=exchangecredit+".$credit." where username='".$regname."'";
		DB :: query($up_sql);
	}
	//更新积分记录
	$learncredit_record=array('uid' => $uid,'username' => $regname,'type' => $type,'mode' => $mode,'objectid' => $optionid,'credit' => $credit,'dateline'=>time());
	DB :: insert("learncredit_record", $learncredit_record);
}
//获取上传文件
function getfile($type){
	 global $_G;
	 $id=getmaxlearnid();
	 $sql="select * from pre_learnupload where  learnid=$id";
	 $query=DB::query($sql);
    if($query==false){
    	return 0;
    }else {
		while ($value =DB :: fetch($query)) {
         $obj[] = $value;
		}
	}
	return $obj;

}
function creatlarn($confidenceindex,$subrealname,$subusername,$subdeptname,$subcompanyname,$subtel,$subPost,$learnsource,$learnHarvest,$learnaction,$learnachievements
	 ,$learnname,$Witnessrealname,$Witnessrealname,$Witnessusername,$Witnessdeptname,$WitnessPost,$Witnesscompanyname,$Witnesstel,$savestatus){
	 global $_G;
	 if($savestatus==2){
      $remindstat=1;//发布之后状态改为 1，发送短信 状态改为 0;
	 }if($savestatus==1){
	  $remindstat=-1;//默认为 -1;
	 }
	DB :: insert("learning_excitation", array (
		'subrealname' => $subrealname,
		'subusername' => $subusername,
		'subdeptname' => $subdeptname,
		'subcompanyname' =>$subcompanyname,
		'subtel' => $subtel,
		'subPost' =>$subPost,
		'learnsource' => $learnsource,
		'learnHarvest' => $learnHarvest,
		'learnaction' => $learnaction,
		'learnachievements' => $learnachievements,
		'learnname' => $learnname,
		'Witnessrealname' => $Witnessrealname,
		'Witnessusername' => $Witnessusername,
		'Witnessdeptname' => $Witnessdeptname,
		'WitnessPost'=>$WitnessPost,
		'Witnesscompanyname' => $Witnesscompanyname,
		'Witnesstel' => $Witnesstel,
		'examinestatus' => $savestatus,
	    'subdateline' => time(),
	    'confidenceindex'=>$confidenceindex,
	    'remindstat'=>$remindstat,
        'fid' => $_G['fid'],
        'uid' => $_G['uid']));
	$id = DB :: insert_id();
	return $id;
}

function savelearning($confidenceindex,$subrealname,$subusername,$subdeptname,$subcompanyname,$subtel,$subPost,$learnsource,$learnHarvest,$learnaction,$learnachievements
	 ,$learnname,$Witnessrealname,$Witnessrealname,$Witnessusername,$Witnessdeptname,$WitnessPost,$Witnesscompanyname,$Witnesstel,$savestatus,$id){
	 global $_G;
	 if($savestatus==2){
      $remindstat=1;//发布之后状态改为 1，发送短信 状态改为 0;
	 }if($savestatus==1){
	  $remindstat=-1;//默认为 -1;
	 }
	 $where=array("id"=>$id);
	DB :: update("learning_excitation", array (
		'subrealname' => $subrealname,
		'subusername' => $subusername,
		'subdeptname' => $subdeptname,
		'subcompanyname' =>$subcompanyname,
		'subtel' => $subtel,
		'subPost' =>$subPost,
		'learnsource' => $learnsource,
		'learnHarvest' => $learnHarvest,
		'learnaction' => $learnaction,
		'learnachievements' => $learnachievements,
		'learnname' => $learnname,
		'Witnessrealname' => $Witnessrealname,
		'Witnessusername' => $Witnessusername,
		'Witnessdeptname' => $Witnessdeptname,
		'WitnessPost'=>$WitnessPost,
		'Witnesscompanyname' => $Witnesscompanyname,
		'Witnesstel' => $Witnesstel,
		'examinestatus' => $savestatus,
	    'subdateline' => time(),
	    'confidenceindex'=>$confidenceindex,
        'fid' => $_G['fid'],
        'uid' => $_G['uid'],
        'remindstat'=>$remindstat),$where);
}

function gontenfile($filestr){
    $gonten= explode('.',$filestr);  //用点号分隔文件名到数组
    $gonten = array_reverse($gonten);  //把上面数组倒序
    return $gonten[0]; //返回倒序数组的第一个值
}
//根据学习力评估的id查找状态
function getlearstatus($learid){
$sql="select  examinestatus   as examinestatus from pre_learning_excitation where id=".$learid;
$info=DB::query($sql);
$value=DB::fetch($info);
return $value[examinestatus];
}
function learngetattach($id, $idtype) {
	global $_G;

	require_once libfile('function/attachment');
	$attachs = $imgattachs = array();
	if($id){
    $addsql=" where learid=".$id;
	}
	$query = DB::query("select * from pre_learn_attachment $addsql");
	$allowext = '';

	while($attach = DB::fetch($query)) {
		$attach['filenametitle'] = $attach['filename'];
		$attach['ext'] = fileext($attach['filename']);

		$attach['filename'] = cutstr($attach['filename'], $_G['setting']['allowattachurl'] ? 25 : 30);
		$attach['attachsize'] = sizecount($attach['filesize']);
		$attach['dateline'] = dgmdate($attach['dateline']);
		$attach['filetype'] = attachtype($attach['ext']."\t".$attach['filetype']);
		if($attach['isimage'] < 1) {
			if($attach['isimage']) {
				$attach['url'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
				$attach['width'] = $attach['width'] > 110 ? 110 : $attach['width'];
			}
			if($attach['learid']) {
				$attachs['used'][] = $attach;
			} else {
				$attachs['unused'][] = $attach;
			}
		} else {
			$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'group/';
			$attach['width'] = $attach['width'] > 110 ? 110 : $attach['width'];
			if($attach['learid']) {
				$imgattachs['used'][] = $attach;
			} else {
				$imgattachs['unused'][] = $attach;
			}
		}
	}
	return array('attachs' => $attachs, 'imgattachs' => $imgattachs);
}
function learngetattachs($id,$type,$imgid = 0) {
	global $_G;

	require_once libfile('function/attachment');
	if($id){
    $addsql=" where learid=".$id." and type=".$type;
	}
	$query = DB::query("select * from pre_learn_attachment $addsql");
	$allowext = '';

	while($attach = DB::fetch($query)) {
		if($attach['aid'] == $imgid){
			continue;
		}
		$attach['filenametitle'] = $attach['filename'];
		$attach['ext'] = fileext($attach['filename']);
//		if($allowext && !in_array($attach['ext'], $allowext)) {
//			continue;
//		}
		$attach['aidencode'] = aidencode($attach['aid']);
		$attach['filename'] = cutstr($attach['filename'], $_G['setting']['allowattachurl'] ? 25 : 30);
		$attach['attachsize'] = sizecount($attach['filesize']);
		$attach['dateline'] = dgmdate($attach['dateline']);
		$attach['filetype'] = attachtype($attach['ext']."\t".$attach['filetype']);
		if($attach['isimage'] < 1) {
			if($attach['isimage']) {
				$attach['url'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
				$attach['width'] = $attach['width'] > 110 ? 110 : $attach['width'];
			}
			$attachs[] = $attach;
		} else {
			$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'group/';
			$attach['attachwidth'] = attachwidth($attach['width']);
			$attach['width'] = $attach['width'] > 110 ? 110 : $attach['width'];
			$imgattachs[] = $attach;
		}
	}
	return array('attachs' => $attachs, 'imgattachs' => $imgattachs);
}
function updatelearattachent($attachentarray,$id){
		global $_G;
		if($attachentarray['sorceaid']){
			$where=array("aid"=>$attachentarray['sorceaid']);
	 	DB :: update("learn_attachment",array("learid"=>$id),$where);
		}
		if($attachentarray['harvestaid']){
			$where=array("aid"=>$attachentarray['harvestaid']);
				DB :: update("learn_attachment",array("learid"=>$id),$where);
		}
		if($attachentarray['actionaid']){
			$where=array("aid"=>$attachentarray['actionaid']);
				DB :: update("learn_attachment",array("learid"=>$id),$where);
		}
		if($attachentarray['chievementsaid']){
			$where=array("aid"=>$attachentarray['chievementsaid']);
				DB :: update("learn_attachment",array("learid"=>$id),$where);
		}
		return 1;
}
function learnviewgetattachs($id,$type,$imgid = 0) {
	global $_G;

	require_once libfile('function/attachment');
	if($id){
    $addsql=" where aid=".$id." and type=".$type;
	}
	$query = DB::query("select * from pre_learn_attachment $addsql");
	$allowext = '';

	while($attach = DB::fetch($query)) {
		if($attach['aid'] == $imgid){
			continue;
		}
		$attach['filenametitle'] = $attach['filename'];
		$attach['ext'] = fileext($attach['filename']);
//		if($allowext && !in_array($attach['ext'], $allowext)) {
//			continue;
//		}
		$attach['aidencode'] = aidencode($attach['aid']);
		$attach['filename'] = cutstr($attach['filename'], $_G['setting']['allowattachurl'] ? 25 : 30);
		$attach['attachsize'] = sizecount($attach['filesize']);
		$attach['dateline'] = dgmdate($attach['dateline']);
		$attach['filetype'] = attachtype($attach['ext']."\t".$attach['filetype']);
		if($attach['isimage'] < 1) {
			if($attach['isimage']) {
				$attach['url'] = $attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl'];
				$attach['width'] = $attach['width'] > 110 ? 110 : $attach['width'];
			}
			$attachs[] = $attach;
		} else {
			$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'group/';
			$attach['attachwidth'] = attachwidth($attach['width']);
			$attach['width'] = $attach['width'] > 110 ? 110 : $attach['width'];
			$imgattachs[] = $attach;
		}
	}
	return array('attachs' => $attachs, 'imgattachs' => $imgattachs);
}
?>
