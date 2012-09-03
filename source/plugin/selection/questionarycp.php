<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once libfile('function/discuzcode');
$questid = empty($_GET['questid'])?0:intval($_GET['questid']);
$query=DB::query("SELECT count(1) as c FROM ".DB::TABLE("questionary")." WHERE questid='$questid' AND fid='$_G[fid]' AND moderated!='-1'");
$count = DB::fetch($query);
$count = $count["c"];
if($count){
	$query = DB :: query("SELECT * FROM " . DB :: table('questionary') . " WHERE questid='$questid' AND fid='$_G[fid]' LIMIT 1");
	$questionary = DB :: fetch($query);
	$questionary[questdescr]=discuzcode($questionary[questdescr]);
	$colors = array('E92725', 'F27B21', 'F2A61F', '5AAF4A', '42C4F5', '0099CC', '3365AE', '2A3591', '592D8E', 'DB3191');
	$questionaryscore=0;
		$query = DB :: query("SELECT * FROM " . DB :: table('questionary_question') . " WHERE questid='$questid'");
		$num= DB :: result_first("SELECT count(*) FROM " . DB :: table('questionary_question') . " WHERE questid='$questid'");
				while ($value = DB :: fetch($query)) {
					$key=$key+1;
					$ci = 0;
					$totalscore=0;
					$questionlist[$key] = $value;
					$questionlist[$key]['questiontype'] =$value[multiple] ? 'checkbox' : 'radio';
					$questionlist[$key]['question']=discuzcode($questionlist[$key]['question']);
					$questionid=$value['questionid'];
					$optionquery=DB :: query("SELECT * FROM " . DB :: table('questionary_questionoption') . " WHERE questionid='$questionid' order by qoptionid ASC");
					$count= DB :: fetch_first("SELECT SUM(choices) AS total FROM ". DB :: table('questionary_questionoption') ." WHERE questionid='$questionid'");
					while($value = DB :: fetch($optionquery)){
						$questionlist[$key]['option'][]=$value;
						$questionlist[$key]['percent'][]=@sprintf("%01.2f", $value['choices'] * 100 / $count['total']);
						$questionlist[$key]['width'][]=$value['choices'] > 0 ? (@round($value['choices'] * 100 / $count['total'])).'%' : '8px';
						$questionlist[$key]['colors'][]=$colors [$ci++];
						$totalscore=$totalscore+$value['choices'] * $value['weight'];
					}
					$questionscore=@sprintf("%01.1f", $totalscore/ $count['total']);
					$questionlist[$key]['questionscore']=$questionscore;
					$questionaryscore=$questionaryscore+$questionscore;
				}
				$prescore=@sprintf("%01.1f", $questionaryscore/$num);
				
	//获得专区信息
		$query = DB::query("SELECT t.name, tt.icon FROM ".DB::table("forum_forum")." t, ".DB::table("forum_forumfield")." tt 
				 WHERE t.fid=tt.fid AND t.fid=".$_G['fid']);
		$group = DB::fetch($query);
		$group['icon'] = get_groupimg($group['icon'], 'icon');
		
	include template("questionary:questionarycp");
}else{
	showmessage('您查看的问卷已被删除','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=questionary&plugin_op=groupmenu');
}
dexit();
?>
