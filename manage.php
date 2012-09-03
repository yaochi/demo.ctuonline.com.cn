<?php
define('APPTYPEID', 3);
define('CURSCRIPT', 'manage');


require './source/class/class_core.php';

$discuz=&discuz_core::instance();

$discuz->init();
$mod = $_G[gp_mod]?$_G[gp_mod]:"index";
$_G[gp_mod]=$mod;

/*echo '用户id$_G[uid]'.$_G['uid']."<br/>";
$_G['uid']=$_G['uid']?$_G['uid']:$_G['gp_uid'];*/


include_once DISCUZ_ROOT."/source/include/misc/misc_login.php";
include_once DISCUZ_ROOT."/source/include/misc/misc_validate.php";    

require DISCUZ_ROOT.'./source/module/manage/manage_'.$mod.'.php';

?>
