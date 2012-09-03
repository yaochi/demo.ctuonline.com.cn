<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: group.php 9822 2010-05-05 04:10:32Z wangjinbo $
 */

define('APPTYPEID', 3);
define('CURSCRIPT', 'group');


require './source/class/class_core.php';

$discuz = & discuz_core::instance();

$cachelist = array('grouptype', 'groupindex', 'blockclass');
$discuz->cachelist = $cachelist;
$discuz->init();

// 增加外部专家访问权限限制
// Add by lujianqing 2010-10-08
if(isset($discuz->var['cookie']['expert_Is'])){
    $url = $discuz->var['cookie']['expert_url'];
    $message = "非常抱歉，由于您为外部专家，暂无权访问该内容";
    showmessage($message,$url);
}

if(!$_G['setting']['groupstatus']) {
	showmessage('group_status_off');
}
$modarray = array('index','tag','invite','more','label', 'tags','other');
$mod = !in_array($_G['mod'], $modarray) ? 'index' : $_G['mod'];

define('CURMODULE', $mod);

runhooks();
$metadescription = strip_tags($_G['setting']['group_description']);
$metakeywords = $_G['setting']['group_keywords'];
include_once DISCUZ_ROOT."/source/include/misc/misc_login.php";

require DISCUZ_ROOT.'./source/module/group/group_'.$mod.'.php';
?>