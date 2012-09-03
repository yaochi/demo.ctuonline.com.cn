<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: group_index.php 9843 2010-05-05 05:38:57Z wangjinbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$ac = getgpc('ac');
$acarray = array('list','detail','test');
if(!in_array($ac,$acarray))$ac='list';
if($ac=='list'){
	$toplabels = $labels = array();
	$query = DB::query(" select f.fid,f.fup,f.name,ff.groupnum from ".DB::table("forum_forum")." f,".DB::table("forum_forumfield")." ff where f.fid=ff.fid and f.type='label' ");
	while($value = DB::fetch($query)){
		if($value[fup]=='0'){
			$toplabels[] = $value;
		}else{
			$labels[] = $value;
		}
	}
}elseif($ac='detail'){
	
	$lid = intval(getgpc('lid'));
	$query = DB::query(" select f.fid,f.fup,f.name,ff.groupnum from ".DB::table("forum_forum")." f,".DB::table("forum_forumfield")." ff where f.fid=ff.fid and f.type='label' and f.fid='".$lid."' ");
	$label = DB::fetch($query);

	$query = DB::query(" select f.*,ff.* from ".DB::table("forum_forum")." f,".DB::table("forum_forumfield")." ff where f.fid=ff.fid and f.fid in (select groupid from ".DB::table("forum_labelgroup")."  where labelid='".$lid."')");
	 $index = 0;
	 while($value=DB::fetch($query)){
		$groups[] = $value;
		$index++;
	 }
	 $count = $index;
}


include template('group/label_list');

?>