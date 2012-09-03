<?php
global $_G;
$title = "记录管理";
$method=$_G["gp_method"]?$_G["gp_method"]:'index';
$_G["gp_method"]=$method;
if($_G["gp_code"]=="true"){
    $_G[gp_usernames] = urldecode($_G[gp_usernames]);
	$_G[gp_usernames_uids] = urldecode($_G[gp_usernames_uids]);
    $_G[gp_content] = urldecode($_G[gp_content]);
    $_G[gp_starttime] = urldecode($_G[gp_starttime]);
    $_G[gp_endtime] = urldecode($_G[gp_endtime]);
} 
if($_G['gp_method']=="index"){
    $pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;
	$indexsql="SELECT 
		doing.doid as doid,
		doing.uid as uid,
		doing.username as username,
		doing.message as message,
		doing.replynum as replynum,
		doing.dateline as dateline,
		re.id as reid,
		profile.realname as realname 
		FROM pre_home_doing doing,pre_common_member_profile profile,pre_common_resource re WHERE re.rtype='doing' AND doing.doid=re.rid AND doing.uid=profile.uid";	
	$countsql="SELECT count(*) FROM pre_home_doing doing,pre_common_member_profile profile,pre_common_resource re WHERE re.rtype='doing' AND doing.doid=re.rid AND doing.uid=profile.uid";
	
	$wheresql="";
	if(!empty($_G['validate']['orgidarray'])&&!in_array(9002,$_G['validate']['orgidarray'])&&!in_array(9001,$_G['validate']['orgidarray'])){
		$wheresql.=" AND re.company_id IN(".dimplode($_G['validate']['orgidarray']).")";
	}
/*	echo "管理组织机构id:";print_r($_G['validate']['orgidarray']);
	echo "<br/>";
	echo "wheresql:".$wheresql;*/
	$countsql.=$wheresql;
	$indexsql.=$wheresql;
	$query=DB::query($countsql);
	$count=DB::fetch($query,0);
	foreach($count as $value){
		$count=$value;
	}
	$list=array();
	if($count){
		$query=DB::query($indexsql." ORDER BY re.dateline DESC LIMIT $start,$pagesize  ");	
		while($row=DB::fetch($query)){
			$row['dateline']=dgmdate($row[dateline]);
			$list[$row['reid']]=$row;		
		}		
	}
	 $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
	$url = "manage.php?mod=home&op=doing&method=index&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);
}elseif($_G["gp_method"]=="search"){
    $pagesize = 10;
    $page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
    $start = ($page - 1) * $pagesize;
/*    require_once libfile("function/org");
    $org_id = get_org_id_by_user($_G[username]);
    echo "所属机构id:".$org_id;
    $org_id = $org_id?$org_id:0;
    
    //统计
    $orgids[] = $org_id;
    $suborgids = get_sub_org_ids($org_id);
    if($suborgids){
        $orgids[] = $suborgids;
    }
    $sql = "SELECT * FROM ".DB::Table("common_resource")." cr,"
        .DB::table("common_member")." cm,".DB::table("common_member_profile")." cmp"
        ." WHERE cm.uid=cmp.uid AND cm.uid=cr.uid AND cr.rtype='doing' AND cr.org_id IN(".implode(",", $orgids).")";
    //用户工号
    if(!empty($_G[gp_username])){
        $query = DB::query("SELECT uid FROM ".DB::table("common_member")." WHERE username='".$_G[gp_username]."'");
        $uids[] = 0;
        while($row=DB::fetch($query)){
            $uids[] = $row[uid];
        }
        $sql = $sql . " AND cr.uid IN(".  implode(",", $uids).")";
    }
    //内容
    if(!empty($_G[gp_content])){
        $query = DB::query("SELECT doid FROM ".DB::Table("home_doing")." WHERE message LIKE '%$_G[gp_content]%'");
        $doids[] = 0;
        while($row=DB::fetch($query)){
            $doids[] = $row[doid];
        }
        $sql = $sql." AND cr.rid IN(".implode(",", $doids).")";
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
        $query = DB::query(str_replace("#fileds#", "cr.rid, cr.dateline, cm.uid, cm.username, cmp.realname", $sql)." LIMIT $start, $pagesize");
        $rids[] = 0;
        while($row=DB::fetch($query)){
            $rids[] = $row[rid];
            $rlist[$row[rid]] = $row;
        }
        $query = DB::query("SELECT doid, message FROM ".DB::Table("home_doing")." WHERE doid IN(".implode(",", $rids).")");
        while($row=DB::fetch($query)){
            $row[date] = dgmdate($rlist[$row[doid]][dateline]);
            $doinglist[] = $row;
        }
    }*/
	$searchsql="SELECT 
		doing.doid as doid,
		doing.uid as uid,
		doing.username as username,
		doing.message as message,
		doing.replynum as replynum,
		doing.dateline as dateline,
		profile.realname as realname 
		FROM pre_home_doing doing,pre_common_member_profile profile,pre_common_resource re WHERE re.rtype='doing' AND doing.doid=re.rid AND doing.uid=profile.uid";	
	$countsql="SELECT count(*) FROM pre_home_doing doing,pre_common_member_profile profile,pre_common_resource re WHERE re.rtype='doing' AND doing.doid=re.rid AND doing.uid=profile.uid";
	$usernames=$_G['gp_usernames']?trim($_G['gp_usernames']):'';
	$content=$_G['gp_content']?trim($_G['gp_content']):'';
	$starttime=$_G['gp_starttime'];
	$endtime=$_G['gp_endtime'];
	
	
	$wheresql="";
	if(!empty($content)){
		$wheresql.=" AND doing.message LIKE '%$content%'";
	}
	if(!empty($usernames)){
		$wheresql.=" AND profile.uid IN(".$_G['gp_usernames_uids'].")";
	}
	if(!empty($starttime)){
		$starttime = strtotime($_G[gp_starttime]);
		$wheresql.=" AND doing.dateline>$starttime";
	}
	if(!empty($endtime)){
		$endtime = strtotime($_G[gp_endtime]);
		$wheresql.=" AND doing.dateline<$endtime";
	}
	if(!empty($_G['validate']['orgidarray'])&&!in_array(9002,$_G['validate']['orgidarray'])&&!in_array(9001,$_G['validate']['orgidarray'])){
		$wheresql.=" AND re.company_id IN(".dimplode($_G['validate']['orgidarray']).")";
	}
	
	$countsql.=$wheresql;
	$searchsql.=$wheresql;
	$query=DB::query($countsql);
	$count=DB::fetch($query,0);
	foreach($count as $value){
		$count=$value;
	}
	$list=array();
	if($count){
		$query=DB::query($searchsql." ORDER BY re.dateline DESC LIMIT $start,$pagesize  ");	
		while($row=DB::fetch($query)){
			$row['dateline']=dgmdate($row[dateline]);
			$list[$row['doid']]=$row;		
		}		
	}
	
    $usernames = urlencode($_G[gp_usernames]);
	$usernames_uids = urldecode($_G[gp_usernames_uids]);
    $content = urlencode($_G[gp_content]);
    $starttime = urlencode($_G[gp_starttime]);
    $endtime = urlencode($_G[gp_endtime]);
    $url = "manage.php?mod=home&op=doing&method=search&code=true&usernames=$usernames&usernames_uids=$usernames_uids&content=$content&starttime=$starttime&endtime=$endtime";
    $multipage = multi($count, $pagesize, $page, $url);
}else if($_G[gp_method]=="delete"){
    //删除
   require_once libfile('function/delete');
   if(!empty($_G['gp_ridarray'])){
  		deletedoings($_G['gp_ridarray']);
  		hook_delete_resources($_G['gp_ridarray'],'doing');	   
   }
   cpmsg_mgr("操作成功", "manage.php?mod=home&op=doing", "succeed");
}

include template("manage/home_doing");
?>
