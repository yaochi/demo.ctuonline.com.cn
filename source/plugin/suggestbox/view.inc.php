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
	$suggestid=$_G['gp_suggestid'];
	 	$sql="update pre_suggestbox set views=views+1 where suggestId=".$suggestid;

		 DB::query($sql);
//		 $replynum=DB::query("select count(*) as countreply from pre_opinion_reply where tap=1 and optionid=".$suggestid);
//		 $countreply = DB::fetch($replynum);
	require_once (dirname(__FILE__)."/function/function_suggestbox.php");
	$suggest=getSuggest($suggestid);
	$reply=getReply($suggestid);
	return array("suggest"=>$suggest,"replys"=>$reply['obj']);
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
?>
