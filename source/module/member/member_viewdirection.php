<?php

/*
* yy 2011-11-21 
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
//print_r($_G[uid]);
require_once libfile('function/home');
$piclist=array();
$bloglist=array();
$rein=0;
if($_GET['rein']){
	$rein=$_GET['rein'];
}
$piccount=DB::result_first("select count(*) from ".DB::TABLE("home_album")." where friend !='0' and uid=".$_G[uid]);
if($piccount){
	$picquery=DB::query("select * from ".DB::TABLE("home_album")." where friend!='0' and uid=".$_G[uid]);
	while($picvalue=DB::fetch($picquery)){
		$query=DB::query("select * from ".DB::TABLE("home_pic")." where albumid=".$picvalue['albumid']." limit 0,5");
		while($value=DB::fetch($query)){
			$picvalue[piclist][] = $value;
		}
		$piclist[]=$picvalue;
	}
		
}
$blogcount=DB::result_first("select count(*) from ".DB::TABLE("home_blog")." where friend !='0' and uid=".$_G[uid]);
if($blogcount){
	$blogquery=DB::query("select blog.*,bf.message from ".DB::table('home_blog')." blog left join ".DB::table('home_blogfield')." bf on bf.blogid = blog.blogid where blog.friend !='0' and blog.uid=".$_G[uid]);
	while($blogvalue=DB::fetch($blogquery)){
			$blogvalue[message]=getstr($blogvalue[message], 50, 1, 1, 0, 0, -1);
			$bloglist[]=$blogvalue;
		}
}

if(submitcheck('sureform')){
	$blogid=$_POST['blogid'];
	$albumid=$_POST['albumid'];
	$type=$_POST['type'];
	$rein='1';
	if($type=='del'){
		require_once libfile("function/delete");
		if($blogid){
			deleteblogs($blogid);
		}
		if($albumid){
			deletealbums($albumid);
		}
	}else if($type=='pub'){
		if($blogid){
			DB::query("update ".DB::TABLE("home_blog")." set friend='0' where uid=$_G[uid] and blogid in (".implode(',',$blogid).")");
		}
		if($albumid){
			//DB::query("update ".DB::TABLE("home_blog")." set friend='0' where uid=$_G[uid] and albumid in (".implode(',',$albumid).")");
			DB::query("delete from ".DB::TABLE("home_album")." where uid=$_G[uid] and albumid in (".implode(',',$albumid).")");
			DB::query("update ".DB::TABLE("home_pic")." set albumid='0' where uid=$_G[uid] and albumid in (".implode(',',$albumid).")");
		}
	}
	if(!submitcheck('endform')){
		dheader("Location: member.php?mod=viewdirection&rein=".$rein);
	}
}

if(submitcheck('endform')){
	DB::query("update ".DB::TABLE("common_member")." set doindex=1 where uid=".$_G[uid]);
	dheader("Location: home.php");
}






include template('member/viewdirection');
?>