<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/home');
require_once libfile('function/space');
require_once libfile('function/spacecp');
require_once libfile('function/discuzcode');

$selectionid = empty($_GET['selectionid'])?0:intval($_GET['selectionid']);
global  $_G;
$mod=$_GET['mod'];
$query=DB::query("SELECT * FROM ".DB::TABLE("selection")." WHERE selectionid='$selectionid' AND fid='$_G[fid]' AND moderated!='-1'");
$selection = DB::fetch($query);
	if($selection){
			$query = DB :: query("SELECT * FROM " . DB :: table('selection_user_vote_num') . " WHERE selectionid='$selectionid' AND uid='$_G[uid]' LIMIT 1");
			$uservotenum = DB :: fetch($query);	 
			$uservotenum['usednum'] = 0;
			$uservotenum['dateline']= $_G['timestamp'];
			$uservotenum = DB :: update("selection_user_vote_num",$uservotenum,"id=".$uservotenum['id']); 
	} 	
//echo "success";

//dexit();
	header("Content-Type: text/xml; charset=utf-8");
	header("Cache-control: private"); 
	header("Pragma: private");
 
	echo('<?xml version="1.0" encoding="utf-8"?><root><![CDATA[success]]></root>');
	dexit();
	
//include template("selection:ajaxupdate");
//exit();
?>
