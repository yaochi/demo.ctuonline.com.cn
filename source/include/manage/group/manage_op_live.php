<?php

$title = "直播管理";
if($_G["gp_code"]=="true"){
     $_G[gp_usernames] = urldecode($_G[gp_usernames]);
	$_G[gp_usernames_uids] = urldecode($_G[gp_usernames_uids]);
    $_G[gp_content] = urldecode($_G[gp_content]);
    $_G[gp_starttime] = urldecode($_G[gp_starttime]);
    $_G[gp_endtime] = urldecode($_G[gp_endtime]);
}
if($_G["gp_method"]=="search"){
    $pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;
   /* require_once libfile("function/org");
    $org_id = get_org_id_by_user($_G[username]);
    $org_id = $org_id?$org_id:0;
    
    //统计
    $orgids[] = $org_id;
    $suborgids = get_sub_org_ids($org_id);
    if($suborgids){
        $orgids[] = $suborgids;
    }*/
    $sql = "SELECT #fileds# FROM ".DB::Table("group_live")." gl,".DB::Table("common_resource")." cr,"
        .DB::table("common_member")." cm,".DB::table("common_member_profile")." cmp"
        ." WHERE cm.uid=cmp.uid AND cm.uid=cr.uid AND gl.liveid=cr.rid AND cr.rtype='live' ";
    if(!empty($_G['validate']['orgidarray'])&&!in_array(9002,$_G['validate']['orgidarray'])&&!in_array(9001,$_G['validate']['orgidarray'])){
		$sql.=" AND cr.company_id IN(".dimplode($_G['validate']['orgidarray']).")";
	}  
    //用户工号
    if(!empty($_G[gp_usernames])){
        $query = DB::query("SELECT uid FROM ".DB::table("common_member")." WHERE uid='".$_G[gp_usernames_uids]."'");
        $uids[] = 0;
        while($row=DB::fetch($query)){
            $uids[] = $row[uid];
        }
        $sql = $sql . " AND cr.uid IN(".  implode(",", $uids).")";
    }
    //直播内容
    if(!empty($_G[gp_content])){
        $query = DB::query("SELECT liveid FROM ".DB::Table("group_live")." WHERE subject LIKE '%$_G[gp_content]%'");
        $liveids[] = 0;
        while($row=DB::fetch($query)){
            $liveids[] = $row[liveid];
        }
        $sql = $sql." AND cr.rid IN(".implode(",", $liveids).")";
    }
    if(!empty($_G[gp_starttime]) && !empty($_G[gp_endtime])){
        $starttime = strtotime($_G[gp_starttime]);
        $endtime = strtotime($_G[gp_endtime]);
        $sql = $sql." AND cr.dateline>$starttime AND cr.dateline<=$endtime";
    }
    $query = DB::query(str_replace("#fileds#", "COUNT(1) AS c", $sql));
    $count = DB::fetch($query);
    $count = $count["c"];
    if($count){
        //查找数据
        $query = DB::query(str_replace("#fileds#", "cr.rid, cr.dateline, cm.uid, cm.username, cmp.realname", $sql));
        $rids[] = 0;
        while($row=DB::fetch($query)){
            $rids[] = $row[rid];
            $rlist[$row[rid]] = $row;
        }
        $query = DB::query("SELECT liveid,subject,fid FROM ".DB::Table("group_live")." WHERE liveid IN(".implode(",", $rids).") ORDER BY liveid DESC LIMIT $start, $pagesize");
        while($row=DB::fetch($query)){
        	$forumquery=DB :: query("SELECT * FROM " . DB :: table('forum_forum') . " WHERE fid = '" . $row[fid] . "'");
        	$forum=DB::fetch($forumquery);
        	$row[forumname]=$forum[name];
        	$row[type]=$forum[type];
            $row[date] = dgmdate($rlist[$row[liveid]][dateline]);
            $livelist[] = $row;
        }
    }
    $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
    $url = "manage.php?mod=group&op=live&method=search&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);
}else if($_G[gp_method]=="delete"){
    //删除
  require_once libfile('function/group');
   if(!empty($_G[gp_ridarray])){
	   foreach($_G[gp_ridarray] as $rid){
	   		$query = DB :: query("SELECT * FROM " . DB :: table('group_live') . " WHERE liveid = '" . $rid . "'");
	   		$live=DB::fetch($query);
	   		$forumquery=DB :: query("SELECT * FROM " . DB :: table('forum_forum') . " WHERE fid = '" . $live[fid] . "'");
	   		$forum=DB::fetch($forumquery);
	   		DB :: query("DELETE FROM " . DB :: table('group_live') . " WHERE liveid = '" . $rid . "'");
	   		DB :: query("DELETE FROM " . DB :: table('home_feed') . " WHERE id = '" . $rid . "' AND idtype='liveid'");
	   		//通知
	   		
	   		notification_add($live['uid'], 'glive', 'live_delete', array('actor' => "<a href=\"home.php?mod=space&uid=$live[uid]\" target=\"_blank\">$live[username]</a>", 'subject' => $live['subject'], 'group' => "<a href=\"forum.php?mod=group&fid=$live[fid]\" target=\"_blank\">$forum[name]</a>"), 1);
	   	
	       hook_delete_resource($rid, "live");
	   }
   }
   cpmsg_mgr("操作成功", "manage.php?mod=group&op=live&method=search", "succeed");
}

include template("manage/group_live");

?>
