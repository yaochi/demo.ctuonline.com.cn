<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_group.php 10851 2010-08-04 08:17:09Z wufuting $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/**
 * 专区创建时调用，更新标签和专区的对应关系
 * @param groupid 专区id
 * @param tagids 标签id字符串，以逗号分隔
 */
function insertlabelgroup($groupid,$tagids){
	
	//$labels = preg_split(",",$tagids);
	//王聪，分隔逗号有问题，用explode函数
	$labels = explode(",",$tagids);
	
	if(!empty($labels) and is_array($labels))
	{
		
		DB::delete("forum_labelgroup"," groupid='".$groupid."' ");
		foreach($labels as $key => $value)
		{
			DB::insert("forum_labelgroup",array('labelid' => $value, 'groupid' => $groupid));
		}
		$query = DB::query("select labelid, count(*) as num from ".DB::table("forum_labelgroup")." group by labelid");
		while($info = DB::fetch($query)){
			DB::query("update ".DB::table("forum_forumfield")." set groupnum=".$info[num]." where fid='".$info[labelid]."'");
		}
	}
}
/**
 * 删除专区或删除标签调用方法
 * @param groupid 专区id
 * @param labelid 标签id
 */
function deletelabelgroup($groupid='',$labelid=''){
	if(!empty($groupid) && !empty($labelid)){
		DB::delete("forum_labelgroup",array('groupid' =>$groupid,'labelid' =>$labelid));
	}else{
		if(!empty($groupid)){
			DB::delete("forum_labelgroup","groupid='$groupid'");
		}
		if(!empty($labelid)){
			DB::delete("forum_labelgroup","labelid='$labelid'");
		}
	}
	$query = DB::query("select labelid, count(*) as num from ".DB::table("forum_labelgroup")." group by labelid");
	while($info = DB::fetch($query)){
		DB::query("update ".DB::table("forum_forumfield")." set groupnum=".$info[num]." where fid='".$info[labelid]."'");
	}
}

/**
 * 根据专区id查找专区所属的标签
 * @param groupid 专区id
 */
function listlabelsbygroupid($groupid){
	if(!empty($groupid)){
		$query = DB::query("select f.fid,f.name from ".DB::table("forum_forum")." f where f.type='label' and f.fid in (select lg.labelid from ".DB::table("forum_labelgroup")." lg where lg.groupid='".$groupid."')");
		$labels = array();
		while($value = DB::fetch($query)){
			$labels[$value['fid']] = $value['name'];
		}
		return $labels;
	}
}
/**
 * fumz 定制
 * @param unknown_type $groupid
 * @return unknown
 */
function listlabelsbygroupoffu($groupid){
	if(!empty($groupid)){
		$query = DB::query("select f.fid,f.name from ".DB::table("forum_forum")." f where f.type='label' and f.fid in (select lg.labelid from ".DB::table("forum_labelgroup")." lg where lg.groupid='".$groupid."')");
		$labels = array();
		while($value = DB::fetch($query)){
			$labels[$value['fid']] = $value['fid'];
		}
		return $labels;
	}
}
function deletelabelgroupfu($groupid='',$labelid=''){
		if($groupid&&$labelid){
			DB::query("delete from pre_forum_labelgroup where groupid=$groupid");
			$sql="update pre_forum_forumfield set groupnum=groupnum-1 where fid=$labelid";
			DB::query($sql);		
		}
}  
/**
 * 列出所有标签（包括标签分类）
 */
function listalllabels(){
	
	$query = DB::query("select f.fid,f.name,f.fup,ff.groupnum from ".DB::table("forum_forum")." f  LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) where f.type='label'");
	$labels =  array();
	while($value = DB::fetch($query)){
		if($value['fup']=='0'){
			$labels[$value['fid']]['0']=$value;
		}else{
			$labels[$value['fup']][$value['fid']] = $value;
		}
	}
	return $labels;
}
/**
 * 查询分类下所有标签列表
 * @param typeId 标签分类id
 */
function listchildlabels($typeId){
	if(empty($typeId)){
		return null;
	}
	$query = DB::query("select f.fid,f.name,f.fup,ff.groupnum from ".DB::table("forum_forum")." f  LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) where f.type='label' and f.fup='".$typeId."' ");
	$labels =  array();
	while($value = DB::fetch($query)){
		$labels[]=$value;
	}
	return $labels;
}
/**
 * 根据标签id查找标签已关联的专区数目
 */
function getlabelgroupnum($labelid){
	if(empty($labelid)){
		return null;
	}
	$query = DB::query("select f.fid,f.name,f.fup,ff.groupnum from ".DB::table("forum_forum")." f  LEFT JOIN ".DB::table('forum_forumfield')." ff USING(fid) where f.type='label' and f.fid='".$labelid."' ");
	$label=null;
	while($value = DB::fetch($query)){
		$count=DB::result_first("select count(*)  from ".DB::table("forum_labelgroup")." where labelid=".$value['fid']);
		$value[groupnum]=$count;
		$label=$value;
	}
	return $label;
}

/**
 * 根据标签名称查找标签id
 */
function getlabelbyname($labelnames){
	if(empty($labelnames)){
		return null;
	}
	$names = explode(",",$labelnames);
	$query = DB::query("select f.fid from ".DB::table("forum_forum")." f  where f.type='label' and f.name in ('".implode("','",$names)."') ");
	$labels =  array();
	while($value = DB::fetch($query)){
		$labels[]=$value['fid'];
	}
	return implode(",",$labels);
}

?>