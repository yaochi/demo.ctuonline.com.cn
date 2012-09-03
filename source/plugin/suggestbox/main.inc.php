<?php
/*
 * Created on 2011-11-29
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

 function index(){
 global $_G;
 require_once (dirname(__FILE__)."/function/function_suggestbox.php");
 $suggests=getSuggests();
 return array("suggests"=>$suggests[obj],"multipage"=>$suggests[multipage],"sug"=>$suggests['sug'],"name"=>$suggests[name],"username"=>$suggests[username]);
 }

  function create(){
 		global $_G;
		$sql="select pro.realname as realname,mem.username as regname from pre_common_member mem, pre_common_member_profile pro where mem.uid=".$_G['uid']." and  pro.uid=".$_G['uid'];
		$info = DB::query($sql);
			$value = DB::fetch($info);

		return array("info"=>$value);
 }

 function passsuggest(){

 global $_G;
 require_once (dirname(__FILE__)."/function/function_suggestbox.php");
 $suggests=getPassSuggest();
 return array("suggests"=>$suggests[obj],"multipage"=>$suggests[multipage],"sug"=>$suggests['sug'],"name"=>$suggests[name],"username"=>$suggests[username]);

 }
  function saveRespond(){
     global $_G;
     require_once (dirname(__FILE__)."/function/function_suggestbox.php");
     $suggest=getSuggest($_G['gp_suggestid']);
     $authorer=getRegname();
        $sql="update pre_suggestbox set respond=respond+1 where suggestId=".$suggest['suggestId'];

     DB::query($sql);
  DB::insert("opinion_reply", array("uid"=>$suggest['uid'],
      "username"=>$suggest['regname'],
      "realname"=>$suggest['name'],
      "replydateline"=>time(),
      "authoreruid"=>$_G['uid'],
      "authorer"=>$authorer,
      "replmessage"=>$_POST["replmessage"],
      "type"=>1,
      "optionid"=>$suggest['suggestId'],
      "fid"=>$_G["fid"],
      "tap"=>2));
  $url="forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=$_G[gp_plugin_name]&plugin_op=groupmenu&suggestbox_action=view&suggestid=".$suggest['suggestId'];
  showmessage('提交建议成功', $url);
 }
 function validate(){
 global $_G;
  require_once (dirname(__FILE__)."/function/function_suggestbox.php");
  $regname=getRegname();
  $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
 $data=array("regname"=>$regname);
 $validate=json_encode($data);
  echo "$callback($validate)";
  dexit();
 }

 //发送短消息
function sengSuggestMessage(){
	global $_G;

	 require_once (dirname(__FILE__)."/function/function_suggestbox.php");
	$touid=$_G['gp_uid'];//$_G['gp_uid'];//发送消息用户的id
	$subjectid=$_G['gp_suggestid'];//建议id
	$subject=$_G['gp_suggest'];//$_G['gp_suggest'];//建议
	$subject=substr($subject,0,20);

	$arr= array('object' => '<a href="forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=suggestbox&plugin_op=groupmenu&suggestbox_action=sugrepond&suggestid='.$subjectid.'">'.$subject.'</a>');
	$msg=notification_add($touid, 'suggestbox', '[意见箱]管理员对您提出的建议“'.$arr[object].'”提出了完善建议，赶快去看看吧',array(), 1);
	updatestatus($subjectid,0);

	$suggest = getSuggest($subjectid);
	$authorer = getRegname();
	$reply=$_G["gp_replmessage"];
	$isadmin=0;
	$uid=$suggest['uid'];
	if($reply!=""||$reply!=null){

	DB :: insert("opinion_reply", array (
		"uid" =>$uid ,
		"username" => $suggest['regname'],
		"realname" => $suggest['name'],"isadmin"=>$isadmin,
	"replydateline" => time(), "authoreruid" => $_G['uid'], "authorer" => $authorer, "replmessage" => $reply, "type" => 1, "optionid" => $suggest['suggestId'], "fid" => $_G["fid"], "tap" => 1));
	}

    $url="forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=$_G[gp_plugin_name]&plugin_op=groupmenu&suggestbox_action=index";
  showmessage('发送提醒成功！', $url);


}
 function view(){
 	global $_G;

	$suggestid=$_G['gp_suggestid'];
	 	$sql="update pre_suggestbox set views=views+1 where suggestId=".$suggestid;

		 DB::query($sql);
		  $replynum=DB::query("select count(*) as countreply from pre_opinion_reply where tap=2 and optionid=".$suggestid);
		 $countreply = DB::fetch($replynum);
	require_once (dirname(__FILE__)."/function/function_suggestbox.php");
	$suggest=viewoption_proc(getSuggest($suggestid));
	$reply=getCommonReply($suggestid);
	$repl=getReply($suggestid);
	return array("suggest"=>$suggest,"replys"=>$reply['obj'],"countreply"=>$countreply['countreply'],"multipage"=>$reply[multipage],"repl"=>$repl['obj']);
 }
 function viewoption_proc($post) {
    global $_G;
    $post['review'] = discuzcode($post['review'], '', '', '', $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'], ($_G['forum']['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $_G['forum']['allowhtml'], ($_G['forum']['jammer'] && $post['authoreruid'] != $_G['uid'] ? 1 : 0), 0, $post['authoreruid'], 1, $post['id']);

    return $post;
  }
  function sugrepond(){
  		global $_G;
	$suggestid=$_G['gp_suggestid'];
	 	$sql="update pre_suggestbox set views=views+1 where suggestId=".$suggestid;
		 DB::query($sql);
	require_once (dirname(__FILE__)."/function/function_suggestbox.php");
	$suggest=getSuggest($suggestid);
	$reply=getReply($suggestid);
	if($suggest['status']==2){
	$url = "forum.php?mod=group&action=plugin&fid=".$_G[fid]."&plugin_name=suggestbox&plugin_op=groupmenu&suggestbox_action=view&suggestid=".$suggestid;
     header("Location:".$url);
	}
	return array("suggest"=>$suggest,"replys"=>$reply['obj']);


  }

?>
