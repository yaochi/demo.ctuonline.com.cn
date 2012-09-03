<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
if($_GET[type]) $type=$_GET[type];
else $type=0;
$s = '<div id="orgtree" style="height:400px;overflow:auto;"></div>';


    $t .= '<link href="static/js/dtree/dtree.css" rel="stylesheet" type="text/css" /><script src="static/js/dtree/dtree.js" type="text/javascript"></script>';
    $t .= '<script type="text/javascript">d = new dTree("d");';
   require_once (dirname(__FILE__)."/function/function_stationcourse.php");
    $allorg = getAllStation();
    $result = array();
    foreach ($allorg as $item) {
        $parentId = $item["parent_id"];
        $typy = $item["type"];
        if (!isset($item["parent_id"])) {
            $parentId = 0;
        }
        if($parentId==-1){
            $t .= 'd.add(' . $item["id"] . ', ' . $parentId . ', "' . $item["name"] . '", "#");';
        }else{
        	if($typy==2){
            $t .= 'd.add(' . $item["id"] . ', ' . $parentId . ', "' . $item["name"] . '", "javascript:selectstation(' . $item["id"] . ', \'' . $item["name"] . '\', ' . $type . ');");';
        	}else{
        	$t .= 'd.add(' . $item["id"] . ', ' . $parentId . ', "' . $item["name"] . '", "#");';	
        	}
        }//$station_id
    }
    $t .= 'document.getElementById("orgtree").innerHTML=d.toString();';
    $s .= $t;



$namevalue = $_GET["namevalue"];
if($_GET["orgid"]){
    $s .= "d.openTo('$_GET[orgid]', true);";
    $curorgid = $_GET["orgid"];
}
$s .= '</script>';
if($_GET["orgname"]){
    $curorgname = urldecode($_GET["orgname"]);
}
include template("stationcourse:select_station");
?>