<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_profile.php 11410 2010-06-02 02:07:42Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/spacecp');

space_merge($space, 'count');
space_merge($space, 'field_home');
space_merge($space, 'field_forum');
space_merge($space, 'profile');
space_merge($space, 'status');

if($space['videophoto'] && ckvideophoto('viewphoto', $space, 1)) {
	$space['videophoto'] = getvideophoto($space['videophoto']);
} else {
	$space['videophoto'] = '';
}

$space['admingroup'] = $_G['cache']['usergroups'][$space['adminid']];
$space['admingroup']['icon'] = g_icon($space['adminid'], 1);

$space['group'] = $_G['cache']['usergroups'][$space['groupid']];
$space['group']['icon'] = g_icon($space['groupid'], 1);

if($space['extgroupids']) {
	$newgroup = array();
	$e_ids = explode(',', $space['extgroupids']);
	foreach ($e_ids as $e_id) {
		$newgroup[] = $_G['usergroups'][$e_id]['grouptitle'];
	}
	$space['extgroupids'] = implode(',', $newgroup);
}

$space['regdate'] = dgmdate($space['regdate']);
if($space['lastvisit']) $space['lastvisit'] = dgmdate($space['lastvisit']);
if($space['lastactivity']) $space['lastactivity'] = dgmdate($space['lastactivity']);
if($space['lastpost']) $space['lastpost'] = dgmdate($space['lastpost']);
if($space['lastsendmail']) $space['lastsendmail'] = dgmdate($space['lastsendmail']);
if($space['lastsendmail']) $space['groupexpiry'] = dgmdate($space['groupexpiry']);

if($_G['uid'] == $space['uid'] || $_G['group']['allowviewip']) {
	require_once libfile('function/misc');
	$space['regip_loc'] = convertip($space['regip']);
	$space['lastip_loc'] = convertip($space['lastip']);
}

$space['buyerrank'] = 0;
if($space['buyercredit']){
	foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
		if($space['buyercredit'] <= $credit) {
			$space['buyerrank'] = $level;
			break;
		}
	}
}

$space['sellerrank'] = 0;
if($space['sellercredit']){
	foreach($_G['setting']['ec_credit']['rank'] AS $level => $credit) {
		if($space['sellercredit'] <= $credit) {
			$space['sellerrank'] = $level;
			break;
		}
	}
}

$space['attachsize'] = formatsize($space['attachsize']);

$space['timeoffset'] = empty($space['timeoffset']) ? '9999' : $space['timeoffset'];

require_once libfile('function/friend');
$isfriend = friend_check($space['uid']);

loadcache('profilesetting');
include_once libfile('function/profile');
include_once libfile('function/org');
$profiles = array();
$privacy = $space['privacy']['profile'] ? $space['privacy']['profile'] : array();

//alter by qiaoyz,2011-3-22,EKSN 193 个人资料处显示个人联系手机和邮箱，我的中心添加绑定信息，显示绑定的手机和邮箱。
$query = DB::query("SELECT privacy FROM ".DB::table("common_member_field_home")." WHERE uid=".$space['uid']);
$result = DB::fetch($query);
$privacy = unserialize($result['privacy']);
$privacy_mobile=$privacy['profile']['mobile'];
$query = DB::query("SELECT mobile FROM ".DB::table("common_member_profile")." WHERE uid=".$space['uid']);
$result = DB::fetch($query);
$mobile_temp=$result['mobile'];
if($_G['uid']==$space['uid']) $mobile=$mobile_temp;
else {
if($privacy_mobile==3) $mobile="保密";
include_once libfile('function/friend');
if($privacy_mobile==2&&!isfriend($space['uid'],$_G['uid'])) $mobile="保密";
if(($privacy_mobile==2&&isfriend($space['uid'],$_G['uid'])) or $privacy_mobile==1) $mobile=$mobile_temp;
else $mobile="保密";
}
$query = DB::query("SELECT username,email FROM ".DB::table("common_member")." WHERE uid=".$space['uid']);
$result = DB::fetch($query);
$email=$result['email'];
$company = getOrgNameByUser($result['username']);
$station = getStationByUser($space['uid']);

foreach($_G['cache']['profilesetting'] as $fieldid=>$field) {
	if($field['available'] && $field['invisible'] != '1'
		&& ($space['self'] || empty($privacy[$fieldid]) || ($isfriend && $privacy[$fieldid] == 1))
		&& strlen($space[$fieldid]) > 0) {

		$val = profile_show($fieldid, $space);
		if($val !== false) {
			if ($val == '')  $val = '&nbsp;';
			$profiles[$fieldid] = array('title'=>$field['title'], 'value'=>$val);
			$profiles['company'] = array('title'=>'工作单位', 'value'=>$company);
			$profiles['station'] = array('title'=>'岗位', 'value'=>$station);
			$profiles['mobile'] = array('title'=>'手机', 'value'=>$mobile);
			$profiles['email'] = array('title'=>'邮箱', 'value'=>$email);
		}
	}
}
dsetcookie('home_diymode', 1);

include_once template("home/space_profile");

?>