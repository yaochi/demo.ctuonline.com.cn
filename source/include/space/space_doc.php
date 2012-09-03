<?php

/* Function: 个人文档
 * Com.:
 * Author: wuhan
 * Date: 2010-8-3
 */
if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}

$minhot = $_G['setting']['feedhotmin'] < 1 ? 3 : $_G['setting']['feedhotmin'];
$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
if ($page < 1)
	$page = 1;
$id = empty ($_GET['id']) ? 0 : intval($_GET['id']);
$classid = $_GET['classid'] = empty ($_GET['classid']) ? 0 : intval($_GET['classid']);

require_once libfile('function/doc');

if (empty ($_GET['view']))
	$_GET['view'] = 'we';

$orderby = empty($_GET['orderby'])? "uploadtime" : $_GET['orderby'];
$orderseq = $_GET['orderseq'];
$classid = $_GET['classid'];
$orderseq = $_GET['orderseq'] ? 1: 2;
$timerange = $_GET['timerange'];
$secrity = $_GET['secrity'];

$perpage = 20;
$perpage = mob_perpage($perpage);
$start = ($page -1) * $perpage;
ckstart_max($start, $perpage);

$summarylen = 300;

$classarr = array ();
$list = array ();
$userlist = array ();
$count = $pricount = 0;

$gets = array (
	'mod' => 'space',
	'uid' => $space['uid'],
	'do' => 'doc',
	'view' => $_GET['view'],
	'display' => $_GET['display'],
	'timerange' => $_GET['timerange'],
	'orderby' => $_GET['orderby'],
	'orderseq' => $_GET['orderseq'],
	'classid' => $_GET['classid'],
	'display' => $_GET['display'],
	'secrity'=> $_GET['secrity'],
	'from'=>$_GET['from'],
);
$theurl = 'home.php?' . url_implode($gets);
$multi = '';

$need_count = true;
$manager = false;
if ($_GET['view'] == 'all') {
	$uid = '';
}
elseif ($_GET['view'] == 'me') {
	//获取分类
	$classarr["1"] = "未分类";
	
	$query = DB::query("SELECT classid, classname FROM ".DB::table('home_doc_class')." WHERE uid='$space[uid]'");
	while ($value = DB::fetch($query)) {
		$classarr[$value['classid']] = $value['classname'];
	}

	$uid = $space['uid'];

	if ($_GET['from'] == 'space')
		$diymode = 1;
	$secrity=0;
	$manager = true;

} else {
	space_merge($space, 'field_home');
	if ($space['feedfriend']) {
		$uid = array();
		
		$query = DB :: query("SELECT * FROM " . DB :: table('home_friend') . " WHERE uid='$space[uid]' ORDER BY num DESC LIMIT 0,500");
		while ($value = DB :: fetch($query)) {
			$uid[] = $value['fuid'];
		}
	} else {
		$need_count = false;
	}
}

$actives = array (
	$_GET['view'] => ' class="a"'
);

if ($need_count) {
	if ($searchkey = stripsearchkey($_GET['searchkey'])) {
	}
	
	switch($timerange){
		case 1:
		$uploadtimeto = $_G['timestamp'];
		$uploadtimefrom = $_G['timestamp'] - ($_G['timestamp'] % 86400) ;
		$uploadtimeto = $uploadtimeto."000";
		$uploadtimefrom = $uploadtimefrom."000";
		break;
		case 2:
		$uploadtimeto = $_G['timestamp'];
		$mondaytime = $_G['timestamp'] - ($_G['timestamp'] + 86400 * 3 + 3600 * 8) % (7 * 86400);
		$uploadtimefrom = $mondaytime;
		$uploadtimeto = $uploadtimeto."000";
		$uploadtimefrom = $uploadtimefrom."000";
		break;
		case 3:
		$uploadtimeto = $_G['timestamp'];
		$uploadtimefrom = $_G['timestamp'] - 30 * 86400;
		$uploadtimeto = $uploadtimeto."000";
		$uploadtimefrom = $uploadtimefrom."000";
		break;
		default:
	}
	$filejson = getFileList($perpage, $page, $uid, $classid, '','',$orderby,$orderseq,$uploadtimefrom, $uploadtimeto, '','',$searchkey,$secrity);
	//print_r($filejson);
	$list = $resourses = array();
	if($filejson){
		$count = $filejson['totalAmount'];
		$list = $filejson['resources'];
	}
}

$filelist = array();

if ($count) {
	$_G['home_today'] = $_G['timestamp'] - $_G['timestamp']%(3600*24);
	
	foreach($list as $key => $value){
		$value['uid'] = $value['userid'];
		//if(ckdocfriend($value) && ckdocstatus($value)){
			if($value['security'] == 4 && $value['uid'] != $_G['uid']){
				$value['keywords'] = array();
				$value['context'] = "";
				$value['averagescore'] = null;
				$value['readnum'] = null;
				$value['sharenum'] = null;
				$value['commentnum'] = null;
				$value['favoritenum'] = null;
				$value['downloadnum'] = null;
			}
			else{
				$value['keywords'] = explode(" ", $value['keyword']);
			}
			if($value['zoneid']&&$value['security'] == 16){
				$value['fname']=get_groupname_by_fid($value['zoneid']);
			}
			$value['uploadtime'] = $value['uploadtime']/1000;
			if($value['dateline']>=$_G['home_today']) {
				$value['uploadtime'] = dgmdate($value['uploadtime'], 'h:i');
			} else {
				$value['uploadtime'] = dgmdate($value['uploadtime']);
			}
			if(preg_match("/^[A-Za-z0-9]+$/",$value[username])){
				$value[username]=user_get_user_name_by_username($value[username]);
			}
			$filelist[] = $value;
		/*}
		else{
			$pricount++;
		}*/
	}
	$multi = multi($count, $perpage, $page, $theurl);
	
	include_once DISCUZ_ROOT.'./source/api/lt_org/user.php';
	$user=new User();
	$org_id=$user->getGroupidByUserid($_G['uid']);
	unset($user);

	if($org_id!='9001'&&$org_id!='9002'&&$org_id!=9001&&$org_id!=9002){
		$isGroupManager = "0";
	}else{
		$isGroupManager = "1";
	}
}


dsetcookie('home_diymode', $diymode);

space_merge($space, 'field_home');
if ($_GET['from'] == 'space'){
	include_once template("home/space_zone_doc_list");
}else{
	include_once template("diy:home/space_doc_list");
}


//对于别人只显示转化好的， 对于自己全部显示
function ckdocstatus($value){
	global $_G;
	if(empty($_G['uid'])) return $value['status'] == 3?false:true;
	if($value['uid'] == $_G['uid']) return true;
	return $value['status'] == 3?false:true;
}

function ckdocfriend($value) {
	global $_G, $space;

	if(empty($_G['uid'])) return $value['security'] == 1?false:true;
	if($value['uid'] == $_G['uid']) return true;

	$var = 'home_ckdocfriend_'.md5($value['uid'].'_'.$value['security']);
	if(isset($_G[$var])) return $_G[$var];

	$_G[$var] = false;
	switch ($value['security']) {
		case 1://全站
			$_G[$var] = true;
			break;
		case 2://仅自己可见
			break;
		case 4://凭密码
			$_G[$var] = true;
			break;
		case 8://全好友可见
			include_once libfile('function/friend');
			if(friend_check($value['uid'])) {
				$_G[$var] = true;
			}
			break;
		case 16://专区可见
			$_G[$var] =  false;
			break;	
		default:
			$_G[$var] = true;
	}
	return $_G[$var];
}
?>
