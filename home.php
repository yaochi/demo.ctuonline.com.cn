<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home.php 7813 2010-04-13 10:34:03Z wangjinbo $
 */

define('APPTYPEID', 1);
define('CURSCRIPT', 'home');

require_once './source/class/class_core.php';
require_once './source/function/function_home.php';

$discuz = & discuz_core::instance();

$cachelist = array('magic','userapp','usergroups', 'blockclass');
$discuz->cachelist = $cachelist;
$discuz->init();

// 增加外部专家访问权限限制
// Add by lujianqing 2010-10-08
/*if($discuz->var['cookie']['expert_Is']){
    // 判断专区和专区所属活动的访问权限s
	if($_G['gp_ac']=='share'||$_G['gp_ac']=='favorite'){
		$url = $discuz->var['cookie']['expert_url'];
	    $message = "非常抱歉，由于您为外部专家，暂无权访问该内容";
	    showmessage($message,$url);	       
	}
}
*/
if(isset($discuz->var['cookie']['expert_Is']) && $_G['gp_ac'] != 'comment' && $_GET['op'] != 'comment' && $_G['gp_ac'] != 'click'){
    $url = $discuz->var['cookie']['expert_url'];    
    $message = "非常抱歉，由于您为外部专家，暂无权访问该内容";
    showmessage($message,$url); 
}


$space = array();

$mod = getgpc('mod');
if(!in_array($mod, array('space', 'spacecp', 'misc', 'magic', 'editor', 'userapp', 'invite', 'task', 'medal','ajax','attachment','learnattachment','pro'))) {
	$mod = 'space';
	$_GET['do'] = 'home';
}

define('CURMODULE', $mod);
runhooks();
$ac = empty($_G['gp_ac']) ? '' : $_G['gp_ac'];

if($mod!="misc" && $ac!="swfupload"){
    include_once DISCUZ_ROOT."/source/include/misc/misc_login.php";
}

if($_G[member][doindex]){
}else{
	dheader("Location: member.php?mod=viewdirection");
}

if($op=='editlink')
{
	require_once libfile('home/'.$mod, 'module');
	
 }else{
require_once libfile('home/'.$mod, 'module');
}

?>