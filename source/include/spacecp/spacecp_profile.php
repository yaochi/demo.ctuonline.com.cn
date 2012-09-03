<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_profile.php 11537 2010-06-07 10:53:57Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$operation = in_array($_GET['op'], array('base', 'contact', 'edu', 'work', 'info', 'bbs', 'password','binding')) ? trim($_GET['op']) : 'base';
$space = getspace($_G['uid']);

//使用真实姓名 added by SK 2010-09-02
$space['username'] = user_get_user_name($_G['uid']);

space_merge($space, 'field_home');
space_merge($space, 'profile');
$seccodecheck = $_G['setting']['seccodestatus'] & 8;
$secqaacheck = $_G['setting']['secqaa']['status'] & 4;
$_G['group']['seccode'] = 1;

if($operation != 'password') {

	include_once libfile('function/profile');

	loadcache('profilesetting');
	if(empty($_G['cache']['profilesetting'])) {
		require_once libfile('function/cache');
		updatecache('profilesetting');
		loadcache('profilesetting');
	}
}

//alter by qiaoyz,2011-3-22,EKSN 193 个人资料处显示个人联系手机和邮箱，我的中心添加绑定信息，显示绑定的手机和邮箱。
if($operation == 'binding'){
	require_once dirname(dirname(dirname(__FILE__)))."/api/lt_org/user.php";
	$user=new User();
	$user_detail=$user->getUser($_G['username']);
	
	if($user_detail['mobile']=='null'||!$user_detail['mobile']){
		$space['bind_mobile'] = '未绑定';
	}else{
		$space['bind_mobile'] = $user_detail['mobile'];
	}
	
	if($user_detail['email']=='null'||!$user_detail['email']){
		$space['bind_email'] = '未绑定';
	}else{
		$space['bind_email'] = $user_detail['email'];
	}
	
	
}

$allowcstatus = !empty($_G['group']['allowcstatus']) ? true : false;

