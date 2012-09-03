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
 
	 require_once libfile("function/category");
    $is_enable_category = false;
    $pluginid = $_GET["plugin_name"];
	if(common_category_is_enable($_G["fid"], $pluginid)){
        $is_enable_category = true;
        $categorys = common_category_get_category($_G["fid"], $pluginid);
    }
    $required=common_category_is_required($_G["fid"], $pluginid);
    return array("selectionclasses"=>$selectionclasses, "editor"=>$editor, "editorid"=>$editorid, "_G"=>$_G,"categorys"=>$categorys, "is_enable_category"=>$is_enable_category,'albumlist'=>$albumlist,'required'=>$required);
}

function new_class(){
	require_once libfile('function/home');
		$_POST['classname'] = empty($_POST['classname'])?'':getstr($_POST['classname'], 40, 1, 1);
		if(empty($_POST['classname'])){
			showmessage("名称不为空，请重新填写", join_plugin_action('to_new_class'));
		}
			DB::insert("selection_class", array("classname"=>$_POST["classname"],
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
	$_POST['selectiondescr']=$_POST['message'];
	$_POST['selectionname'] = empty($_POST['selectionname'])?'':getstr($_POST['selectionname'], 100, 1, 1);
	if(empty($_POST['selectionname'])){
		showmessage("评选标题不能为空",join_plugin_action('index'));
	}
	$_POST['selectiondescr'] = empty($_POST['selectiondescr'])?'':getstr($_POST['selectiondescr'], 5000, 1, 0);
	if(empty($_POST['selectiondescr'])){
		showmessage("简介不能为空",join_plugin_action('index'));
	}
	$_POST['selectionstartdate'] = empty($_POST['selectionstartdate'])?'':strtotime($_POST['selectionstartdate']);
	$_POST['selectionenddate'] = empty($_POST['selectionenddate'])?'':strtotime($_POST['selectionenddate']);
	if(empty($_POST['selectionstartdate'])){
		showmessage("评选开始时间不能为空",join_plugin_action('index'));
	}
	if(empty($_POST['selectionenddate'])){
		showmessage("评选结束时间不能为空",join_plugin_action('index'));
	}
	$_POST['voteNum'] = empty($_POST['voteNum'])?'':$_POST['voteNum'];
	if(empty($_POST['voteNum'])){
		showmessage("评选投票数不能为空",join_plugin_action('index'));
	}
	$pluginid = $_GET["plugin_name"];
    require_once libfile("function/category");
    $other = common_category_is_other($_G["fid"], $pluginid);
    if($other["state"]=='Y' && $other["required"]=='Y' && !isset($_POST["category"])){
        showmessage('请选择类型', join_plugin_action("index"));
    }
	$setarr = array();
	$setarr['selectionname'] = $_POST['selectionname'];
	$setarr['selectiondescr'] = $_POST['selectiondescr'];
	$setarr['selectiondescr'] = addslashes($setarr['selectiondescr']);
	$setarr['selectionstartdate'] = $_POST['selectionstartdate'];
	$setarr['selectionenddate'] = $_POST['selectionenddate'];
	$setarr['voteNum'] = $_POST['voteNum'];
	$setarr['votecreatetime'] = $_POST['votecreatetime'];
	$setarr['votecreatetype'] = $_POST['votecreatetype'];
	$setarr['votebatchflag'] = empty($_POST['votebatchflag'])?0:$_POST['votebatchflag'];
	$setarr['voterepeatflag'] = empty($_POST['voterepeatflag'])?0:$_POST['voterepeatflag'];
	$setarr['showvoteflag'] = empty($_POST['showvoteflag'])?0:$_POST['showvoteflag'];
	$setarr['showrecordflag'] = empty($_POST['showrecordflag'])?0:$_POST['showrecordflag'];
 
	
                    $image_id = "imgurl";
					
					if(!empty($_POST['checkimg'])){
					
					 
                    // 判断文件上传HTTP是否有错
                    if($_FILES[$image_id]['error']){
                        $msg = "Error: ".$_FILES[$image_id]['error']."<br />";
                        showmessage($msg, $return_url);
                    }

                   // 文件上传处理
                   require_once libfile('class/upload');
                   $upload = new discuz_upload();

                   // 判断文件上传初始化是否成功
		   			if(!$upload->init($_FILES[$image_id], 'plugin_selection',$_G['fid'])){
                       $errorcode = "file_upload_error_".$upload->error();
                       $msg = errorMsg($errorcode);
                       //echo "init $msg";
                       //exit;
                       showmessage($msg, $return_url);
                   }

                   // 判断文件保存是否成功
                   if(!$upload->save()){
                       $errorcode = "file_upload_error_".$upload->error();
                       $msg = errorMsg($errorcode);
                       //echo "save $msg";
                       //exit;
                       showmessage($msg, $return_url);
                   }
                    $target = $upload->attach['target'];
                    $url_arr = explode("./",$target);
                    $url = $url_arr[1].$url_arr[2];
                   
                    $setarr["imgurl"] = $url;
                    }             

               
	//$setarr['imgurl'] = $_POST['imgurl'];
	
	$setarr['classid'] = $_POST['category'];
	$setarr['uid'] = $_G['uid'];
	$setarr['username'] = $_G['username'];
	$setarr['fid'] = $_G['fid'];
	$setarr['dateline']= $_G['timestamp'];
	$selectionid = DB::insert('selection', $setarr, 1);
	
	//hook_create_resource($questid,'question',$_G[fid]);
	//积分
	require_once libfile("function/credit");
	//credit_create_credit_log($_G['uid'],"createselection",$selectionid);
	
	//经验值
	require_once libfile("function/group");
	//group_add_empirical_by_setting($_G['uid'],$_G[fid], 'create_selection', $selectionid);
	
	
	//发送动态
	//add by qiaoyz,2011-3-24, EKSN-216 在专区中建评选活动后，不会在专区首页DIY的专区动态模块中显示出来
	//if($_POST["issend"]==1) {
		require_once libfile('function/feed');
			if($repeatsinfo){
				$tite_data = array('username' => '<a class="perPanel" href="forum.php?mod=group&fid='.$repeatsinfo['fid'].'">'.$repeatsinfo['name'].'</a>',  'questname' => '<a href="forum.php?mod='.$mod.'&action=plugin&fid='.$setarr['fid'].'&plugin_name=selection&plugin_op=groupmenu&diy=&selectionid='.$selectionid.'&selection_action=answer">'.$setarr['selectionname'].'</a>');
			}else{
				$tite_data = array('username' => '<a href="home.php?mod=space&uid='.$setarr['uid'].'">'.user_get_user_name_by_username($setarr['username']).'</a>', 'questname' => '<a href="forum.php?mod='.$mod.'&action=plugin&fid='.$setarr['fid'].'&plugin_name=selection&plugin_op=groupmenu&diy=&selectionid='.$selectionid.'&selection_action=answer">'.$setarr['selectionname'].'</a>');
				}
		feed_add('selection', 'feed_selection', $tite_data, '', array(), '', array(), array(), '', '', '', 0, $selectionid, 'selectionid', $_G['uid'], $username,$_G['fid'],array(),'',0,0,$anonymity);
	//}
	$url="forum.php?mod=$mod&action=plugin&fid=".$_G['fid']."&plugin_name=selection&plugin_op=createmenu&selectionid=".$selectionid."&selection_action=insertoption";
	showmessage('创建成功，请添加评选项',join_plugin_action('insertoption',array('selectionid'=>$selectionid)));
}

function insertoption(){
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
	$selectionid = $_GET['selectionid'];
	$query = DB :: query("SELECT * FROM " . DB :: table('selection') . " WHERE selectionid='$selectionid' AND fid='$_G[fid]' LIMIT 1");
	$selection = DB :: fetch($query);
	$selection[selectiondescr]=discuzcode($selection[selectiondescr]);
	
	$optionquery=DB :: query("SELECT * FROM " . DB :: table('selection_option') . " WHERE selectionid='$selectionid' order by ordernum ASC");
	while($value = DB :: fetch($optionquery)){
		$value[optiondescr] = discuzcode($value[optiondescr]);
		$optionlist[]=$value;
	}
	
	
	$query = DB ::  result_first("SELECT count(*) FROM " . DB :: table('selection_option') . " WHERE selectionid='$selectionid' AND fid='$_G[fid]'");
 
    return array("selectionid"=>$selectionid,"selection"=>$selection,"optionlist"=>$optionlist,"editor"=>$editor, "editorid"=>$editorid, "_G"=>$_G,"mod"=>$mod,'albumlist'=>$albumlist,"ordernum"=>$query);
}

function insertoptionend(){
	global  $_G;
	require_once libfile('function/home');
	$_POST['optiondescr']=$_POST['message'];
	$selectionid=$_GET['selectionid'];
  	$_POST['url'] = empty($_POST['url'])?'':getstr($_POST['url'], 5000, 1, 1);
	$_POST['optionlimitname'] = empty($_POST['orgname_input'])?'':getstr($_POST['orgname_input'], 5000, 1, 1);
	$_POST['optionlimitid'] = empty($_POST['orgname_input_id'])?0:$_POST['orgname_input_id'];
 
 	$option = array();
	$option['optiondescr'] = $_POST['optiondescr'];
	$option['optiondescr'] = addslashes($option['optiondescr']);
	$option['url'] = $_POST['url'];
	$option['optionlimitname'] = $_POST['orgname_input'];
	$option['optionlimitid'] = $_POST['orgname_input_id'];
	$option['optionname'] = empty($_POST['optionname'])?'':getstr($_POST['optionname'], 5000, 1, 1);
	if(!empty($_POST['ordertypeFlag'])){
		$option['ordertype'] = empty($_POST['ordertype'])?0:$_POST['ordertype'];
	}
	$option['selectionid'] = $selectionid;
	$option['ordernum']	= $_POST['ordernum'];
	$option['fid'] = $_G['fid'];
 
	$ordernum = $option['ordernum'];
 

	$optionid = DB::insert('selection_option', $option, 1);

	if($ordernum==0){
		$query = DB :: query("SELECT count(*) FROM " . DB :: table('selection_option') . " WHERE selectionid='$selectionid'");
		$num = DB :: fetch($query);
		//第一个选项
		if($num==1){
		}else{
			//后面的选项
			$query = DB :: query("update " . DB :: table('selection_option') . " set ordernum = ordernum + 1 WHERE selectionid='$selectionid' and ordernum>".$ordernum);
		}
	}
 
 	$query = DB :: query("SELECT * FROM " . DB :: table('selection') . " WHERE selectionid='$selectionid' AND fid='$_G[fid]' LIMIT 1");
	$selection = DB :: fetch($query);
	if(!empty($_POST['ordertypeFlag'])){
		$selection['ordertype'] = empty($_POST['ordertype'])?0:$_POST['ordertype'];
	}
	DB::update('selection', $selection, "selectionid=".$selectionid);

	$url="forum.php?mod=$mod&action=plugin&fid=".$_G['fid']."&plugin_name=selection&plugin_op=createmenu&selectionid=".$selectionid."&selection_action=insertoption";
	showmessage('候选项添加成功',join_plugin_action('insertoption',array('selectionid'=>$selectionid)));
	}
	
function delete(){
	$selectionid=$_GET['selectionid'];
	//DB :: query("DELETE FROM " . DB :: table('user_vote_num') . " WHERE selectionid='$selectionid'");
	//DB :: query("DELETE FROM " . DB :: table('selection_record') . " WHERE selectionid='$selectionid'");
	//DB :: query("DELETE FROM " . DB :: table('selection_option') . " WHERE selectionid='$selectionid'");
	//DB :: query("DELETE FROM " . DB :: table('selection') . " WHERE selectionid='$selectionid'");
	DB :: query("update " . DB :: table('selection') . " set moderated = -1 WHERE selectionid='$selectionid'");
	showmessage("删除成功","forum.php?mod=".$_GET['mod']."&action=plugin&fid=".$_GET['fid']."&plugin_name=selection&plugin_op=groupmenu");
}

function edit(){
	require_once libfile('function/discuzcode');
	global  $_G;
	$selectionid=$_GET['selectionid'];
	$query = DB :: query("SELECT * FROM " . DB :: table('selection') . " WHERE selectionid='$selectionid'");
	$selection = DB :: fetch($query);
	
	$selection[selectionstartdate]= dgmdate($selection[selectionstartdate]);
	$selection[selectionenddate]= dgmdate($selection[selectionenddate]);
	
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
	  require_once libfile("function/category");
    $is_enable_category = false;
    $pluginid = $_GET["plugin_name"];
	if(common_category_is_enable($_G["fid"], $pluginid)){
        $is_enable_category = true;
        $categorys = common_category_get_category($_G["fid"], $pluginid);
    }
    return array("selectionid"=>$_GET['selectionid'],"selection"=>$selection,"editor"=>$editor, "editorid"=>$editorid, "_G"=>$_G,'albumlist'=>$albumlist,"categorys"=>$categorys, "is_enable_category"=>$is_enable_category);

}
function editend(){
require_once libfile('function/home');
	global  $_G;
	$mod=$_GET['mod'];
	$selectionid=$_POST['selectionid'];
	$query = DB :: query("SELECT * FROM " . DB :: table('selection') . " WHERE selectionid='$selectionid'");
	$setarr = DB :: fetch($query);
	$_POST['selectiondescr']=$_POST['message'];
	$_POST['selectionname'] = empty($_POST['selectionname'])?'':getstr($_POST['selectionname'], 100, 1, 1);
	if(empty($_POST['selectionname'])){
		showmessage("评选标题不能为空",join_plugin_action('index'));
	}
	$_POST['selectiondescr'] = empty($_POST['selectiondescr'])?'':getstr($_POST['selectiondescr'], 5000, 1, 0);
	if(empty($_POST['selectiondescr'])){
		showmessage("简介不能为空",join_plugin_action('index'));
	}
	$_POST['selectionstartdate'] = empty($_POST['selectionstartdate'])?'':strtotime($_POST['selectionstartdate']);
	$_POST['selectionenddate'] = empty($_POST['selectionenddate'])?'':strtotime($_POST['selectionenddate']);
	if(empty($_POST['selectionstartdate'])){
		showmessage("评选开始时间不能为空",join_plugin_action('index'));
	}
	if(empty($_POST['selectionenddate'])){
		showmessage("评选结束时间不能为空",join_plugin_action('index'));
	}
	$_POST['voteNum'] = empty($_POST['voteNum'])?'':$_POST['voteNum'];
	if(empty($_POST['voteNum'])){
		showmessage("评选投票数不能为空",join_plugin_action('index'));
	}
	$pluginid = $_GET["plugin_name"];
    require_once libfile("function/category");
    $other = common_category_is_other($_G["fid"], $pluginid);
    if($other["state"]=='Y' && $other["required"]=='Y' && !isset($_POST["category"])){
        showmessage('请选择类型', join_plugin_action("index"));
    }
 
	$setarr['selectionname'] = $_POST['selectionname'];
	$setarr['selectiondescr'] = $_POST['selectiondescr'];
	$setarr['selectiondescr'] = addslashes($setarr['selectiondescr']);
	$setarr['selectionstartdate'] = $_POST['selectionstartdate'];
	$setarr['selectionenddate'] = $_POST['selectionenddate'];
	$setarr['voteNum'] = $_POST['voteNum'];
	$setarr['votecreatetime'] = $_POST['votecreatetime'];
	$setarr['votecreatetype'] = $_POST['votecreatetype'];
	$setarr['votebatchflag'] = empty($_POST['votebatchflag'])?0:$_POST['votebatchflag'];
	$setarr['voterepeatflag'] = empty($_POST['voterepeatflag'])?0:$_POST['voterepeatflag'];
	$setarr['showvoteflag'] = empty($_POST['showvoteflag'])?0:$_POST['showvoteflag'];
	$setarr['showrecordflag'] = empty($_POST['showrecordflag'])?0:$_POST['showrecordflag'];
 	$setarr['classid'] = $setarr['classid'];
	
                    $image_id = "imgurl";
 
					if(!empty($_POST['checkimg'])&&$_POST['checkimg']!=$setarr['imgurl']){
                    // 判断文件上传HTTP是否有错
                    if($_FILES[$image_id]['error']){
                        $msg = "Error: ".$_FILES[$image_id]['error']."<br />";
                        showmessage($msg, $return_url);
                    }

                   // 文件上传处理
                   require_once libfile('class/upload');
                   $upload = new discuz_upload();

                   // 判断文件上传初始化是否成功
		   			if(!$upload->init($_FILES[$image_id], 'plugin_selection',$_G['fid'])){
                       $errorcode = "file_upload_error_".$upload->error();
                       $msg = errorMsg($errorcode);
                       //echo "init $msg";
                       //exit;
                       showmessage($msg, $return_url);
                   }

                   // 判断文件保存是否成功
                   if(!$upload->save()){
                       $errorcode = "file_upload_error_".$upload->error();
                       $msg = errorMsg($errorcode);
                       //echo "save $msg";
                       //exit;
                       showmessage($msg, $return_url);
                   }
                    $target = $upload->attach['target'];
                    $url_arr = explode("./",$target);
                    $url = $url_arr[1].$url_arr[2];
                  
                    $setarr["imgurl"] = $url;
                    }             

               
	//$setarr['imgurl'] = $_POST['imgurl'];
	
	$setarr['classid'] = $_POST['category'];
 	DB::update('selection', $setarr, 'selectionid='.$selectionid);
 
	
	//hook_create_resource($questid,'question',$_G[fid]);
	//积分
	require_once libfile("function/credit");
	//credit_create_credit_log($_G['uid'],"createselection",$selectionid);
	
	//经验值
	require_once libfile("function/group");
	//group_add_empirical_by_setting($_G['uid'],$_G[fid], 'create_selection', $selectionid);
	
	
	//发送动态
	/*if($_POST["issend"]==1) {
		require_once libfile('function/feed');
				$tite_data = array('username' => '<a href="home.php?mod=space&uid='.$setarr['uid'].'">'.user_get_user_name_by_username($setarr['username']).'</a>', 'questname' => '<a href="forum.php?mod='.$mod.'&action=plugin&fid='.$setarr['fid'].'&plugin_name=selection&plugin_op=groupmenu&diy=&questid='.$questid.'&selection_action=answer">'.$setarr['questname'].'</a>');
		feed_add('selection', 'feed_selection', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $_G['uid'], $username);
	}*/
	$optionnum = DB :: result_first("SELECT count(*) FROM " . DB :: table('selection_option') . " WHERE selectionid='$selectionid'");
	if($optionnum>0){
		$url="forum.php?mod=$mod&action=plugin&fid=".$_G['fid']."&plugin_name=selection&plugin_op=createmenu&selectionid=".$selectionid."&selection_action=updateoption";
	}else{
		$url="forum.php?mod=$mod&action=plugin&fid=".$_G['fid']."&plugin_name=selection&plugin_op=createmenu&selectionid=".$selectionid."&selection_action=insertoption";
	}
	
	showmessage('编辑成功',$url);

}

function updateoption(){
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
	$selectionid = $_GET['selectionid'];
	$query = DB :: query("SELECT * FROM " . DB :: table('selection') . " WHERE selectionid='$selectionid' AND fid='$_G[fid]' LIMIT 1");
	$selection = DB :: fetch($query);
	$selection[selectiondescr]=discuzcode($selection[selectiondescr]);
	
	$optionquery=DB :: query("SELECT * FROM " . DB :: table('selection_option') . " WHERE selectionid='$selectionid' order by ordernum ASC");
	while($value = DB :: fetch($optionquery)){
		$value[optiondescrbase] = $value[optiondescr];
		$value[optiondescrbase] = addslashes($value[optiondescr]);
		
		$value[optiondescr] = discuzcode($value[optiondescr]);
		//echo("===========".$value[optiondescr]);
		
		$optionlist[]=$value;
	}
	
	$query = DB :: query("SELECT * FROM " . DB :: table('selection_option') . " WHERE selectionid='$selectionid' AND fid='$_G[fid]' AND ordernum = 0 LIMIT 1");
	$firstoption =  DB :: fetch($query);
	
 
    return array("selectionid"=>$selectionid,"selection"=>$selection,"optionlist"=>$optionlist,"editor"=>$editor, "editorid"=>$editorid, "_G"=>$_G,"mod"=>$mod,'albumlist'=>$albumlist,"ordernum"=>$query,"firstoption"=>$firstoption);
}

function updateoptionend(){
	global  $_G;
	require_once libfile('function/home');
	$_POST['optiondescr']=$_POST['message'];
	$selectionid=$_GET['selectionid'];
	$optionid = $_POST['optionid'];
 
	
	$query = DB :: query("select * from " . DB :: table('selection_option') . " WHERE selectionid='$selectionid' and optionid='".$optionid."'");
	$option = DB :: fetch(query);

  	$_POST['url'] = empty($_POST['url'])?'':getstr($_POST['url'], 5000, 1, 1);
	$_POST['optionlimitname'] = empty($_POST['orgname_input'])?'':getstr($_POST['orgname_input'], 5000, 1, 1);
	
	
	$_POST['optionlimitid'] = empty($_POST['orgname_input_id'])?0:$_POST['orgname_input_id'];
	$option['optiondescr'] = $_POST['optiondescr'];
	$option['optiondescr'] = addslashes($option['optiondescr']);
	$option['url'] = $_POST['url'];
	$option['optionlimitname'] = $_POST['orgname_input'];
	if(empty($_POST['optionlimitname'])){
		$option['optionlimitid'] = '';
	}else{
		$option['optionlimitid'] = $_POST['orgname_input_id'];
	}
	
	$option['ordertype'] = empty($_POST['ordertype'])?0:$_POST['ordertype']; 
	$option['ordernum']	= $_POST['ordernum'];
	$option['optionname'] = empty($_POST['optionname'])?'':getstr($_POST['optionname'], 5000, 1, 1);
 	if(!empty($_POST['ordertypeFlag'])){
		$option['ordertype'] = empty($_POST['ordertype'])?0:$_POST['ordertype'];
	}else{
		$option['ordertype'] = 0;
	}
 
	$ordernum = $option['ordernum'];
 

	DB::update('selection_option', $option,"optionid=".$optionid);

			//后面的选项
	$query = DB :: query("update " . DB :: table('selection_option') . " set ordernum = ordernum + 1 WHERE selectionid='$selectionid' and ordernum>".$ordernum);
	
 
 	$query = DB :: query("SELECT * FROM " . DB :: table('selection') . " WHERE selectionid='$selectionid' AND fid='$_G[fid]' LIMIT 1");
	$selection = DB :: fetch($query);
	if(!empty($_POST['ordertypeFlag'])){
		$selection['ordertype'] = empty($_POST['ordertype'])?0:$_POST['ordertype'];
	}else{
		$selection['ordertype'] = 0;
	}
 
	DB::update('selection', $selection, "selectionid=".$selectionid);
 	
	showmessage('候选项编辑成功',join_plugin_action('updateoption',array('selection'=>$selection)));
	}
	
function deleteoption(){
	$optionid = $_POST['deloptionid'];	
	$query = DB :: query("select * FROM " . DB :: table('selection_option') . " WHERE optionid='$optionid'");
	$option = DB :: fetch($query);
	$selectionid = $option['selectionid'];
	$ordernum = $option['ordernum'];
	DB :: query("update " . DB :: table('selection_option') . " set ordernum = ordernum-1 WHERE ordernum>".$ordernum);
	DB :: query("DELETE FROM " . DB :: table('selection_option') . " WHERE optionid='$optionid'");
	
	$optionnum = DB :: result_first("SELECT count(*) FROM " . DB :: table('selection_option') . " WHERE selectionid='$selectionid'");
	if($optionnum>0){
		showmessage('删除成功',join_plugin_action('updateoption',array('selection'=>$selection)));
	}else{
		showmessage('删除成功',join_plugin_action('insertoption',array('selection'=>$selection)));
	}
	
}

function showanswer(){
	$selectionid = $_GET['selectionid'];
	$query = DB :: query("SELECT * FROM " . DB :: table('selection') . " WHERE selectionid='$selectionid'");
	$selection = DB :: fetch($query);
	
	$query = DB :: query("SELECT * FROM " . DB :: table('selection_option') . " WHERE selectionid='$selectionid' order by scored desc");
	$outputdata = "西藏电信公司“2011年度工作会特邀代表”投票结果统计表\t\n";
	$outputdata.= "hello"."\t";
	$outputdata.= "world"."\t";
	$outputdata.= "\t\n";
	$outputdata .= "本次参与投票总人数"."\t".""."\t".$selection['scored']."\t".""."\t"."\t\n";
	$outputdata .= "排名"."\t"."姓名"."\t"."票数"."\t"."百分比"."\t"."\t\n";
	$i = 1;
	while($value = DB :: fetch($query)){
		$optionlist[]=$value;
		if($selection['scored']==0){
			$outputdata .= $i."\t".$value['optionname']."\t".$value['scored']."\t"."0"."\t"."\t\n";
		}else{
			$outputdata .= $i."\t".$value['optionname']."\t".$value['scored']."\t".$value['scored']/$selection['scored']."\t"."\t\n";
		}
		
		$i = $i+1;
	}
	
	header("Content-Type: application/vnd.ms-execl");
	header("Content-Disposition: attachment; filename=myExcel.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	//$outputdata = iconv( "UTF-8", "gb2312",$outputdata);
	//showmessage($outputdata);
	echo($outputdata);
	exit();

} 
?>
