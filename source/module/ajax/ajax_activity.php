<?php

$ac = $_G["gp_m"];
if($ac=="sorce"){
	$fid=$_G["gp_fid"];
    $key = ($_G[uid]."_".$fid);
    if($_COOKIE["activity_vote_uid"]!=$key){
        $cur_content_sorce = $_G["gp_cur_content_sorce"];
        $cur_teacher_sorce = $_G["gp_cur_teacher_sorce"];
        DB::query("UPDATE ".DB::table("forum_forum_activity")." SET teacher_num=teacher_num+$cur_teacher_sorce,
                teacher_count=teacher_count+1, live_num=live_num+$cur_content_sorce,
                live_count=live_count+1 WHERE fid=".$fid);
        DB::query("UPDATE ".DB::table("forum_forum_activity")." SET average=(teacher_num+live_num)/(teacher_count+live_count) WHERE fid=".$fid);
        setcookie("activity_vote_uid", $key, time()+86400);
        
        //通知
        require_once libfile('function/group');
        $ainfo = get_uid_by_fid($fid);
        $groupname = get_groupname_by_fid($ainfo[fup]);
        notification_add($ainfo['founderuid'], "zq_event", '[活动]“{actor}”对您“{groupname}”的专区的“{activityname}”的活动进行了打分，讲师{cur_teacher_sorce}分，课程内容{cur_content_sorce}分，快去看看吧', array('groupname' => '<a href="forum.php?mod=group&fid='.$ainfo['fup'].'">'.$groupname.'</a>', 'activityname'=>'<a href="forum.php?mod=activity&fid='.$ainfo['fid'].'">'.$ainfo['name'].'</a>', 'cur_teacher_sorce'=>$cur_teacher_sorce, 'cur_content_sorce'=>$cur_content_sorce), 0);
        
        $msg = 'yes';
    }else{
        $msg = "no";
    }
    header("content-type: text/xml");
    echo '<?xml version="1.0" encoding="utf-8"?><root>'.$msg.'</root>';
}
?>
