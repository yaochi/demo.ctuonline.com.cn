<?php
define('APPTYPEID', 3);

require './source/class/class_core.php';

$discuz = & discuz_core :: instance();

$discuz->init();

// 增加外部专家访问权限限制
// Add by lujianqing 2010-10-08
if(isset($discuz->var['cookie']['expert_Is'])){
    $url = $discuz->var['cookie']['expert_url'];
    $message = "非常抱歉，由于您为外部专家，暂无权访问该内容";
    showmessage($message,$url);
}

include_once DISCUZ_ROOT."/source/include/misc/misc_login.php";

if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}

include_once libfile("function/core");
include_once libfile("function/move");

$mod = $_G[gp_mod]?$_G[gp_mod]:"index";
$_G[gp_mod]=$mod;


require DISCUZ_ROOT.'./source/module/mobileapp/mobile_'.$mod.'.php';

?>
