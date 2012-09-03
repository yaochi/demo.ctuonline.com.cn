<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

function index(){
    global $_G;
    $url = "forum.php?mod=forumdisplay&action=list&fid=".$_G["gp_fid"]."&special=0&plugin_name=topic&plugin_op=groupmenu";
    header("Location:".$url);
}

function recommend(){
    global $_G;
    $tid=$_G[gp_tid];
    $data=1;
    $thread = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid='$tid'");
    if($_G[uid] == $thread[authorid]) $data = 0;
    else{
        $history=DB::fetch_first("SELECT * FROM ".DB::table('forum_memberrecommend')." WHERE tid='$tid' AND recommenduid='$_G[uid]'");
        if(!$history){
    
    DB::query("UPDATE ".DB::table('forum_thread')." SET heats=heats+'".(abs($_G['group']['allowrecommend']) * $_G['heatthread']['recommend'])."', recommends=recommends+'{$_G[group][allowrecommend]}', recommend_add=recommend_add+1 WHERE tid='$_G[tid]'");
	DB::query("INSERT INTO ".DB::table('forum_memberrecommend')." (tid, recommenduid, dateline) VALUES ('$_G[tid]', '$_G[uid]', '$_G[timestamp]')"); 
    $user=DB::fetch_first("SELECT * FROM ".DB::table('common_member_profile')." WHERE uid='$_G[uid]'");
    $content='[话题]“'.$user[realname].'”对您“'.$_G[forum][name].'”专区的“'.$thread[subject].'”的话题进行了支持表态';
    notification_add($thread[authorid],"thread",$content, array(), 1);
    }else $data = 2;
    }
    $arraydata=array("s"=>$data,"tid"=>$tid);
    $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";
	
	exit();	
}
?>
