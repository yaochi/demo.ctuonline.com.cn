<?php

/*
 * 根据专区id,提问贴id返回提问
 */
function view(){
	$groupid=$_GET['fid'];//专区id
    $threadid = $_GET["threadid"];//提问id
    $type='';//帖子类型，提问（discuz! X中的悬赏）
    $query = DB::query("SELECT * FROM ".DB::table("forum_thread")." WHERE id=".$threadid." AND fid=".$groupid);//undo 添加类型
    $thread = DB::fetch($query);
    return array("thread"=>$thread);
}

?>