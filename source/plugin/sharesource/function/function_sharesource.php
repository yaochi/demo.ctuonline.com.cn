<?php
/*
 * Created on 2012-2-28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

/**
 *获取课程分享列表
 */
 function getlist($start,$per,$type,$uid=0){
 	if($type==0) $where="";
 	elseif($type==-1) $where=" where uid =".$uid;
 	else $where=" where category=".$type;
 	$query=DB::query("select * from pre_sharesource ".$where." order by commentnum desc,degree desc limit ".$start.",".$per);
 	if ($query == false) {
		return null;
	} else {
		while ($value = DB :: fetch($query)) {
			$obj[] = $value;
		}
		return $obj;
	}
 }

/**
 * 符合条件的分享条数
 */
 function getsharecount($type,$uid=0){
 	if($type==0) $where="";
 	elseif($type==-1) $where=" where uid =".$uid;
 	else $where=" where category=".$type;
 	$count = DB :: result(DB :: query("SELECT COUNT(*) FROM ".DB :: table('sharesource').$where),0);
 	return $count;
 }

 /**
 *获取课程分享记录
 */
 function getsharesource($id){
 	$query=DB::query("select * from pre_sharesource where id=".$id);
 	if ($query == false) {
		return null;
	} else {
		$value = DB :: fetch($query);
		return $value;
	}
 }

 /**
 *获取贡献排行榜表
 */
 function getrank($start,$per){
 	$query=DB::query("select *  from pre_share_province order by num desc limit ".$start.",".$per);
 	if ($query == false) {
		return null;
	} else {
		while ($value = DB :: fetch($query)) {
			$obj[] = $value;
		}
		return $obj;
	}
 }

?>
