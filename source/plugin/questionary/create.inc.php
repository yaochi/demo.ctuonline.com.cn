<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");



function index(){
    global  $_G;
	require_once libfile("function/forum");
    loadforum();
    $editorid = 'e';
	$editormode = 0;
    $_G['group']['allowpostattach'] = 1;
    $editor = array("editormode" => 1, "allowswitcheditor" => 1, "allowhtml" => 0, "allowsmilies" => 1,
        "allowbbcode" => 1, "allowimgcode" => 1, "allowcustombbcode" => 1, "allowresize" => 1,
        "textarea" => "message");
    $allowpostimg = 0;
    $_G['group']['allowpostattach'] = 0;
    $allowuploadnum = 1;
    $maxattachsize_mb = $_G['group']['maxattachsize'] / 1048576 >= 1 ? round(($_G['group']['maxattachsize'] / 1048576), 1) . 'MB' : round(($_G['group']['maxattachsize'] / 1024)) . 'KB';
    $_G['group']['attachextensions'] = implode(",", array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
    $albumlist = array();
    if ($_G['uid']) {
        $query = DB::query("SELECT albumid, albumname, picnum FROM " . DB::table('home_album') . " WHERE uid='$_G[uid]' ORDER BY updatetime DESC");
        while ($value = DB::fetch($query)) {
            if ($value['picnum']) {
                $albumlist[] = $value;
            }
        }
    }
    $fid = $_GET["fid"];
    $query = DB::query("SELECT * FROM ".DB::table("questionary_class")." WHERE fid=".$fid);
    $questionaryclass = array();
    while ($questionaryclass = DB::fetch($query)) {
    	$questionaryclasses[] = $questionaryclass;
    }
	 require_once libfile("function/category");
    $is_enable_category = false;
    $pluginid = $_GET["plugin_name"];
	if(common_category_is_enable($_G["fid"], $pluginid)){
        $is_enable_category = true;
        $categorys = common_category_get_category($_G["fid"], $pluginid);
    }
    $required=common_category_is_required($_G["fid"], $pluginid);
    return array("questionaryclasses"=>$questionaryclasses, "editor"=>$editor, "editorid"=>$editorid, "_G"=>$_G,"categorys"=>$categorys, "is_enable_category"=>$is_enable_category,'albumlist'=>$albumlist,'required'=>$required);
}

function new_class(){
	require_once libfile('function/home');
		$_POST['classname'] = empty($_POST['classname'])?'':getstr($_POST['classname'], 40, 1, 1);
		if(empty($_POST['classname'])){
			showmessage("名称不为空，请重新填写", join_plugin_action('to_new_class'));
		}
			DB::insert("questionary_class", array("classname"=>$_POST["classname"],
			"fid"=>$_POST["fid"],
			"dateline"=>$_G['timestamp'],
			uid=>$_G['uid']));
			showmessage('创建成功', join_plugin_action('index'));		
}


function to_new_class(){
	$fid = $_GET["fid"];
	return array("fid"=>$fid);
}

function create(){
require_once libfile('function/home');
	global $_G;
	$mod=$_GET['mod'];
	
	$anonymity=$_POST['anonymity'];
	if(!$anonymity){
		$anonymity=$_G[member][repeatsstatus];
	}
	if($anonymity>0){
		include_once libfile('function/repeats','plugin/repeats');
		$repeatsinfo=getforuminfo($anonymity);
	}
	
	$_POST['questdescr']=$_POST['message'];
	$_POST['questname'] = empty($_POST['questname'])?'':getstr($_POST['questname'], 100, 1, 1);
	if(empty($_POST['questname'])){
		showmessage("问卷标题不能为空",join_plugin_action('index'));
	}
	$_POST['questdescr'] = empty($_POST['questdescr'])?'':getstr($_POST['questdescr'], 5000, 1, 0);
	if(empty($_POST['questdescr'])){
		showmessage("简介不能为空",join_plugin_action('index'));
	}
	$pluginid = $_GET["plugin_name"];
    require_once libfile("function/category");
    $other = common_category_is_other($_G["fid"], $pluginid);
    if($other["state"]=='Y' && $other["required"]=='Y' && !isset($_POST["category"])){
        showmessage('请选择类型', join_plugin_action("index"));
    }
	$setarr = array();
	$setarr['questname'] = $_POST['questname'];
	$setarr['questdescr'] = $_POST['questdescr'];
	$setarr['visible'] = $_POST['visible'];
	$setarr['classid'] = $_POST['category'];
	$setarr['scored'] = $_POST['scored'];
	$setarr['uid'] = $_G['uid'];
	$setarr['username'] = $_G['username'];
	$setarr['fid'] = $_G['fid'];
	$setarr['dateline']= $_G['timestamp'];
	$questid = DB::insert('questionary', $setarr, 1);
	
	hook_create_resource($questid,'question',$_G[fid]);
	//积分
	require_once libfile("function/credit");
	credit_create_credit_log($_G['uid'],"createquestionnaire",$questid);
	
	//经验值
	require_once libfile("function/group");
	group_add_empirical_by_setting($_G['uid'],$_G[fid], 'create_questionary', $questid);
	
	
	//发送动态
	if($_POST["issend"]==1) {
		require_once libfile('function/feed');
			if($repeatsinfo){
				$tite_data = array('username' => '<a class="perPanel" href="forum.php?mod=group&fid='.$repeatsinfo['fid'].'">'.$repeatsinfo['name'].'</a>', 'questname' => '<a href="forum.php?mod='.$mod.'&action=plugin&fid='.$setarr['fid'].'&plugin_name=questionary&plugin_op=groupmenu&diy=&questid='.$questid.'&questionary_action=answer">'.$setarr['questname'].'</a>');
			}else{
				$tite_data = array('username' => '<a class="perPanel"  href="home.php?mod=space&uid='.$setarr['uid'].'">'.user_get_user_name_by_username($setarr['username']).'</a>', 'questname' => '<a href="forum.php?mod='.$mod.'&action=plugin&fid='.$setarr['fid'].'&plugin_name=questionary&plugin_op=groupmenu&diy=&questid='.$questid.'&questionary_action=answer">'.$setarr['questname'].'</a>');
			}
		feed_add('questionary', 'feed_questionary', $tite_data, '', array(), '', array(), array(), '', '', '', 0, $questid, 'questid', $_G['uid'], $username,$_G['fid'],array(),'',0,0,$anonymity);
	}
	$url="forum.php?mod=$mod&action=plugin&fid=".$_G['fid']."&plugin_name=questionary&plugin_op=groupmenu&questid=".$questid."&questionary_action=upload";
	showmessage('创建成功，请添加问题',join_plugin_action('insert_question',array('questid'=>$questid)));
}

function insert_question(){
	require_once libfile('function/discuzcode');
	require_once libfile("function/forum");
    global  $_G;
    loadforum();
    $editorid = 'e';
	$editormode = 0;
    $_G['group']['allowpostattach'] = 1;
    $editor = array("editormode" => 1, "allowswitcheditor" => 1, "allowhtml" => 0, "allowsmilies" => 1,
        "allowbbcode" => 1, "allowimgcode" => 1, "allowcustombbcode" => 1, "allowresize" => 1,
        "textarea" => "message");
    $allowpostimg = 0;
    $_G['group']['allowpostattach'] = 0;
    $allowuploadnum = 1;
    $maxattachsize_mb = $_G['group']['maxattachsize'] / 1048576 >= 1 ? round(($_G['group']['maxattachsize'] / 1048576), 1) . 'MB' : round(($_G['group']['maxattachsize'] / 1024)) . 'KB';
    $_G['group']['attachextensions'] = implode(",", array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
    $albumlist = array();
    if ($_G['uid']) {
        $query = DB::query("SELECT albumid, albumname, picnum FROM " . DB::table('home_album') . " WHERE uid='$_G[uid]' ORDER BY updatetime DESC");
        while ($value = DB::fetch($query)) {
            if ($value['picnum']) {
                $albumlist[] = $value;
            }
        }
    }
	$mod=$_GET['mod'];
	$questid = $_GET['questid'];
	$query = DB :: query("SELECT * FROM " . DB :: table('questionary') . " WHERE questid='$questid' AND fid='$_G[fid]' LIMIT 1");
	$questionary = DB :: fetch($query);
	$questionary[questdescr]=discuzcode($questionary[questdescr]);
	$query = DB :: query("SELECT * FROM " . DB :: table('questionary_question') . " WHERE questid='$questid'");
			while ($value = DB :: fetch($query)) {
				$key=$key+1;
				$questionlist[$key] = $value;
				$questionlist[$key]['questiontype'] =$value[multiple] ? 'checkbox' : 'radio';
				$questionlist[$key]['question']=discuzcode($questionlist[$key]['question']);
				$questionid=$value['questionid'];
				$optionquery=DB :: query("SELECT * FROM " . DB :: table('questionary_questionoption') . " WHERE questionid='$questionid' order by qoptionid ASC");
				while($value = DB :: fetch($optionquery)){
					$questionlist[$key]['option'][]=$value;
				}
			}
    return array("questid"=>$questid,"questionary"=>$questionary,"questionlist"=>$questionlist,"editor"=>$editor, "editorid"=>$editorid, "_G"=>$_G,"mod"=>$mod,'albumlist'=>$albumlist);
}

function insert(){
require_once libfile('function/home');
	$_POST['question']=$_POST['message'];
	$questid=$_GET['questid'];
  	 $_POST['question'] = empty($_POST['question'])?'':getstr($_POST['question'], 5000, 1, 1);
		if(empty($_POST['question'])){
			showmessage("问题不能为空");
		}
		$_POST['questiondescr'] = empty($_POST['questiondescr'])?'':getstr($_POST['questiondescr'], 5000, 1, 1);
		$questionoption=$_POST['questionoption'];
		$weight=$_POST['weight'];
		$questoptiondescr=$_POST['questoptiondescr'];
		foreach($questionoption as $key => $value) {
			$questionoption[$key] = censor($questionoption[$key]);
			if(trim($value) === '') {
				unset($questionoption[$key]);
				unset($weight[$key]);
				unset($questoptiondescr[$key]);
			}
		}
		
		if(count($questionoption) < 2){
			showmessage("问题选项最少问两个");
		}
		foreach($weight as $key => $value){
			if(preg_match("/^\d*$/", trim($value))) {
			}else{
				showmessage('对不起，分数只支持数字，请返回修改');
			}
		}
		if(preg_match("/^\d*$/", trim($_POST['maxchoices']))) {
			}else{
				showmessage('对不起，最大可选项只支持数字，请返回修改');
			}
		
		$curquestionoption = count($questionoption);
		$questionarray['maxchoices'] = empty($_POST['maxchoices']) ? 1 : ($_POST['maxchoices']> $curquestionoption ? $curquestionoption : $_POST['maxchoices']);
		$questionarray['multiple'] = empty($_POST['maxchoices']) || $_POST['maxchoices'] == 1 ? 0 : 1;
		$questionarray['options'] = $questionoption;
		$questionarray['weight'] = $weight;
		$questionarray['questoptiondescr'] = $questoptiondescr;
		
		DB::query("INSERT INTO ".DB::table('questionary_question')." (question, questiondescr, multiple, maxchoices, questid)
			VALUES ('$_POST[question] ', '$_POST[questiondescr]', '$questionarray[multiple]', '$questionarray[maxchoices]', '$questid')");
		$questionid=DB::insert_id();

		foreach($questionarray['options'] as $key=>$polloptvalue) {
			$polloptvalue = dhtmlspecialchars(trim($polloptvalue));
			$weights=$questionarray['weight'][$key];
			$questoptiondescrs=$questionarray['questoptiondescr'][$key];
			DB::query("INSERT INTO ".DB::table('questionary_questionoption')." (questionid, questionoption,weight,questid,descr) VALUES ('$questionid','$polloptvalue','$weights','$questid','$questoptiondescrs')");
		}
		
		showmessage('问题添加成功',join_plugin_action('insert_question',array('questid'=>$questid)));
	}
	
function delete(){
	$questionid=$_GET['questionid'];
	$questid=$_GET['questid'];
	DB :: query("DELETE FROM " . DB :: table('questionary_question') . " WHERE questionid='$questionid'");
	DB :: query("DELETE FROM " . DB :: table('questionary_questionoption') . " WHERE questionid='$questionid'");
	DB :: query("DELETE FROM " . DB :: table('questionary_questionchoicers') . " WHERE questionid='$questionid'");
	showmessage("删除成功",join_plugin_action('insert_question',array('questid'=>$questid)));
}

function edit(){
require_once libfile('function/discuzcode');
	global  $_G;
	$questionid=$_GET['questionid'];
	$questid=$_GET['questid'];
	$query = DB :: query("SELECT * FROM " . DB :: table('questionary_question') . " WHERE questionid='$questionid'");
			while ($value = DB :: fetch($query)) {
				$question= $value;
				$optionquery=DB :: query("SELECT * FROM " . DB :: table('questionary_questionoption') . " WHERE questionid='$questionid' order by qoptionid ASC");
				while($value = DB :: fetch($optionquery)){
						$questionoption[]=$value;
				}
			}
	require_once libfile("function/forum");
    loadforum();
    $editorid = 'e';
	$editormode = 0;
    $_G['group']['allowpostattach'] = 1;
    $editor = array("editormode" => 1, "allowswitcheditor" => 1, "allowhtml" => 0, "allowsmilies" => 1,
        "allowbbcode" => 1, "allowimgcode" => 1, "allowcustombbcode" => 1, "allowresize" => 1,
        "textarea" => "message",'value'=>$question[question]);
    $allowpostimg = 0;
    $_G['group']['allowpostattach'] = 0;
    $allowuploadnum = 1;
    $maxattachsize_mb = $_G['group']['maxattachsize'] / 1048576 >= 1 ? round(($_G['group']['maxattachsize'] / 1048576), 1) . 'MB' : round(($_G['group']['maxattachsize'] / 1024)) . 'KB';
    $_G['group']['attachextensions'] = implode(",", array('jpg', 'jpeg', 'gif', 'png', 'bmp'));
    $albumlist = array();
    if ($_G['uid']) {
        $query = DB::query("SELECT albumid, albumname, picnum FROM " . DB::table('home_album') . " WHERE uid='$_G[uid]' ORDER BY updatetime DESC");
        while ($value = DB::fetch($query)) {
            if ($value['picnum']) {
                $albumlist[] = $value;
            }
        }
    }
	$query = DB :: query("SELECT * FROM " . DB :: table('questionary') . " WHERE questid='$questid' AND fid='$_G[fid]' LIMIT 1");
	$questionary = DB :: fetch($query);
	$questionary[questdescr]=discuzcode($questionary[questdescr]);
	$query = DB :: query("SELECT * FROM " . DB :: table('questionary_question') . " WHERE questid='$questid' and questionid !='$questionid' ");
			while ($value = DB :: fetch($query)) {
				$key=$key+1;
				$questionlist[$key] = $value;
				$questionid=$value['questionid'];
				$questionlist[$key]['questiontype'] =$value[multiple] ? 'checkbox' : 'radio';
				$questionlist[$key]['question']=discuzcode($questionlist[$key]['question']);
				$optionquery=DB :: query("SELECT * FROM " . DB :: table('questionary_questionoption') . " WHERE questionid='$questionid' order by qoptionid ASC");
				while($value = DB :: fetch($optionquery)){
					$questionlist[$key]['option'][]=$value;
				}
			}
    return array("questid"=>$questid,"questionid"=>$_GET['questionid'],"questionary"=>$questionary,"questionlist"=>$questionlist,"questionoption"=>$questionoption,"editor"=>$editor, "editorid"=>$editorid,"question"=>$question, "_G"=>$_G,'albumlist'=>$albumlist);

}
function editend(){
require_once libfile('function/home');
	global  $_G;
	$_POST['question']=$_POST['message'];
	$questionid=$_GET['questionid'];
	$questid=$_GET['questid'];
	$questionoptid='';
	$query = DB::query("SELECT qoptionid FROM ".DB::table('questionary_questionoption')." WHERE questionid='$questionid'");
	while($tempoptid = DB::fetch($query)) {
			$questionoptid[] = $tempoptid['qoptionid'];
		}
	$question = empty($_POST['question'])?'':getstr($_POST['question'], 5000, 1, 1);
	if(empty($question)){
			showmessage("问题不能为空");
		}
	$questiondescr = empty($_POST['questiondescr'])?'':getstr($_POST['questiondescr'], 5000, 1, 1);
	$questionoption=$_POST['questionoption'];
	$weight=$_POST['weight'];
	$questionoptionid=$_POST['questionoptionid'];
	$questoptiondescr=$_POST['questoptiondescr'];
	foreach($questionoption as $key => $value) {
	$questionoption[$key] = censor($questionoption[$key]);
		if(trim($value) === '') {
			DB::query("DELETE FROM ".DB::table('questionary_questionoption')." WHERE qoptionid='$questionoptionid[$key]'");
				unset($questionoption[$key]);
				unset($weight[$key]);
				unset($questionoptionid[$key]);
				unset($questoptiondescr[$key]);
				}
			}
			if(count($questionoption) < 2){
				showmessage("问题选项最少问两个");
			}
			foreach($weight as $key => $value){
			if(preg_match("/^\d*$/", trim($value))) {
			}else{
				showmessage('对不起，分数只支持数字，请返回修改');
			}
		}
			if(preg_match("/^\d*$/", trim($_POST['maxchoices']))) {
			}else{
				showmessage('对不起，最大可选项只支持数字，请返回修改');
			}
			$curquestionoption = count($questionoption);
			$maxchoices = empty($_POST['maxchoices']) ? 1 : ($_POST['maxchoices']> $curquestionoption ? $curquestionoption : $_POST['maxchoices']);
			$multiple = empty($_POST['maxchoices']) || $_POST['maxchoices'] == 1 ? 0 : 1;
			$questionarray['options'] = $questionoption;
			$questionarray['weight'] = $weight;
			$questionarray['questoptiondescr'] = $questoptiondescr;
				
			DB::query("UPDATE ".db::table('questionary_question')." SET question='$question',questiondescr='$questiondescr',multiple='$multiple',maxchoices='$maxchoices' WHERE questionid='$questionid'");
			foreach($questionarray['options'] as $key=>$polloptvalue) {
				$polloptvalue = dhtmlspecialchars(trim($polloptvalue));
				$weights=$questionarray['weight'][$key];
				$questoptiondescrs=$questionarray['questoptiondescr'][$key];
				if(in_array($questionoptionid[$key], $questionoptid)) {
					DB::query("UPDATE ".db::table('questionary_questionoption')." SET questionoption='$polloptvalue',weight='$weights',descr='$questoptiondescrs' WHERE qoptionid='$questionoptionid[$key]'");
				}else{
					DB::query("INSERT INTO ".DB::table('questionary_questionoption')." (questionid, questionoption,weight,questid,descr) VALUES ('$questionid', '$polloptvalue','$weights','$questid','$questoptiondescrs')");
				}
			}
				showmessage('问题编辑成功',join_plugin_action('insert_question',array('questid'=>$questid)));

}


?>