if(submitcheck('profilesubmit')) {

	require_once libfile('function/discuzcode');

	$forum = $setarr = $verifyarr = $errorarr = array();
	$forumfield = array('customstatus', 'sightml');

	if(!class_exists('discuz_censor')) {
		include libfile('class/censor');
	}
	$censor = discuz_censor::instance();

	foreach($_POST as $key => $value) {
		$field = $_G['cache']['profilesetting'][$key];
		if(in_array($key, $forumfield)) {
			$censor->check($value);
			if($censor->modbanned()) {
				profile_showerror($key, lang('spacecp', 'profile_censor'));
			}
			if($key == 'sightml') {
				loadcache(array('smilies', 'smileytypes'));
				$value = cutstr($value, $_G['group']['maxsigsize'], '');
				foreach($_G['cache']['smilies']['replacearray'] AS $skey => $smiley) {
					$_G['cache']['smilies']['replacearray'][$skey] = '[img]'.$_G['siteurl'].'static/image/smiley/'.$_G['cache']['smileytypes'][$_G['cache']['smilies']['typearray'][$skey]]['directory'].'/'.$smiley.'[/img]';
				}
				$value = preg_replace($_G['cache']['smilies']['searcharray'], $_G['cache']['smilies']['replacearray'], trim($value));
				$forum[$key] = addslashes(discuzcode(stripslashes($value), 1, 0, 0, 0, $_G['group']['allowsigbbcode'], $_G['group']['allowsigimgcode'], 0, 0, 1));
			} elseif($key=='customstatus' && $allowcstatus) {
				$forum[$key] = dhtmlspecialchars(trim($value));
			}
			continue;
		} elseif($field && !$field['available']) {
			continue;
		} elseif($key == 'timeoffset') {
			DB::update('common_member', array('timeoffset' => intval($value)), array('uid'=>$_G['uid']));
		}

		if(empty($field)) {
			continue;
		} elseif(profile_check($key, $value, $space)) {
			$censor->check($value);
			if($censor->modbanned()) {
				profile_showerror($key, lang('spacecp', 'profile_censor'));
			}
			$setarr[$key] = dhtmlspecialchars(trim($value));
		} else {
			if($key=='birthprovince') {
				$key = 'birthcity';
			} elseif($key=='resideprovince' || $key=='residecommunity'||$key=='residedistrict') {
				$key = 'residecity';
			} elseif($key=='birthyear' || $key=='birthmonth') {
				$key = 'birthday';
			}
			profile_showerror($key);
		}
		if(isset($setarr[$key]) && $_G['cache']['profilesetting'][$key]['needverify']) {
			$verifyarr[$key] = ($key == 'gender') ? lang('space', 'gender_'.intval($setarr[$key])) : $setarr[$key];
			unset($setarr[$key]);
		}
	}

	if($forum) {
		if(!$_G['group']['maxsigsize']) {
			$forum['sightml'] = '';
		}
		DB::update('common_member_field_forum', $forum, array('uid'=>$_G['uid']));
	}
	/*if(isset($_POST['nickname'])&& $space['nickname'] != $_POST['nickname']){
		$count=DB::result_first("select count(*) from ".DB::TABLE("common_member_profile")." where nickname='".$_POST['nickname']."' and uid!=$_G[uid]");
		$fcount=DB::result_first("select count(*) from ".DB::TABLE("forum_forum")." where name='".$_POST['nickname']."' and type='sub'");
		if($count || $fcount){
			profile_showerror('nickname','已存在');
		}else{
			$setarr['nickname']=$_POST['nickname'];
		}
	}*/

	if(isset($_POST['birthmonth']) && ($space['birthmonth'] != $_POST['birthmonth'] || $space['birthday'] != $_POST['birthday'])) {
		$setarr['constellation'] = get_constellation($_POST['birthmonth'], $_POST['birthday']);
	}
	if(isset($_POST['birthyear']) && $space['birthyear'] != $_POST['birthyear']) {
		$setarr['zodiac'] = get_zodiac($_POST['birthyear']);
	}
	$emailnew = dhtmlspecialchars($_G['gp_emailnew']);
	if($emailnew != $_G['member']['email'] && $emailnew) {
		$setarrs['email'] = $emailnew;
		DB::update('ucenter_members', $setarrs, array('uid'=>$_G['uid']));
		DB::update('common_member', $setarrs, array('uid'=>$_G['uid']));
	}
	if($setarr) {
		DB::update('common_member_profile', $setarr, array('uid'=>$_G['uid']));
	}
	if($verifyarr) {
		$inserts = array();
		foreach($verifyarr as $key=>$newvalue) {
			$fieldids[] = $key;
			$oldvalue = daddslashes($space[$key]);
			$inserts[] = "('$_G[uid]', '$_G[username]', '$key', '$oldvalue', '$newvalue', '$_G[timestamp]')";
		}
		DB::query('DELETE FROM '.DB::table('common_member_security')." WHERE uid='$_G[uid]' AND fieldid IN (".dimplode($fieldids).")");
		DB::query('INSERT INTO '.DB::table('common_member_security').'(`uid`, `username`, `fieldid`, `oldvalue`, `newvalue`, `dateline`) VALUES '.implode(', ', $inserts));
	}

	if(isset($_POST['privacy'])) {
		foreach($_POST['privacy'] as $key=>$value) {
			if(isset($_G['cache']['profilesetting'][$key])) {
				$space['privacy']['profile'][$key] = intval($value);
			}
		}
		DB::update('common_member_field_home', array('privacy'=>addslashes(serialize($space['privacy']))), array('uid'=>$space['uid']));
	}

	if($_G['setting']['my_app_status']) manyoulog('user', $_G['uid'], 'update');

	include_once libfile('function/feed');
	feed_add('profile', lang('spacecp', 'feed_profile_update_'.$operation), array('hash_data'=>'profile'));

	profile_showsuccess();

} elseif(submitcheck('passwordsubmit', 0, $seccodecheck, $secqaacheck)) {

	$membersql = $memberfieldsql = $authstradd1 = $authstradd2 = $newpasswdadd = '';
	$setarr = array();
	$emailnew = dhtmlspecialchars($_G['gp_emailnew']);

	if($_G['gp_questionidnew'] === '') {
		$_G['gp_questionidnew'] = $_G['gp_answernew'] = '';
	} else {
		$secquesnew = $_G['gp_questionidnew'] > 0 ? random(8) : '';
	}

	if(($_G['adminid'] == 1 || $_G['adminid'] == 2 || $_G['adminid'] == 3) && $_G['config']['admincp']['forcesecques']) {
		showmessage('profile_admin_security_invalid');
	}

	if(!empty($_G['gp_newpassword']) && $_G['gp_newpassword'] != $_G['gp_newpassword2']) {
		showmessage('profile_passwd_notmatch');
	}

	loaducenter();
	$ucresult = uc_user_edit($_G['username'], $_G['gp_oldpassword'], $_G['gp_newpassword'], $emailnew, 0, $_G['gp_questionidnew'], $_G['gp_answernew']);
	if($ucresult == -1) {
		showmessage('profile_passwd_wrong');
	} elseif($ucresult == -4) {
		showmessage('profile_email_illegal');
	} elseif($ucresult == -5) {
		showmessage('profile_email_domain_illegal');
	} elseif($ucresult == -6) {
		showmessage('profile_email_duplicate');
	}

	if($emailnew != $_G['member']['email']) {
		$setarr['email'] = $emailnew;
	}
	if(!empty($_G['gp_newpassword']) || $secquesnew) {
		$setarr['password'] = md5(random(10));
	}

	$authstr = false;
	if($_G['setting']['regverify'] == 1 && $_G['adminid'] == 0 && $emailnew != $_G['member']['email'] && (($_G['group']['grouptype'] == 'member' && $_G['adminid'] == 0) || $_G['groupid'] == 8)) {
		$idstring = random(6);
		$setarr['groupid'] = $groupid = 8;
		loadcache('usergroup_8');

		$setarr['emailstatus'] = 0;
		$authstr = true;
		DB::update('common_member_field_forum', array('authstr' => TIMESTAMP."\t2\t".$idstring), array('uid' => $_G['uid']));
		$email_verify_message = lang('email', 'email_verify_message', array(
			'username' => $_G['member']['username'],
			'bbname' => $_G['setting']['bbname'],
			'uid' => $_G['uid'],
			'siteurl' => $_G['siteurl'],
			'idstring' => $idstring,
		));
		include_once libfile('function/mail');
		sendmail("{$_G[member][username]} <$emailnew>", lang('email', 'email_verify_subject'), $email_verify_message);
	}

	if($setarr) {
		DB::update('common_member', $setarr, array('uid' => $_G['uid']));
	}

	if($authstr) {
		showmessage('profile_email_verify');
	} else {
		showmessage('profile_succeed', 'home.php?mod=spacecp&ac=profile&op=password');
	}
}

