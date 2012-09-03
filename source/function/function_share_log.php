<?php

/*
 * 分享次数累加
 * $rid 资源编号，如相册编号
 * $rtype 资源类型 
 * $subject 资源标题
 * $subjectlink 资源连接
 *  $total 累加次数，可以为负数，表示减少累加次数
 */

function share_log_inc($rid, $rtype,$subject,$subjectlink, $total=1) {
    if (!isset($rid) || !isset($rtype) || !isset($subject) || !isset($subjectlink) ) {
        return false;
    }
    $query = DB::query("SELECT COUNT(1) AS c FROM " . DB::table("common_share_log") . " WHERE rid=" . $rid . " AND rtype='" . $rtype . "'");
    $count = DB::fetch($query);
    if (!$count || $count["c"]==0) {
        return DB::insert("common_share_log", array(rid => $rid, total => $total, last_update => time(), rtype => $rtype,subject => $subject,subjectlink => $subjectlink));
    } else {
        DB::query("UPDATE " . DB::table("common_share_log") . " SET total=total+" . $total . ", last_update=" . time() . " WHERE rid=" . $rid . " AND rtype='" . $rtype . "'");
    }
}

/**
 * 获取分享次数
 * $rid 资源编号，如相册编号
 * $rtype 资源类型 
 */
function share_log_get_total($rid, $rtype) {
    if (!isset($rid) || !isset($rtype)) {
        return false;
    }
    $query = DB::query("SELECT id, total FROM " . DB::table("common_share_log") . " WHERE rid=" . $rid . " AND rtype='" . $rtype . "'");
    if($query){
        $row = DB::fetch($query);
        if($row){
            return $row;
        }
    }
}

/**
 * 获取排行榜
 * $rtype 资源类型
 * $limitsize 所取记录数 
 */
function share_log_get_type($rtype, $limitsize){
	if(!isset($rtype)){
		return false;
	}
	$query = DB::query("SELECT rid,subject,subjectlink,total FROM " . DB::table("common_share_log") . " WHERE rtype='" . $rtype . "' order by total desc,last_update desc limit ".$limitsize);
    $shares =  array();
	while($value = DB::fetch($query)){
		$shares[]=$value;
	}
	return $shares;
}
?>
