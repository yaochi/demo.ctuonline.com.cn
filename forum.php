<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum.php 9195 2010-04-27 08:47:03Z monkey $
 */

define('APPTYPEID', 2);
define('CURSCRIPT', 'forum');


require './source/class/class_core.php';
require './source/function/function_forum.php';

$discuz = & discuz_core::instance();

$modarray = array('ajax','announcement','attachment','forumdisplay',
'group','image','index','medal','misc','modcp','notice','post','redirect',
'relatekw','relatethread','rss','search','topicadmin','trade','viewthread','activity'
);

$modcachelist = array(
'index'		=> array('announcements', 'onlinelist', 'forumlinks', 'advs_index',
'heats', 'historyposts', 'onlinerecord', 'blockclass', 'userstats'),
'forumdisplay'	=> array('smilies', 'announcements_forum', 'globalstick', 'forums',
'icons', 'onlinelist', 'forumstick', 'blockclass',
'threadtable_info', 'threadtableids'),
'viewthread'	=> array('smilies', 'smileytypes', 'forums', 'usergroups', 'ranks',
'stamps', 'bbcodes', 'smilies',
'custominfo', 'groupicon', 'stamps', 'threadtableids', 'threadtable_info'),
'post'		=> array('bbcodes_display', 'bbcodes', 'smileycodes', 'smilies', 'smileytypes',
'icons', 'domainwhitelist'),
'space'		=> array('fields_required', 'fields_optional', 'custominfo'),
'group'		=> array('grouptype'),
);

$mod = !in_array($discuz->var['mod'], $modarray) ? 'index' : $discuz->var['mod'];

define('CURMODULE', $mod != 'redirect' ? $mod : 'viewthread');
$cachelist = array();
if(isset($modcachelist[CURMODULE])) {
	$cachelist = $modcachelist[CURMODULE];
}

$discuz->cachelist = $cachelist;
$discuz->init();


// 增加外部专家访问权限限制
// Add by lujianqing 2010-10-08
if($discuz->var['cookie']['expert_Is']){
    // 判断专区和专区所属活动的访问权限s
	if($_G['gp_ac']=='share'||$_G['gp_ac']=='favorite'){
		$url = $discuz->var['cookie']['expert_url'];
	    $message = "非常抱歉，由于您为外部专家，暂无权访问该内容";
	    showmessage($message,$url);	       
	}
    if($_G['gp_fid'] != $discuz->var['cookie']['expert_fid']  && $_GET['mod'] != 'viewthread' && $mod != 'post'){
    	//判断是否为活动，若是，则继续判断活动所在专区是否为专家所在专区
    	$activity = get_uid_by_fid($_G['gp_fid']);
    	if ($activity['fup'] != $discuz->var['cookie']['expert_fid']  && $_GET['mod'] != 'viewthread' && $mod != 'post' && $_G['gp_action']!='recommend') {
	        // 外部专家允许进入到专区fid
	        //$fid_allow = $discuz->var['cookie']['expert_fid'];
	        $url = $discuz->var['cookie']['expert_url'];
	        $message = "非常抱歉，由于您为外部专家，暂无权访问该内容";
	        showmessage($message,$url);	        
    	}
    }
}


loadforum();
set_rssauth();
runhooks();
if($_G["gp_plugin_name"]!="groupalbum2"){
    include_once DISCUZ_ROOT."/source/include/misc/misc_login.php";
}

require DISCUZ_ROOT.'./source/module/forum/forum_'.$mod.'.php';

?>