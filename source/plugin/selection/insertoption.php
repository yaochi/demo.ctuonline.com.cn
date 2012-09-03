<?php
/* Function: 更新问卷
 * Com.:
 * Author: yangy
 * Date: 2010-7-13
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/home');
require_once libfile('function/space');
require_once libfile('function/spacecp');

$questid = empty($_GET['questid'])?0:intval($_GET['questid']);
$questionid = empty($_GET['questionid'])?0:intval($_GET['questionid']);
if($_GET['op']=='del'){
	DB :: query("DELETE FROM " . DB :: table('questionary') . " WHERE questid='$questid'");
	DB :: query("DELETE FROM " . DB :: table('questionary_question') . " WHERE questid='$questid'");
	DB :: query("DELETE FROM " . DB :: table('questionary_questionoption') . " WHERE questid='$questid'");
	DB :: query("DELETE FROM " . DB :: table('questionary_questionchoicers') . " WHERE questid='$questid'");
	hook_delete_resource($questid,'question');
	showmessage("删除成功",join_plugin_action('index',array(questid=>'')));
}else if($_GET['op']=='delquestion'){
	DB :: query("DELETE FROM " . DB :: table('questionary_question') . " WHERE questionid='$questionid'");
	DB :: query("DELETE FROM " . DB :: table('questionary_questionoption') . " WHERE questionid='$questionid'");
	DB :: query("DELETE FROM " . DB :: table('questionary_questionchoicers') . " WHERE questionid='$questionid'");
	
	showmessage("删除成功",join_plugin_action('upload',array(questid=>$questid,op=>'')));
}else{
$query = DB :: query("SELECT * FROM " . DB :: table('questionary') . " WHERE questid='$questid' AND fid='$_G[fid]' LIMIT 1");
$questionary = DB :: fetch($query);
require_once libfile("function/forum");
    loadforum();
    $editorid = 'e';
	$editormode = 0;
    $_G['group']['allowpostattach'] = 1;
    $editor = array("editormode" => 1, "allowswitcheditor" => 1, "allowhtml" => 0, "allowsmilies" => 1,
        "allowbbcode" => 1, "allowimgcode" => 1, "allowcustombbcode" => 1, "allowresize" => 1,
        "textarea" => "message",'value'=>$questionary[questdescr]);
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
	$query = DB :: query("SELECT * FROM " . DB :: table('questionary_question') . " WHERE questid='$questid'");
			while ($value = DB :: fetch($query)) {
				$key=$key+1;
				$questionlist[$key] = $value;
				$questionid=$value['questionid'];
				$optionquery=DB :: query("SELECT * FROM " . DB :: table('questionary_questionoption') . " WHERE questionid='$questionid' order by qoptionid ASC");
				while($value = DB :: fetch($optionquery)){
					$questionlist[$key]['option'][]=$value;
				}
			}
$query = DB::query("SELECT * FROM ".DB::table("questionary_class")." WHERE fid=".$_G[fid]);
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
include template("questionary:upload");
}

dexit();
?>
