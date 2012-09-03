<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
$query = DB::query("SELECT data FROM " . DB::table("common_syscache") . " WHERE cname='orgtrees'");
$data = DB::fetch($query);
$s .= $data["data"];
if (!$data["data"]) {
    require_once dirname(dirname(dirname(__FILE__)))."/api/lt_org/group.php";
    $groupmgr = new Group();
    $allorg = $groupmgr->getAllGroups();
    $result = array();
    foreach ($allorg as $item) {
		if($item["parentID"]==9002){
			//$item["name"] = mb_convert_encoding($item["name"], "UTF-8", "GBK");
			$s.="<tr><td><input type='checkbox' id='ids' name='ids' value='".$item[id]."'  title='".$item[name]."'/></td><td>".$item[name]."</td></tr>";
		}
        
    }
    //$t .= 'document.getElementById("orgtrees").innerHTML=d.toString();';
    //$s .= $t;
    //写入cache
    DB::insert("common_syscache", array(cname => "orgtrees", data => addslashes($s)));
}


include template('common/queryorgs');
?>