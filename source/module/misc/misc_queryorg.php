<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
global $_G;

$channel = empty($_GET['channel']) ? "orgname" : $_GET['channel'];
$cname=empty($_GET['channel']) ? "orgtree" : "orgtree_".$_GET['channel'];
$query = DB::query("SELECT data FROM " . DB::table("common_syscache") . " WHERE cname='".$cname."'");
$data = DB::fetch($query);
$s = '<div id="orgtree" style="height:400px;overflow:auto;"></div>';
$s .= $data["data"];
if (!$data["data"]) {
    $t .= '<link href="static/js/dtree/dtree.css" rel="stylesheet" type="text/css" /><script src="static/js/dtree/dtree.js" type="text/javascript"></script>';
    $t .= '<script type="text/javascript">d = new dTree("d");';
    require_once dirname(dirname(dirname(__FILE__)))."/api/lt_org/group.php";
    $groupmgr = new Group();
    $allorg = $groupmgr->getAllGroups();
    $result = array();
    foreach ($allorg as $item) {
        $parentId = $item["parentID"];
        if (!isset($item["parentID"])) {
            $parentId = 0;
        }
        if($_G[config][misc][convercode]) $item["name"] = mb_convert_encoding($item["name"], "UTF-8", "GBK");
        if($parentId==-1){
            $t .= 'd.add(' . $item["id"] . ', ' . $parentId . ', "' . $item["name"] . '", "#");';
        }else{
            $t .= 'd.add(' . $item["id"] . ', ' . $parentId . ', "' . $item["name"] . '", "javascript:orgtreeselect(' . $item["id"] . ', \'' . $item["name"]  .'\', \'' . $channel . '\');");';
        }
    }
    $t .= 'document.getElementById("orgtree").innerHTML=d.toString();';
    $s .= $t;
    //写入cache
    DB::insert("common_syscache", array(cname => $cname, data => addslashes($t)));
}

$namevalue = $_GET["namevalue"];
if($_GET["orgid"]){
    $s .= "d.openTo('$_GET[orgid]', true);";
    $curorgid = $_GET["orgid"];
}
$s .= '</script>';
if($_GET["orgname"]){
    $curorgname = urldecode($_GET["orgname"]);
}
include template('common/queryorg');
?>