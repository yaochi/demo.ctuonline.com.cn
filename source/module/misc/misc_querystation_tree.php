<?php
 	header("content-type: text/xml");
    require_once dirname(dirname(dirname(__FILE__)))."/api/lt_org/station.php";
    $stationmgr = new Station();
	if($_GET["parent_id"]){
    	$parent_id = $_GET["parent_id"];
		$allstation = $stationmgr->getStationsByParentId($parent_id);
	}else{
		$allstation = $stationmgr->getStationsByParentId(9);
	}
	$t = '<?xml version="1.0" encoding="UTF-8"?>';
	$t .= '<tree>';
 	foreach ($allstation as $item) {
		$item["s_name"] = mb_convert_encoding($item["s_name"], "UTF-8", "GBK");
		$t .= '<tree text="'.$item["s_name"].'" src="misc.php?mod=querystation_tree&amp;parent_id='.$item["s_id"].'" action="javascript:selectStation(\''.$item["s_id"].'\',\''.$item["s_name"].'\');"/>';
		 
	}
	$t .= '</tree>';
	unset ($stationmgr);
	
 	echo $t;
?>
