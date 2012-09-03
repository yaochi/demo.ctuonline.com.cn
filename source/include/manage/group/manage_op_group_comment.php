<?php
$title = "群组评论管理";
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
    $sql = "SELECT #fileds# FROM ".DB::Table("common_resource")." cr,"
        .DB::table("common_member")." cm,".DB::table("common_member_profile")." cmp"
        ." WHERE cm.uid=cmp.uid AND cm.uid=cr.uid AND cr.fid!='null' ";
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
    //内容
    if(!empty($_G[gp_content])){
        $query = DB::query("SELECT pid FROM ".DB::Table("forum_post")." WHERE message LIKE '%$_G[gp_content]%'");
        $pcommentids[] = 0;
        while($row=DB::fetch($query)){
            $pcommentids[] = $row[pid];
        }
        $sql = $sql." AND (( cr.rid IN(".implode(",", $pcommentids).") AND cr.rtype='group_pcomment' )";
        $homequery=DB::query("SELECT cid FROM ".DB::Table("home_comment")." WHERE message LIKE '%$_G[gp_content]%'");
        $hcommentids[] = 0;
     	while($row=DB::fetch($homequery)){
            $hcommentids[] = $row[cid];
        }
        $sql = $sql." OR ( cr.rid IN(".implode(",", $hcommentids).") AND cr.rtype='group_hcomment' )";
        $pquery=DB::query("SELECT id FROM ".DB::Table("forum_postcomment")." WHERE comment LIKE '%$_G[gp_content]%'");
        $ppcommentids[] = 0;
     	while($row=DB::fetch($pquery)){
            $ppcommentids[] = $row[id];
        }
        $sql = $sql." OR ( cr.rid IN(".implode(",", $ppcommentids).") AND cr.rtype='group_ppcomment' ))";
    }else{
    	 $sql = $sql." AND (cr.rtype='group_pcomment' OR cr.rtype='group_hcomment' OR cr.rtype='group_ppcomment')";
    }
    if(!empty($_G[gp_starttime]) && !empty($_G[gp_endtime])){
        $starttime = strtotime($_G[gp_starttime]);
        $endtime = strtotime($_G[gp_endtime]);
        $sql = $sql." AND cr.dateline>$starttime AND cr.dateline<=$endtime";
    }
    $query = DB::query(str_replace("#fileds#", "COUNT(1) AS c", $sql));
    $count = DB::fetch($query);
    $count = $count["c"];
    require_once libfile('function/post');
    require_once libfile('function/discuzcode');
    if($count){
        //查找数据
        $query = DB::query(str_replace("#fileds#", "cr.rid, cr.dateline,cr.rtype, cm.uid, cm.username, cmp.realname", $sql)." ORDER BY dateline DESC LIMIT $start, $pagesize");
        $rids[] = 0;
        while($row=DB::fetch($query)){
            $rids[] = $row[rid];
            if($row[rtype]=="group_pcomment"){
            	 $pquery = DB::query("SELECT p.pid,p.message,p.tid,p.fid,t.special FROM ".DB::Table("forum_post")." p LEFT JOIN ".DB::Table("forum_thread")." t USING(tid) WHERE pid ='". $row[rid] ."'");
		             while($rowin=DB::fetch($pquery)){
			        	$forumquery=DB :: query("SELECT * FROM " . DB :: table('forum_forum') . " WHERE fid = '" . $rowin[fid] . "'");
			        	$forum=DB::fetch($forumquery);
			        	$rowin[forumname]=$forum[name];
			        	$rowin[type]=$forum[type];
			            $rowin[date] = dgmdate($row[dateline]);
			            $rowin[uid]=$row[uid];
			            if($rowin[special]=='0'){
			            	$rowin[idtype]='topic';
			            }else if($rowin[special]=='1'){
			            	$rowin[idtype]='poll';
			            }else if($rowin[special]=='3'){
			            	$rowin[idtype]='qbar';
			            }
			            $rowin[rid]=$row[rid];
			            $rowin[rtype]=$row[rtype];
			            $rowin[realname]=$row[realname];
			            $rowin[message]=discuzcode(cutstr($rowin[message],50));
			            $commentlist[] = $rowin;
			        }
            }
            if($row[rtype]=="group_hcomment"){
            	 $hquery = DB::query("SELECT cid,message,id,idtype FROM ".DB::Table("home_comment")." WHERE cid ='". $row[rid] ."'");
	             while($rowin=DB::fetch($hquery)){
		        	if($rowin[idtype]=='docid'){//知识中心
		        		$queryin=DB::query("SELECT * FROM ".DB::Table("resourcelist")." WHERE resourceid ='". $rowin[id] ."'");
		        		$frow=DB::fetch($queryin);
		        		if(!$frow){
		        			$queryin=DB::query("SELECT * FROM ".DB::Table("group_doc")." WHERE docid ='". $rowin[id] ."'");
		        			$frow=DB::fetch($queryin);
		        		}
		        			$forumquery=DB :: query("SELECT * FROM " . DB :: table('forum_forum') . " WHERE fid = '" . $frow[fid] . "'");
				        	$forum=DB::fetch($forumquery);
				        	$rowin[forumname]=$forum[name];
				        	$rowin[type]=$forum[type];
				            $rowin[date] = dgmdate($row[dateline]);
				            $rowin[uid]=$row[uid];
				           	$rowin[idtype]=$frow[type];
				           	$rowin[rid]=$row[rid];
				           	$rowin[rtype]=$row[rtype];
				           	$rowin[url]=$frow[titlelink];
				           	$rowin[fid]=$frow[fid];
			            	$rowin[realname]=$row[realname];
			            	$rowin[message]=discuzcode(cutstr($rowin[message],50));
				            $commentlist[] = $rowin;
		        	}else if($rowin[idtype]=='lecturerid'){//讲师
		        		$queryin=DB::query("SELECT * FROM ".DB::Table("lecturer")." WHERE id ='". $rowin[id] ."'");
		        		while($frow=DB::fetch($queryin)){
		        			$forumquery=DB :: query("SELECT * FROM " . DB :: table('forum_forum') . " WHERE fid = '" . $frow[fid] . "'");
				        	$forum=DB::fetch($forumquery);
				        	$rowin[forumname]=$forum[name];
				        	$rowin[type]=$forum[type];
				            $rowin[date] = dgmdate($row[dateline]);
				            $rowin[uid]=$row[uid];
				            $rowin[fid]=$forum[fid];
				            $rowin[rid]=$row[rid];
				            $rowin[rtype]=$row[rtype];
			           		$rowin[realname]=$row[realname];
			           		$rowin[message]=discuzcode(cutstr($rowin[message],50));
				            $commentlist[] = $rowin;
		        		}
		        	}else if($rowin[idtype]=='noticeid'){//通知
		        		$queryin=DB::query("SELECT * FROM ".DB::Table("notice")." WHERE id ='". $rowin[id] ."'");
		        		while($frow=DB::fetch($queryin)){
		        			$forumquery=DB :: query("SELECT * FROM " . DB :: table('forum_forum') . " WHERE fid = '" . $frow[group_id] . "'");
				        	$forum=DB::fetch($forumquery);
				        	$rowin[forumname]=$forum[name];
				        	$rowin[type]=$forum[type];
				            $rowin[date] = dgmdate($row[dateline]);
				            $rowin[uid]=$row[uid];
				            $rowin[rid]=$row[rid];
				            $rowin[fid]=$forum[fid];
				            $rowin[rtype]=$row[rtype];
			            	$rowin[realname]=$row[realname];
			            	$rowin[message]=discuzcode(cutstr($rowin[message],50));
				            $commentlist[] = $rowin;
		        		}
		        	}else if($rowin[idtype]=='gpicid'){//图片
		        		$queryin=DB::query("SELECT p.picid, p.albumid,p.filepath,a.albumname,a.fid FROM ".DB::Table("group_pic")." p LEFT JOIN ".DB::Table("group_album")." a USING(albumid) WHERE picid ='". $rowin[id] ."'");
		        		while($frow=DB::fetch($queryin)){
		        			$forumquery=DB :: query("SELECT * FROM " . DB :: table('forum_forum') . " WHERE fid = '" . $frow[fid] . "'");
				        	$forum=DB::fetch($forumquery);
				        	$rowin[forumname]=$forum[name];
				        	$rowin[type]=$forum[type];
				            $rowin[date] = dgmdate($row[dateline]);
				            $rowin[uid]=$row[uid];
				            $rowin[fid]=$forum[fid];
				            $rowin[rid]=$row[rid];
				            $rowin[rtype]=$row[rtype];
				            $rowin[message]=discuzcode(cutstr($rowin[message],50));
			            	$rowin[realname]=$row[realname];
				            $commentlist[] = $rowin;
		        		}
		        	}else{//
		        		
		        	}
			    }
            }
            if($row[rtype]=="group_ppcomment"){
            	 $pquery = DB::query("SELECT pp.id,pp.comment,pp.pid,pp.tid,p.fid FROM ".DB::Table("forum_postcomment")." pp LEFT JOIN ".DB::Table("forum_post")." p USING(pid) WHERE id ='". $row[rid] ."'");
	             while($rowin=DB::fetch($pquery)){
			        	$forumquery=DB :: query("SELECT * FROM " . DB :: table('forum_forum') . " WHERE fid = '" . $rowin[fid] . "'");
			        	$forum=DB::fetch($forumquery);
			        	$rowin[forumname]=$forum[name];
			        	$rowin[type]=$forum[type];
			            $rowin[date] = dgmdate($row[dateline]);
			            $rowin[idtype]='comment';
			            $rowin[uid]=$row[uid];
			            $rowin[rid]=$row[rid];
			            $rowin[rtype]=$row[rtype];
			            $rowin[message]=discuzcode(cutstr($rowin[comment],50));
			            $rowin[realname]=$row[realname];
			            $commentlist[] = $rowin;
			        }	 
            } 
        }
    }
   $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
    $url = "manage.php?mod=group&op=group_comment&method=search&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);
}else if($_G[gp_method]=="delete"){
    //删除
  require_once libfile('function/group');
  require_once libfile("function/credit");
   if(!empty($_G[gp_ridarray])){
	   foreach($_G[gp_ridarray] as $rid){
			$query=DB::query("SELECT * FROM ".DB::table(common_resource)." WHERE rid='$rid'");	
			while($row=DB::fetch($query)){	
				if($row[rtype]=='group_pcomment'){
					$thread = DB::fetch_first("SELECT tid FROM ".DB::Table("forum_post")." WHERE pid='$rid' ");
					DB :: query("UPDATE ".DB::Table("forum_thread")." SET replies=replies-1 WHERE tid=$thread[tid] ");	
					DB::query("DELETE FROM ".DB::Table("forum_post")." where pid='$rid'");
					hook_delete_resource($rid,'group_pcomment');
					$pcommentquery=DB::query("SELECT id FROM ".DB::table(forum_postcomment)." WHERE pid='$rid'");
					while($pcomment=DB::fetch($pcommentquery)){
						hook_delete_resource($pcomment[id],'group_ppcomment');
					}
					DB::query("DELETE FROM ".DB::table(forum_postcomment)." where pid='$rid'");	
				}else if($row[rtype]=='group_hcomment'){
					DB::query("DELETE FROM ".DB::table(home_comment)." where cid='$rid'");
					hook_delete_resource($rid,'group_hcomment');
				}else if($row[rtype]=='group_ppcomment'){
					DB::query("DELETE FROM ".DB::table(forum_postcomment)." where id='$rid'");
					hook_delete_resource($rid,'group_ppcomment');	
				}else{	
				}
			}
	   }
   }
   cpmsg_mgr("操作成功", "manage.php?mod=group&op=group_comment&method=search", "succeed");
}

include template("manage/group_comment");

?>
