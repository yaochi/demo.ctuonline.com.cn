<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_share.php 6741 2010-03-25 07:36:01Z cnteacher $
 */

function mkshare($share) {
	$share['body_data'] = unserialize($share['body_data']);

	$searchs = $replaces = array();
	if($share['body_data']) {
		foreach (array_keys($share['body_data']) as $key) {
			$searchs[] = '{'.$key.'}';
			$replaces[] = $share['body_data'][$key];
		}
	}
	$share['body_template'] = str_replace($searchs, $replaces, $share['body_template']);
	return $share;
}

function listShareIds($id,$idType) {
	$wheresql=" 1=1 ";
	$sids=array();
	if($id){
		$wheresql.=" AND id in ('".implode("','", $id)."')";
	}
	if($idType){
		$wheresql.=" AND idtype='$idType'";
	}
	$query = DB::query("SELECT sid FROM ".DB::table('home_sharelog')."
		WHERE $wheresql
		GROUP BY sid");
	while ($row = DB::fetch($query)) {
		$sids[]=$row['sid'];
	}
	return $sids;
}
?>