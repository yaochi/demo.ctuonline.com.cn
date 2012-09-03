<?php


/* Function: 上传文档, 移动分类, 删除文档, 编辑文档, 编辑文档分类, 删除文档分类
 * Com.:
 * Author: wuhan
 * Date: 2010-8-4
 */

if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}

require_once libfile('function/doc');

$docid = empty ($_GET['docid']) ? 0 : intval($_GET['docid']);
$op = empty ($_GET['op']) ? '' : $_GET['op'];

if ($_GET['op'] == 'delete') {
	if (submitcheck("deletesubmit")) {
//		$doc = getFile($docid);	
		
		if (deleteFiles($_G['uid'], array (
				$docid
			))) {
				
//			if($doc && !empty($doc['id'])){
//				if($doc['userid'] != $_G['uid']){
//					notification_add($doc['userid'], 'doc', 'doc_delete', array('actor' => "<a href=\"home.php?mod=space&uid=$doc[userid]\" target=\"_blank\">$doc[username]</a>", 'title' => $doc['title']), 1);
//				}
//				//删除积分
//				require_once libfile('function/credit');
//				credit_create_credit_log($doc['userid'], 'deletedocument');
//			}
			
			showmessage('do_success', dreferer());
		} else {
			showmessage('failed_to_delete_operation');
		}
	}
}
elseif ($_GET['op'] == 'change') {
	$classarr = array();
	$query = DB::query("SELECT classid, classname FROM ".DB::table('home_doc_class')." WHERE uid='$space[uid]'");
	
	$classarr["1"] = "未分类";
	while ($value = DB::fetch($query)) {
		$classarr[$value['classid']] = $value['classname'];
	}
	$doc = getFile($docid);
	if (submitcheck("changesubmit")) {
		$c_type = $_POST['type'];
		$classid = 0;
		if ($c_type == 1) { //新建分类
			$_POST['classname'] = getstr($_POST['classname'], 40, 1, 1, 1);
			if(strlen($_POST['classname']) < 1) {
				showmessage('enter_the_correct_class_name');
			}
			$classname = $_POST['classname'];
			if($classname == "未分类"){
				$classid = 1;
			}else{
				$classid = DB::result(DB::query("SELECT classid FROM ".DB::table('home_doc_class')." WHERE classname='$classname' AND uid='$_G[uid]'"));
				if(empty($classid)){
					$setarr = array (
						'classname' => $classname,
						'uid' => $_G['uid'],
						'dateline' => $_G['timestamp']
					);
					$classid = DB :: insert('home_doc_class', $setarr, 1);
				}
			}
		} else { //使用已有分类
			$classid = intval($_POST['classid']);
		}

		if (changeFolder($_G['uid'], $classid, array (
				$docid
			))) {
			showmessage('do_success', dreferer());
		} else {
			showmessage('failed_to_change_doc_operation');
		}
	}

}
elseif ($_GET['op'] == 'edit') {
	showmessage('do_success', "http://".$_G['config']['misc']['resourcehost']."/WebRoot/resource.do?m=edit&resId=$docid&ac=redirect&tmp=$_G[timestamp]");
}elseif ($_GET['op'] == 'select') {
	$classarr = array();
	$query = DB::query("SELECT classid, classname FROM ".DB::table('home_doc_class')." WHERE uid='$space[uid]'");
	
	$classarr["1"] = "未分类";
	while ($value = DB::fetch($query)) {
		$classarr[$value['classid']] = $value['classname'];
	}
	
	if (submitcheck("changesubmit")) {
		$c_type = $_POST['type'];
		$classid = 0;
		if ($c_type == 1) { //新建分类
			$_POST['classname'] = getstr($_POST['classname'], 40, 1, 1, 1);
			if(strlen($_POST['classname']) < 1) {
				showmessage('enter_the_correct_class_name');
			}
			$classname = $_POST['classname'];
			if($classname == "未分类"){
				$classid = 1;
			}else{
				$classid = DB::result(DB::query("SELECT classid FROM ".DB::table('home_doc_class')." WHERE classname='$classname' AND uid='$_G[uid]'"));
				if(empty($classid)){
					$setarr = array (
						'classname' => $classname,
						'uid' => $_G['uid'],
						'dateline' => $_G['timestamp']
					);
					$classid = DB :: insert('home_doc_class', $setarr, 1);
				}
			}
		} else { //使用已有分类
			$classid = intval($_POST['classid']);
		}

		showmessage('do_success', "http://".$_G['config']['misc']['resourcehost']."/WebRoot/upload.do?m=index&folderid=$classid&ac=redirect&tmp=$_G[timestamp]");
	}
	
}elseif ($_GET['op'] == 'editclass') {
	$classid = empty($_GET['classid'])?0:intval($_GET['classid']);
	
	$class = array();
	if($classid) {
		$query = DB::query("SELECT * FROM ".DB::table('home_doc_class')." WHERE classid='$classid' AND uid='$_G[uid]'");
		$class = DB::fetch($query);
	}
	if(empty($class)) showmessage('did_not_specify_the_type_of_operation');
	
	if (submitcheck("editsubmit")) {
		$_POST['classname'] = getstr($_POST['classname'], 40, 1, 1, 1);
		
		if(strlen($_POST['classname']) < 1) {
			showmessage('enter_the_correct_class_name');
		}
		
		$classname = $_POST['classname'];
		
		$query = DB::query("SELECT * FROM ".DB::table('home_doc_class')." WHERE classname='$classname' AND uid='$_G[uid]'");
		$sameclass = DB::fetch($query);
		if($sameclass || $classname == '未分类'){
			showmessage('same_class_name');
		}
		
		
		DB::update('home_doc_class', array('classname' => $classname), array('classid' => $classid, 'uid' => $_G['uid']));

		showmessage('do_success', dreferer(),array('classid'=>$classid), array('showdialog' => 1, 'showmsg' => true, 'closetime' => 1));
	}
}
elseif ($_GET['op'] == 'deleteclass') {
	$classid = empty($_GET['classid'])?0:intval($_GET['classid']);
	
	$class = array();
	if($classid) {
		$query = DB::query("SELECT * FROM ".DB::table('home_doc_class')." WHERE classid='$classid' AND uid='$_G[uid]'");
		$class = DB::fetch($query);
	}
	if(empty($class)) showmessage('did_not_specify_the_type_of_operation');
	
	if(submitcheck("deletesubmit")) {
		DB::query("DELETE FROM ".DB::table('home_doc_class')." WHERE classid='$classid'");
		
		changeAllDocFolder($classid, "1");

		showmessage('do_success', dreferer());
	}
}

include_once template("home/spacecp_doc");

function changeAllDocFolder($classid, $newclassid){
	global $_G;
	
	include_once libfile('function/doc');
	
	$page = 1;
	$pagesize = 500;
	
	$filejson = getFileList(1, 1, $_G['uid'], $classid);
	$count = 0;
	if($filejson){
		$count = $filejson['totalAmount'];
	}
	
	if($count){
		while(($page - 1) * $pagesize <= $count){
			$filejson = getFileList($pagesize, $page, $_G['uid'], $classid);
			$list = $docids = array();
			if($filejson){
				$list = $filejson['resources'];
			}
			if($list){
				foreach($list as $doc){
					$docids[] = $doc['id'];
				}
				
				changeFolder($_G['uid'], $newclassid, $docids);
			}
			$page++;
		}
	}
}
?>
