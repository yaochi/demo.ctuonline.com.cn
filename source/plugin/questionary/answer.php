<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/home');
require_once libfile('function/space');
require_once libfile('function/spacecp');
require_once libfile('function/discuzcode');

$questid = empty($_GET['questid'])?0:intval($_GET['questid']);
$anonymity=DB::result_first("select anonymity from ".DB::TABLE("home_feed")." where icon='questionary' and id=".$questid);
if($anonymity>0){
	include_once libfile('function/repeats','plugin/repeats');
	$repeats=getforuminfo($anonymity);
}
$mod=$_GET['mod'];
$query=DB::query("SELECT count(1) as c FROM ".DB::TABLE("questionary")." WHERE questid='$questid' AND fid='$_G[fid]' AND moderated!='-1'");
 $count = DB::fetch($query);
    $count = $count["c"];
	if($count){
		if(submitcheck('answersubmit')){
			if(empty($_G['uid'])){
				showmessage('你还没有登录','member.php?mod=logging&action=login');
			}
			$query = DB::query("SELECT choicerids FROM ".DB::table('questionary_questionoption')." WHERE questid='$questid'");
			while($questionaryarray = DB::fetch($query)) {
				if(strexists("\t".$questionaryarray['choicerids']."\t", "\t".$_G['uid']."\t")) {
					showmessage('你已经答过了');
				}
			}
			$query=DB :: query("SELECT * FROM " . DB :: table('questionary') . " WHERE questid='$questid' AND fid='$_G[fid]' LIMIT 1");
			$value=DB::fetch($query);
			$questname=$value[questname];
			$visible=$value[visible];
			$joiner=$value[joiner]+1;
			$questionid=$_POST['questionid'];
			foreach($questionid as $key => $value){
				$answer=$_POST['answers_'.$value];
				if(empty($answer)){
					showmessage("你还有问题没回答，请返回");
				}
			}
			$uid=$_G['uid'];
			$username = $_G['username'];
			foreach($questionid as $key => $value) {
				$questionid=$value;
				$answer=$_POST['answers_'.$value];
				$dateline= $_G['timestamp'];
				$options='';
				foreach($answer as $key=>$value){
					$qoptionid=$value;
					$query=DB :: query("SELECT choices,choicerids FROM " . DB :: table('questionary_questionoption') . " WHERE qoptionid='$qoptionid' LIMIT 1");
					$value=DB::fetch($query);
					$choices=$value[choices]+1;
					$choicerids= $value[choicerids]."\t".$_G['uid'];
					$options.= "\t".$qoptionid;
					DB::query("UPDATE ".db::table('questionary_questionoption')." SET choices='$choices',choicerids='$choicerids' WHERE qoptionid='$qoptionid'");
				}
				DB::query("INSERT INTO ".db::table('questionary_questionchoicers')."(questid, questionid,uid,username,options,dateline) VALUES ('$questid', '$questionid','$uid','$username','$options','$dateline')");
			}
			//积分
			require_once libfile("function/credit");
			credit_create_credit_log($_G['uid'],"joinquestionnaire",$questid);
			
			//经验值
			require_once libfile("function/group");
			group_add_empirical_by_setting($_G['uid'],$_G[fid], 'questionnaire_join', $questionid);
			//feed
			require_once libfile('function/feed');
			$tite_data = array('username' => '<a href="home.php?mod=space&uid='.$uid.'">'.user_get_user_name_by_username($username).'</a>', 'questname' => '<a href="forum.php?mod='.$mod.'&action=plugin&fid='.$_G[fid].'&plugin_name=questionary&plugin_op=groupmenu&diy=&questid='.$questid.'&questionary_action=answer">'.$questname.'</a>');
			feed_add('questionary', 'feed_questionary_answer', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $_G['uid'], $username,$_G['fid']);
			
			DB::query("UPDATE ".db::table('questionary')." SET joiner='$joiner' WHERE questid='$questid' AND fid='$_G[fid]'");
			if($visible==0){
				showmessage('回答成功',join_plugin_action('index'));
			}else{
				showmessage('回答成功',join_plugin_action('questionarycp'));
			}
		}else{
		
		$query = DB :: query("SELECT * FROM " . DB :: table('questionary') . " WHERE questid='$questid' AND fid='$_G[fid]' LIMIT 1");
		$questionary = DB :: fetch($query);
		$questionary[questdescr]=discuzcode($questionary[questdescr],-1,0,1,1,1,1,1);
		if($questionary[visible]==1&&$_G['uid']!=$questionary[uid]){
			$query = DB::query("SELECT choicerids FROM ".DB::table('questionary_questionoption')." WHERE questid='$questid'");
			while($questionaryarray = DB::fetch($query)) {
				if(strexists("\t".$questionaryarray['choicerids']."\t", "\t".$_G['uid']."\t")) {
					 $url = "forum.php?mod=$mod&action=plugin&fid=".$_G['fid']."&plugin_name=questionary&plugin_op=groupmenu&questid=".$questid."&questionary_action=questionarycp";
					header("Location:".$url); 
				}
			}
		}else if($_G['uid']==$questionary[uid]){
		$query = DB::query("SELECT choicerids FROM ".DB::table('questionary_questionoption')." WHERE questid='$questid'");
			while($questionaryarray = DB::fetch($query)) {
				if(strexists("\t".$questionaryarray['choicerids']."\t", "\t".$_G['uid']."\t")) {
					$cananswer=1;
				}
			}
		}
		else{
			$query = DB::query("SELECT choicerids FROM ".DB::table('questionary_questionoption')." WHERE questid='$questid'");
			while($questionaryarray = DB::fetch($query)) {
				if(strexists("\t".$questionaryarray['choicerids']."\t", "\t".$_G['uid']."\t")) {
					showmessage('你已经答过了','forum.php?mod='.$mod.'&action=plugin&fid='.$_G[fid].'&plugin_name=questionary&plugin_op=groupmenu');
				}
			}
		}
		
			$query = DB :: query("SELECT * FROM " . DB :: table('questionary_question') . " WHERE questid='$questid'");
					while ($value = DB :: fetch($query)) {
						$key=$key+1;
						$questionlist[$key] = $value;
						$questionlist[$key]['questiontype'] =$value[multiple] ? 'checkbox' : 'radio';
						$questionlist[$key]['question']=discuzcode($questionlist[$key]['question'],-1,0,1,1,1,1,1);
						$questionid=$value['questionid'];
						$optionquery=DB :: query("SELECT * FROM " . DB :: table('questionary_questionoption') . " WHERE questionid='$questionid' order by qoptionid ASC");
						while($value = DB :: fetch($optionquery)){
							$questionlist[$key]['option'][]=$value;
						}
					}
					
					$isgroupuser=group_is_group_member($_G['fid'],$_G['uid']);
					//获得专区信息
			$query = DB::query("SELECT t.name, tt.icon FROM ".DB::table("forum_forum")." t, ".DB::table("forum_forumfield")." tt 
					 WHERE t.fid=tt.fid AND t.fid=".$_G['fid']);
			$group = DB::fetch($query);
			$group['icon'] = get_groupimg($group['icon'], 'icon');
		include template("questionary:answer");
		
		}
	}else{
		showmessage('您查看的问卷已被删除','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=questionary&plugin_op=groupmenu');
	}
dexit();
?>
