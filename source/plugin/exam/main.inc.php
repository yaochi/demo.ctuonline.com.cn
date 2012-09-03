<?php
/*
 * Created on 2012-2-28
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
  require_once (dirname(dirname(dirname(__FILE__))) . "/joinplugin/pluginboot.php");

/**
 *学员进入有奖问答页面
 */
  function index(){
  	global $_G;
  	require_once (dirname(__FILE__)."/function/function_exam.php");
  	$id=$_G['gp_id'];
  	if($id=='') $id=getmaxid();
  	if($id=='') $id=1;
  	$info=getexaminfo($id,1);	//0:未开启memcache
  	if($info[base][starttime]<time()){
		$info[base][status]=1;
  	}
  	$my=getmyanswer($id,$_G[uid],1);
  	if($my[is]){
  		$url = "forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=exam&plugin_op=viewmenu&exam_action=index";
   		header("Location:".$url);
  	}
  	$user=array("uid"=>$_G[uid],"regname"=>$_G[username],"realname"=>user_get_user_name($_G[uid]));
  	return array("user"=>$user,"questions"=>$info[questions],"base"=>$info[base]);
  }

/**
 *学员保存答题信息
 */
  function save(){
  	global $_G;
  	require_once (dirname(__FILE__)."/function/function_exam.php");
  	$num=$_G[gp_num];
  	$eid=$_G[gp_eid];
  	$info=getexaminfo($eid,1);	//0:未开启memcache
  	$my=getmyanswer($eid,$_G[uid],1);
  	if($my[is]){
  		showmessage("你已经参与过本次有奖问答，请勿重复提交","forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=exam&plugin_op=viewmenu&id=".$eid);
  	}
  	$rightnum=0;
  	$answer=$tids='';
	for($i=1;$i<=$num;$i++){
		if($info[questions][$i-1][type]==1)
			$answ=$_G['gp_t_'.$i];
		else
			$answ=join("",$_G['gp_t_'.$i]);
		if($answ==$info[answers][$i]){
			$rightnum++;
			$tids.=$i.",";
		}
		$answer.=$answ.",";
	}

	$status=0;
	if($rightnum==$num) $status=1;
		updaterightnum($eid,$status);
		op_learncredit($_G[uid],$_G[username],$_G[gp_realname],4,4,$eid,($status+1)*10,'');

	if($tids!='')	update_question_rightnum($eid,substr($tids,0,-1));
	$answer=substr($answer,0,-1);
	$arr=array("eid"=>$eid,"uid"=>$_G[uid],"realname"=>$_G[gp_realname],"answers"=>$answer,"rightnum"=>$rightnum,"tel"=>$_G[gp_tel],"dateline"=>time());
	record_answer($arr);
	showmessage("回答完毕","forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=exam&plugin_op=viewmenu&id=".$eid);
  }

/**
 *有奖问卷列表
 */
 function exams(){
 	global $_G;
 	require_once (dirname(__FILE__)."/function/function_exam.php");
 	$list=getlist(20);
 	return array("list"=>$list);
 }

/**
 *管理员统计页面,包括相关统计和答题排名前列的学员名单
 */
 function stats(){
 	global $_G;
 	require_once (dirname(__FILE__)."/function/function_exam.php");
	$eid=$_G['gp_id'];
	$info=getexaminfo($eid,0);
	$answerlist=getanswerlist($eid,20);
	return array("base"=>$info[base],"questions"=>$info[questions],"answerlist"=>$answerlist);
  }

  function test(){
	require_once (dirname(__FILE__)."/function/function_exam.php");
  	getexaminfo(1,1);
  }
?>
