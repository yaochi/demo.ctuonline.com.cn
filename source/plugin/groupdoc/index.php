<?php
/* Function: 专区文档
 * Com.:
 * Author: wuhan
 * Date: 2010-8-4
 */
if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}

if (empty ($_G['fid'])) {
	showmessage('group_rediret_now', 'group.php');
}

require_once libfile('function/home');

$minhot = $_G['setting']['feedhotmin'] < 1 ? 3 : $_G['setting']['feedhotmin'];
$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
if ($page < 1)
	$page = 1;
$id = empty ($_GET['id']) ? 0 : intval($_GET['id']);
$typeid = $_GET['typeid'] = empty ($_GET['typeid']) ? 0 : intval($_GET['typeid']);

require_once libfile('function/doc');

$orderby = empty($_GET['orderby']) || !in_array($_GET['orderby'], array('uploadtime', 'title', 'averagescore', 'read', 'share', 'comment', 'favorite', 'download'))? "uploadtime" : $_GET['orderby'];
$orderseq = $_GET['orderseq'] ? 1: 2;
$typeid = $_GET['typeid'];
$timerange = $_GET['timerange'];

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
	'display' => $_GET['display'],
	'timerange' => $_GET['timerange'],
	'orderby' => $_GET['orderby'],
	'orderseq' => $_GET['orderseq'],
	'typeid' => $_GET['typeid'],
);
$theurl = join_plugin_action2('index', $gets);
$multi = '';

$need_count = true;

if ($need_count) {
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
	
	$filejson = getFileList($perpage, $page, '', $typeid, $_G['fid'],'',$orderby,$orderseq,$uploadtimefrom, $uploadtimeto);

	$list = $resourses = array();
	if($filejson){
		$count = $filejson['totalAmount'];
		$list = $filejson['resources'];
	}
}

$filelist = array();

if ($count) {
	//-----------------取文档id 置顶，精华， 高亮--------------------------------
	$docidlist = $docmoderated = array();
	foreach($list as $key => $value){
		if($value['id'])
			$docidlist[] = $value['id'];
	}
	
	if($docidlist){
		$query = DB :: query("SELECT * FROM ".DB::table('group_doc')." WHERE docid IN (".dimplode($docidlist).")");
		while ($value = DB :: fetch($query)) {
			$docmoderated[$value['docid']] = $value;
		}
	}
	//------------------------------------------------------------------
	
	
	$_G['home_today'] = $_G['timestamp'] - $_G['timestamp']%(3600*24);
	
	include_once libfile('function/cache');
	
	$doctops = array();//保存置顶文档
	
	foreach($list as $key => $value){
		$value['uid'] = $value['userid'];
		if(ckdocfriend($value) && ckdocstatus($value)){
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
			
			$value['uploadtime'] = $value['uploadtime']/1000;
			if($value['dateline']>=$_G['home_today']) {
				$value['uploadtime'] = dgmdate($value['uploadtime'], 'H:i');
			} else {
				$value['uploadtime'] = dgmdate($value['uploadtime']);
			}
			
			if($docmoderated[$value['id']]){
				$value['displayorder'] = $docmoderated[$value['id']]['displayorder'];
				$value['highlight'] = parsehighlight($docmoderated[$value['id']]['highlight']);
				$value['digest'] = $docmoderated[$value['id']]['digest'];
				$value['moderated'] = $docmoderated[$value['id']]['moderated'];
			}else{
				$value['displayorder'] = 0;
			}
			if(preg_match("/^[A-Za-z0-9]+$/",$value[username])){
				$value[username]=user_get_user_name_by_username($value[username]);
			}
			if($value['displayorder'] > 0){
				$doctops[] = $value;
			}
			else{
				$filelist[] = $value;
			}
		}
		else{
			$pricount++;
		}
	}
	if(!empty($doctops))
		$filelist = array_merge($doctops, $filelist);
	$multi = multi($count, $perpage, $page, $theurl);
}

//分类
require_once libfile("function/category");
$pluginid = $_GET["plugin_name"];
$allowedittype = common_category_is_enable($_G['fid'], $pluginid);
$allowprefix = common_category_is_prefix($_G['fid'], $pluginid);
$allowrequired = common_category_is_required($_G['fid'], 'groupdoc');
$categorys = array();
if($allowedittype){
	$categorys = common_category_get_category($_G['fid'], $pluginid);
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

	if(empty($_G['uid'])) return $value['security'] == 1? true:false;
	if($value['uid'] == $_G['uid']) return true;

	$var = 'home_ckdocfriend_'.md5($value['uid'].'_'.$value['security']);
	if(isset($_G[$var])) return $_G[$var];

	$_G[$var] = false;
	switch ($value['security']) {
		case 1://全站
			$_G[$var] = true;
			break;
		case 4://凭密码
			$_G[$var] = true;
			break;
		case 16://专区可见
			$_G[$var] = $_G['ismember'] ? true : false;
			break;
		default:
			$_G[$var] = true;
	}
	return $_G[$var];
}
?>