if($operation == 'password') {
    //add by qiaoyz,2011-3-7,add修改密码 URL
    $updatepasswordurl=$_G['config']['ctuonline']['url'];
	$resend = getcookie('resendemail');
	$resend = empty($resend) ? true : (TIMESTAMP - $resend) > 300;
	if($_G['gp_resend'] && $resend) {
		$toemail = $space['newemail'] ? $space['newemail'] : $space['email'];
		emailcheck_send($space['uid'], $toemail);
		dsetcookie('resendemail', TIMESTAMP);
		showmessage('send_activate_mail_succeed', "home.php?mod=spacecp&ac=profile&op=password");
	}

	$actives = array('password' =>' class="a"');

} else {

	space_merge($space, 'field_home');
	space_merge($space, 'field_forum');

	require_once libfile('function/editor');
	$space['sightml'] = html2bbcode($space['sightml']);

	$privacy = $space['privacy']['profile'] ? $space['privacy']['profile'] : array();
	$_G['setting']['privacy'] = $_G['setting']['privacy'] ? $_G['setting']['privacy'] : array();
	$_G['setting']['privacy'] = is_array($_G['setting']['privacy']) ? $_G['setting']['privacy'] : unserialize($_G['setting']['privacy']);
	$_G['setting']['privacy']['profile'] = !empty($_G['setting']['privacy']['profile']) ? $_G['setting']['privacy']['profile'] : array();
	$privacy = array_merge($_G['setting']['privacy']['profile'], $privacy);

	$actives = array('profile' =>' class="a"');
	$opactives = array($operation =>' class="a"');
	$allowitems = array();
	if($operation == 'base') {
		$allowitems = array('realname', 'gender', 'birthday', 'field1', 'field2', 'field3', 'field4', 'field5', 'field6', 'field7', 'field8');
	} elseif($operation == 'contact') {
		$allowitems = array('telephone', 'mobile');
	} elseif($operation == 'edu') {
		$allowitems = array('graduateschool', 'education');
	} elseif($operation == 'work') {
		$allowitems = array('occupation', 'company', 'position', 'revenue');
	} elseif($operation == 'info') {
		$allowitems = array('address', 'zipcode', 'nationality', 'residecommunity', 'residesuite', 'height', 'weight', 'site', 'bio', 'interest');
	}
    
	$htmls = $settings = array();
	foreach($allowitems as $fieldid) {
		$html = profile_setting($fieldid, $space, true);
		if($html) {
			$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
			$htmls[$fieldid] = $html;
		}
	}
}

include template("home/spacecp_profile");

function get_constellation($birthmonth,$birthday) {
	$birthmonth = intval($birthmonth);
	$birthday = intval($birthday);
	$idx = $birthmonth;
	if ($birthday <= 22) {
		if (1 == $birthmonth) {
			$idx = 12;
		} else {
			$idx = $birthmonth - 1;
		}
	}
	return $idx > 0 && $idx <= 12 ? lang('space', 'constellation_'.$idx) : '';
}

function get_zodiac($birthyear) {
	$birthyear = intval($birthyear);
	$idx = (($birthyear - 1900) % 12) + 1;
	return $idx > 0 && $idx <= 12 ? lang('space', 'zodiac_'. $idx) : '';
}

function profile_showerror($key, $extrainfo) {
	echo '<script>';
	echo 'parent.show_error("'.$key.'", "'.$extrainfo.'");';
	echo '</script>';
	exit();
}

function profile_showsuccess() {
	echo '<script type="text/javascript">';
	echo 'parent.show_success();';
	echo '</script>';
	exit();
}

?>