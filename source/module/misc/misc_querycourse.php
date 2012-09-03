<?php
global $_G;
//echo("lllllllllllllllllll");
require_once(dirname(dirname(dirname(__FILE__)))."/plugin/resourcelist/create.inc.php");
$pagesize=20;
$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
$start = ($page - 1) * $pagesize;
$title=$_REQUEST['title'];
$title=urldecode($title);
$count=0;
$resourcetype=4;//课程（即点播）
$from=$_REQUEST['from'];
$theurl="misc.php?mod=querycourse&page=$page&title=$title&from=$from&title=$title";
$filejson=getresourcelist($page,$pagesize,$resourcetype,$title,'','','','','','');
$list = $resourses = array();
if($filejson){
	$count = $filejson['result']['totalAmount'];
	$list = $filejson['result']['resources'];
}
$multipage='';

if($from=='search'){
	header('Content-Type: text/xml');
    $sql = "<?xml version='1.0' encoding='utf-8'?><root><![CDATA[";
    $sql.="<table class='table' width='100%' >";
    $sql.="<tr style='font-size:12px;color:#000000; font-weight:bold;width:600px;'><td  width='10%' style='padding-left:20px;'>选择</td><td width='15%' class='a b'>点播编号</td><td  width='75%'>点播名称</td></tr>";
    foreach ($list as $key=>$value){
        $sql .= "<tr style='width:600px;'>";
        $sql.="<td style='padding-left:20px;'><input type='radio' id='course$value[id]' name='courseurl' value='$value[titlelink]' /></td>";
        $sql.="<td align='center'>$value[id]</td>";
        $sql.="<td><label for='course$value[id]'>$value[title]</label></td>";
        $sql.="</tr>";
    }
    $sql .="<tr style='width:600px;'><td colspan='3' style='padding:5px;'>$multipage</td></tr>";
    $sql.="</table>]]></root>";
    echo $sql;
    exit;
}else{	
	if($count>$pagesize) {
		$multipage = multi($count, $pagesize, $page, $theurl);
	}
	include template('common/querycourse');	
}
?>