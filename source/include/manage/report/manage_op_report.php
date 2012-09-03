<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
global $_G;

$title="举报管理";
$pagesize=20;
$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
$start = ($page - 1) * $pagesize;
$type=$_G[gp_type]?$_G[gp_type]:'new';
$method=$_G["gp_method"]?$_G["gp_method"]:'index';
$_G["gp_method"]=$method;
$result=array();
//alter by qiaoyz,2011-3-22,EKSN 208 查看举报权限修改
require_once dirname(dirname(dirname(dirname(__FILE__))))."/api/lt_org/role.php";
$role=new TRole();
$all_role=$role->getRoleByUserId($_G['uid']);
$roles=array();
foreach ($all_role as $value) $roles[]=$value['id'];
if(in_array('10044',$roles)||($_G['uid']==1)) {

if($_G["gp_method"]=='index'){
	//$result=array();
	//exit("index");
	$curcredits = $_G['setting']['creditstransextra'][8] ? $_G['setting']['creditstransextra'][8] : $_G['setting']['creditstrans'];
	
	if($type=="new"){
		$reportcount = DB::result_first("SELECT count(*) FROM ".DB::table('common_report')." WHERE opuid=0");
		$query = DB::query("SELECT * FROM ".DB::table('common_report')." WHERE opuid=0 ORDER BY num DESC, dateline DESC LIMIT $start, $pagesize");		
	}else{
		$reportcount = DB::result_first("SELECT count(*) FROM ".DB::table('common_report')." WHERE opuid>0");
		$query = DB::query("SELECT * FROM ".DB::table('common_report')." WHERE opuid>0 ORDER BY optime DESC LIMIT $start, $pagesize");			
	}
	while($row=DB::fetch($query)){		
		$row['dateline']=date('y-m-d H:i', $row['dateline']);
		$result[$row['id']]=$row;
	}
	}
	$multipage = multi($reportcount, $pagesize, $page,"manage.php?mod=report&amp;op=report&method=index&amp;type=$type");	
}elseif($_G["gp_method"]=="delete"){
	//exit("delete");
	if(!empty($_G['gp_reportids'])) {
		DB::query("DELETE FROM ".DB::table('common_report')." WHERE id IN(".dimplode($_G['gp_reportids']).")");
	}
	cpmsg_mgr('举报删除成功', "manage.php?mod=report&op=report&type=$type", 'succeed');
}elseif($_G["gp_method"]=="resolve"){
	//exit("resolve");
	if(!empty($_G['gp_reportids'])) {
		$curcredits = $_G['setting']['creditstransextra'][8] ? $_G['setting']['creditstransextra'][8] : $_G['setting']['creditstrans'];
		foreach($_G['gp_reportids'] as $id) {
			$opresult = !empty($_G['gp_creditsvalue'][$id])? $curcredits."\t".intval($_G['gp_creditsvalue'][$id]) : 'ignore';
			if(!empty($_G['gp_creditsvalue'][$id])) {
				$uid = $_G['gp_reportuids'][$id];
				$credittag = $_G['gp_creditsvalue'][$id] > 0 ? '+' : '';
				$creditchange = $_G['setting']['extcredits'][$curcredits]['title'].'&nbsp;'.$credittag.$_G['gp_creditsvalue'][$id];
				notification_add($uid, 'report', 'report_change_credits', array('creditchange' => $creditchange), 1);
				updatemembercount($uid, array($curcredits => $_G['gp_creditsvalue'][$id]), true, 'RPC', $id);
			}
			DB::query("UPDATE ".DB::table('common_report')." SET opuid='$_G[uid]', opname='$_G[username]', optime='".TIMESTAMP."', opresult='$opresult' WHERE id='$id'");
		}
		cpmsg_mgr('举报处理成功', "manage.php?mod=report&op=report&type=$type", 'succeed');
	}
	
}


include template("manage/report_report");
?>