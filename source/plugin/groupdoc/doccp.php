<?php
/* Function: 上传文档
 * Com.:
 * Author: wuhan
 * Date: 2010-8-4
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$docid = empty($_GET['docid'])?0:intval($_GET['docid']);
$op = empty($_GET['op'])?'':$_GET['op'];

if ($_GET['op'] == 'delete') {
	if (submitcheck("deletesubmit")) {
		$doc = getFile($docid);
		if (deleteFiles($_G['uid'], array (
				$docid
			))) {
				
			if($doc && !empty($doc['id'])){
				if($doc['userid'] != $_G['uid']){
					notification_add($doc['userid'], 'doc', 'gdoc_delete', array('actor' => "<a href=\"home.php?mod=space&uid=$doc[userid]\" target=\"_blank\">$doc[username]</a>", 'title' => $doc['title'], 'group' => "<a href=\"forum.php?mod=group&fid=$_G[fid]\" target=\"_blank\">".$_G['forum']['name']."</a>"), 1);
				}
				//删除文档积分
			//	require_once libfile('function/credit');
				//credit_create_credit_log($doc['userid'], 'deletedocument');		
			}
			
			showmessage('do_success', dreferer());
		} else {
			showmessage('failed_to_delete_operation');
		}
	}
}
elseif ($_GET['op'] == 'change') {
	//分类
	require_once libfile("function/category");
	$pluginid = $_GET["plugin_name"];
	$allowedittype = common_category_is_enable($_G['fid'], $pluginid);
	$categorys = array();
	if($allowedittype){
		$categorys = common_category_get_category($_G['fid'], $pluginid);
	}
	else{
		showmessage('undefined_action');
	}
	$doc = getFile($docid);
	if (submitcheck("changesubmit")) {
		$c_type = $_POST['type'];
		$classid = intval($_POST['typeid']);
		
	    $allowrequired = common_category_is_required($_G['fid'], 'groupdoc');
		if(empty($classid) && $allowrequired){
			showmessage('select_a_type');
		}
		
		$doc = getFile($docid);
		if(empty($doc) || empty($doc['id'])){
			showmessage('view_to_info_did_not_exist');
		}

		if ($doc['folderid'] == $classid || changeFolder($_G['uid'], $classid, array ($docid))) {
			if($doc['userid'] != $_G['uid']){
				require_once libfile("function/feed");
				$note_type = 'doc';
				$note = 'gdoc_type';
				$note_values = array('group' => "<a href=\"forum.php?mod=group&fid=$_G[fid]\" target=\"_blank\">".$_G['forum']['name']."</a>", 'title'=>"$doc[title]", 'actor' => "<a href=\"home.php?mod=space&uid=$doc[userid]\" target=\"_blank\">$doc[username]</a>", 'type1' => $categorys[$doc['folderid']]['name'], 'type2' => $categorys[$classid]['name']);
				
				notification_add($doc['userid'], $note_type, $note, $note_values);
			}
			showmessage('do_success', dreferer());
		} else {
			showmessage('failed_to_change_doc_operation');
		}
	}

}
elseif ($_GET['op'] == 'edit') {
	showmessage('do_success', "http://".$_G['config']['misc']['resourcehost']."/WebRoot/resource.do?m=edit&resId=$docid&ac=redirect&tmp=$_G[timestamp]");
	
}elseif ($_GET['op'] == 'upload') {
	
}else{
	//分类
	require_once libfile("function/category");
	$pluginid = $_GET["plugin_name"];
	$allowedittype = common_category_is_enable($_G['fid'], $pluginid);
	$allowrequired = common_category_is_required($_G['fid'], 'groupdoc');
		
	$categorys = array();
	if($allowedittype){
		$categorys = common_category_get_category($_G['fid'], $pluginid);
		//开启分类的时候，判断是否一定需要归类
		if($allowrequired){
			//showmessage('select_a_type');
		}
	}
	else{
		showmessage('do_success', "http://".$_G['config']['misc']['resourcehost']."/WebRoot/upload.do?m=index&folderid=0&zoneid=$_G[fid]&ac=redirect");
	}
	
	if (submitcheck("selectsubmit")) {
		$c_type = $_POST['type'];
		$classid = intval($_POST['typeid']);

		if(empty($classid) && $allowrequired){
			showmessage('select_a_type');
		}

		showmessage('do_success', "http://".$_G['config']['misc']['resourcehost']."/WebRoot/upload.do?m=index&folderid=$classid&zoneid=$_G[fid]&ac=redirect");
	}
}

include template("groupdoc:doccp");

dexit();
?>
