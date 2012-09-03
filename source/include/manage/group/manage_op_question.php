<?php

$title = "问卷管理";
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
    $sql = "SELECT #fileds# FROM ".DB::Table("questionary")." q,".DB::Table("common_resource")." cr,"
        .DB::table("common_member")." cm,".DB::table("common_member_profile")." cmp"
        ." WHERE cm.uid=cmp.uid AND cm.uid=cr.uid AND q.questid=cr.rid AND cr.rtype='question'";
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
    //名称
    if(!empty($_G[gp_content])){
        $query = DB::query("SELECT questid FROM ".DB::Table("questionary")." WHERE questname LIKE '%$_G[gp_content]%'");
        $questids[] = 0;
        while($row=DB::fetch($query)){
            $questids[] = $row[questid];
        }
        $sql = $sql." AND cr.rid IN(".implode(",", $questids).")";
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
        $query = DB::query("SELECT questid,questname,fid FROM ".DB::Table("questionary")." WHERE questid IN(".implode(",", $rids).") ORDER BY dateline DESC LIMIT $start, $pagesize");
        while($row=DB::fetch($query)){
        	$forumquery=DB :: query("SELECT * FROM " . DB :: table('forum_forum') . " WHERE fid = '" . $row[fid] . "'");
        	$forum=DB::fetch($forumquery);
        	$row[forumname]=$forum[name];
        	$row[type]=$forum[type];
            $row[date] = dgmdate($rlist[$row[questid]][dateline]);
            $questlist[] = $row;
        }
    }
   $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
    $url = "manage.php?mod=group&op=question&method=search&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);
}else if($_G[gp_method]=="delete"){
    //删除
  require_once libfile('function/group');
   if(!empty($_G[gp_ridarray])){
	   foreach($_G[gp_ridarray] as $key => $rid){
	   		$query = DB :: query("SELECT * FROM " . DB :: table('questionary') . " WHERE questid = '" . $rid . "'");
	   		$questionary=DB::fetch($query);
	   		$forumquery=DB :: query("SELECT * FROM " . DB :: table('forum_forum') . " WHERE fid = '" . $questionary[fid] . "'");
	   		$forum=DB::fetch($forumquery);
			DB :: query("DELETE FROM " . DB :: table('questionary') . " WHERE questid='$rid'");
			DB :: query("DELETE FROM " . DB :: table('questionary_question') . " WHERE questid='$rid'");
			DB :: query("DELETE FROM " . DB :: table('questionary_questionoption') . " WHERE questid='$rid'");
			DB :: query("DELETE FROM " . DB :: table('questionary_questionchoicers') . " WHERE questid='$rid'");
			hook_delete_resource($rid,'question');
			notification_add($questionary[uid],"zq_question",'[问卷]您“'.$forum[name].'”的专区的“'.$questionary[questname].'”的资源被['.user_get_user_name_by_username($_G[username]).']删除了', array(), 1);
	   }
   }
   cpmsg_mgr("操作成功", "manage.php?mod=group&op=question&method=search", "succeed");
}

include template("manage/group_questionary");

?>
