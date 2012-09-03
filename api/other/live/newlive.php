<?php
	require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
	$discuz = & discuz_core::instance();
	$discuz->init();
	global $_G;
	$action=$_G['gp_ac'];
	if($action){
		$action();
	}
	
	function groups(){
		global $_G;
		$key=$_G['gp_key'];
		$mykey=md5("newlive_groups");
		$arr=array();
		
		if($mykey!=$key)
		{
			$arr['status']=0;
			$arr['message']="Key error";
		} 
		else 
		{
			$arr['status']=1;
			$arr['message']="SUCCESS";
			$obj=array();
			$query=DB::query("SELECT f.fid,f.name FROM `pre_forum_forumfield` ff,`pre_forum_forum` f where f.type='sub' AND ff.fid=f.fid AND (ff.group_type=1 OR ff.group_type=20);");
			while($value=DB::fetch($query))
			{
				$obj[]=$value;
			} 
				$arr['data']=$obj;
			
		}
		$jsondata = json_encode ($arr);
		echo $jsondata;
	}
	
	//http://localhost/forum111/api/other/live/newlive.php?ac=ismanage&regname=E00230202&fid=507&key=20486571d9d0391a3692a62475db9368
	function ismanage(){
		global $_G;
		$regname=$_G['gp_regname'];
		$fid=$_G['gp_fid'];
		$key=$_G['gp_key'];
		$mykey=md5("newlive_".$regname."_".$fid);
		$arr=array();
		
		if(!$regname)
		{
			$arr['status']=0;
			$arr['message']="Regname is null";
		}
		else if(!$fid)
		{
			$arr['status']=0;
			$arr['message']="Fid is null";
		}  
		else if($mykey!=$key)
		{
			$arr['status']=0;
			$arr['message']="Key error";
		} 
		else 
		{
			$arr['status']=1;
			$arr['message']="SUCCESS";
			if($regname=='admin')
				$arr['data']=1;
			else
			{
				$query=DB::query("SELECT * FROM `pre_forum_groupuser` WHERE fid=".$fid." AND username='".$regname."' AND level<=2");
				$value=DB::fetch($query);
				if($value) 
					$arr['data']=1;
				else
					$arr['data']=0;
			}
		}
		$jsondata = json_encode ($arr);
		echo $jsondata;
	}
	
	function userinfo(){
		global $_G;
		$regname=$_G['gp_regname'];
		$key=$_G['gp_key'];
		$mykey=md5("newlive_".$regname);
		if(!$regname)
		{
			$arr['status']=0;
			$arr['message']="Regname is null";
		}
		else if($mykey!=$key)
		{
			$arr['status']=0;
			$arr['message']="Key error";
		} 
		else 
		{
			$arr['status']=1;
			$arr['message']="SUCCESS";
			$query=DB::query("SELECT cmp.uid,cmp.realname FROM `pre_common_member` cm,`pre_common_member_profile` cmp WHERE cm.username='".$regname."' AND cmp.uid=cm.uid;");
			$value=DB::fetch($query);
			if($value) 
			{
				$data=array("isexist"=>1,"uid"=>$value[uid],"realname"=>$value[realname]);
				$arr['data']=$data;
			}
			else
				$arr['data']=array("isexist"=>0);
		}
		$jsondata = json_encode ($arr);
		echo $jsondata;
	}
	
?>