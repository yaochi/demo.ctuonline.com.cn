<?php
/*
 * Created on 2011-11-30
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	if(!defined('IN_DISCUZ'))
	{
		exit('Access Denied');
	}
	 require_once(dirname(dirname(dirname(dirname(__FILE__))))."/function/function_discuzcode.php");

	define('DISCUZ_CORE_FUNCTION', true);

function getSuggests(){
		global $_G;
		$orderby=$_GET["orderby"];
		if($orderby==""){
			$sort=" order by createdate desc";
		}else if($orderby=="timeasc"){
				$sort=" order by createdate asc";
		}else if($orderby=="viewnum"){
				$sort=" order by views desc";
		}else if($orderby=="replynum"){
			$sort=" order by respond desc";
		}
		$sug=$_REQUEST["sug"];
		$name=$_REQUEST["name"];
		$username=$_REQUEST["username"];
		if($sug!=null||$sug!=""){
			$search.=" and suggest like '%".$sug."%'";
		}
		if($name!=null||$name!=""){
			$search.=" and name like '%".$name."%'";
		}
       if($username!=null||$username!=""){
			$search.=" and regname= '".$username."'";

		}

		if($_G['forum']['ismoderator']==1){
		$perpage = 20;
		$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
		$start = ($page -1) * $perpage;
		$sql="SELECT * FROM pre_suggestbox where  fid=".$_G[fid]." and status=1".$search." or fid= ".$_G[fid]." and status=0 and uid=".$_G['uid'].$search.$sort." LIMIT $start,$perpage";
		$info = DB::query($sql);
		$obj=array();
		if($info==False)
		{
			return  0;
		}
		else
		{
	        while ($value = DB::fetch($info))
	        {

				$obj[]=$value;

			}

		$getcount = DB :: result_first("SELECT count(*) FROM " . DB :: table('suggestbox') . " WHERE fid=" . $_G['fid']." and status=1".$search." or fid= ".$_G[fid]." and status=0 and uid=".$_G['uid'].$search);
		$url = "forum.php?mod=" . $_G['mod'] . "&action=plugin&fid=" . $_G['fid'] . "&plugin_name=suggestbox&plugin_op=groupmenu&orderby=".$orderby."&sug=".$sug."&name=".$name."&username=".$username;
		if ($getcount) {
			$multipage = multi($getcount, $perpage, $page, $url);
		}
		return array (
			"multipage" =>$multipage,
			"obj" =>$obj,
			'mod' =>  $_G['mod'],
			"getcount" => $getcount,
			'sug' =>$sug,
			'name'=>$name,
			'username'=>$username
		);
		}
		}if($_G['forum']['ismoderator']==0){

		$perpage = 10;
		$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
		$start = ($page -1) * $perpage;

		$regname=getRegname();
		$sql="SELECT * FROM pre_suggestbox where  fid=".$_G[fid]." AND regname='".$regname."' and status!=2".$search.$sort." LIMIT $start,$perpage";
		$info = DB::query($sql);
		$obj=array();
		if($info==False)
		{
			return  0;
		}
		else
		{
	        while ($value = DB::fetch($info))
	        {

				$obj[]=$value;

			}

		$getcount = DB :: result_first("SELECT count(*) FROM " . DB :: table('suggestbox') . " WHERE fid=" . $_G['fid']." AND regname='".$regname."' and status!=2".$search);
		$url = "forum.php?mod=" . $_G['mod'] . "&action=plugin&fid=" . $_G['fid'] . "&plugin_name=suggestbox&plugin_op=groupmenu&orderby=".$orderby."&sug=".$sug."&name=".$name."&username=".$username;
		if ($getcount) {
			$multipage = multi($getcount, $perpage, $page, $url);
		}
		return array (
			"multipage" =>$multipage,
			"obj" =>$obj,
			'mod' =>  $_G['mod'],
			"getcount" => $getcount,
			'sug' =>$sug,
			'name'=>$name,
			'username'=>$username
		);
		}
	}
}
function getSuggest($suggestid){
		global $_G;
		$sql="SELECT * FROM pre_suggestbox where  fid=".$_G[fid]." AND suggestId=".$suggestid;

		$info = DB::query($sql);

		$value = DB::fetch($info);

		return $value;
	}
function getReply($suggestid){
			global $_G;

		$sql="SELECT * FROM pre_opinion_reply where  fid=".$_G[fid]." AND optionid=".$suggestid." and tap=1";

		$info = DB::query($sql);

		$obj=array();
		if($info==False)
		{
			return  0;
		}
		else
		{
	        while ($value = DB::fetch($info))
	        {

				$obj[]=viewoption_procpost($value);
			}
		 }

			return array (
			"obj" =>$obj,
			'mod' =>  $_G['mod'],
		     );
}
function getCommonReply($suggestid){
			global $_G;
				$perpage = 6;
		$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
		$start = ($page -1) * $perpage;
		$sql="SELECT * FROM pre_opinion_reply where  fid=".$_G[fid]." AND optionid=".$suggestid." and tap=2 order by replydateline desc"." LIMIT $start,$perpage";

		$info = DB::query($sql);

		$obj=array();
		if($info==False)
		{
			return  0;
		}
		else
		{
	        while ($value = DB::fetch($info))
	        {

				$obj[]=viewoption_procpost($value);

			}
		}


			$getcount = DB :: result_first("SELECT count(*) FROM " . DB :: table('opinion_reply') . " WHERE fid=" . $_G['fid']." AND optionid=".$suggestid." and tap=2");
		$url = "forum.php?mod=" . $_G['mod'] . "&action=plugin&fid=" . $_G['fid'] . "&plugin_name=suggestbox&plugin_op=groupmenu&suggestbox_action=view&suggestid=".$suggestid;
		if ($getcount) {
			$multipage = multi($getcount, $perpage, $page, $url);
		}

			return array (
			"multipage" =>$multipage,
			"obj" =>$obj,
			'mod' =>  $_G['mod'],
			"getcount" => $getcount,
		     );
}
function getPassSuggest(){
      global $_G;
      		$sug=$_REQUEST["sug"];
		$name=$_REQUEST["name"];
		$username=$_REQUEST["username"];
		$keywords=$_REQUEST["keywords"];
		if($sug!=null||$sug!=""){
			$search.=" and suggest like '%".$sug."%'";
		}
		if($name!=null||$name!=""){
			$search.=" and name like '%".$name."%'";
		}
       if($username!=null||$username!=""){
			$search.=" and regname= '".$username."'";

		}
		if($keywords!=null||$keywords!=""){
			$search.=" and suggest like '%".$keywords."%' or name like '%".$keywords."%' ";
		}
      		$orderby=$_GET["orderby"];
		$perpage = 20;
		$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
		$start = ($page -1) * $perpage;
		if($orderby==""){
			$sort=" order by passdate desc";
		}else if($orderby=="timeasc"){
				$sort=" order by passdate asc";
		}else if($orderby=="viewnum"){
				$sort=" order by views desc";
		}else if($orderby=="replynum"){
			$sort=" order by respond desc";
		}
		$assort=$_REQUEST["assort"];
		if($assort=="my"){
			$search.=" and uid=".$_G['uid'];
		}
		$sql="SELECT * FROM pre_suggestbox where  fid=".$_G[fid]." and status=2".$search.$sort." LIMIT $start,$perpage";
		$info = DB::query($sql);
		$obj=array();
		if($info==False)
		{
			return  0;
		}
		else
		{
	        while ($value = DB::fetch($info))
	        {

				$obj[]=$value;

			}

		$getcount = DB :: result_first("SELECT count(*) FROM " . DB :: table('suggestbox') . " WHERE fid=" . $_G['fid']." and status=2".$search);
		$url = "forum.php?mod=" . $_G['mod'] . "&action=plugin&fid=" . $_G['fid'] . "&plugin_name=suggestbox&plugin_op=groupmenu&suggestbox_action=passsuggest&orderby=".$orderby."&sug=".$sug."&name=".$name."&username=".$username;
		if ($getcount) {
			$multipage = multi($getcount, $perpage, $page, $url);
		}
		return array (
			"multipage" =>$multipage,
			"obj" =>$obj,
			'mod' =>  $_G['mod'],
			"getcount" => $getcount,
			'sug' =>$sug,
			'name'=>$name,
			'username'=>$username
		);
		}
}
function getRegname(){
		global $_G;
		$sql="SELECT username FROM  pre_common_member where  uid=".$_G['uid'];
		$info = DB::query($sql);
			$value = DB::fetch($info);

		return $value['username'];

	 }
function getRealname(){
		global $_G;
		$sql="SELECT realname FROM  pre_common_member_profile where  uid=".$_G['uid'];
		$info = DB::query($sql);
			$value = DB::fetch($info);

		return $value['realname'];

}
function viewoption_procpost($post) {
    global $_G;
    $post['replmessage'] = discuzcode($post['replmessage'], '', '', '', $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'], ($_G['forum']['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $_G['forum']['allowhtml'], ($_G['forum']['jammer'] && $post['authoreruid'] != $_G['uid'] ? 1 : 0), 0, $post['authoreruid'], 1, $post['id']);

    return $post;
 }
// function openFileAPIcompany($url) {
//	$opts = array (
//		'http' => array (
//			'method' => 'GET',
//			'timeout' => 300000,
//		)
//	);
//	$context = @ stream_context_create($opts);
//	$result = file_get_contents($url, false, $context);
//	return $result;
//}
function getcompanyinfo($regname){
	global $_G;
	$FILE_SEARCH_PAGE = "http://" . $_G[config][expert][activeurl];
	$FILE_SEARCH_PAGE .= "/api/user/getuserprovinceorg.do?regname=" . $regname;
	$str1 = openFileAPIcompany($FILE_SEARCH_PAGE);
	$filejson = json_decode($str1, true);
	return $filejson[groupname];
}

function updatestatus($sugestid,$status){//修改意见的状态
	global $_G;
	$sql="update pre_suggestbox set status=".$status." where suggestId=".$sugestid;
	DB::query($sql);
}


/**
 * 学习积分操作
 *
 *用户信息 uid-用户ID,regname-用户网大帐号,realname-用户真实姓名
 *type 1-学习激励 2-意见箱 3-学习力评估 4-有奖问卷
 *mode 1-新建 2-审核通过 3-证明奖励 4-奖励 5-其他
 *credit 积分>0
 *company 所在公司
 *optionid 如学习激励ID
 *
 *积分表pre_learn_credit,积分记录表pre_learncredit_record
 *
 *调用接口根据regname获取所在省公司getprovince($regname)
 *根据uid获取realname-user_get_user_name
 */
function op_learncredit($uid,$regname,$realname,$type,$mode,$optionid,$credit,$company){
	$info=DB :: fetch(DB :: query("select count(*) as count from pre_learn_credit where username='".$regname."'"));
	if($info[count]==0){
		//查找用户所在省公司
		$province=getcompanyinfo($regname);
		if($province!="") $company=$province;

		//查找用户真实姓名
		if($mode==3){
			$realname=user_get_user_name($uid);
		}
		$learn_credit=array('uid' => $uid,'username' => $regname,'realname' => $realname,'totalcredit' => $credit,'exchangecredit' => $credit,'company' => $company);
		DB :: insert("learn_credit", $learn_credit);
	} else {
		$up_sql="update pre_learn_credit set totalcredit=totalcredit+".$credit.",exchangecredit=exchangecredit+".$credit." where username='".$regname."'";
		DB :: query($up_sql);
	}
	//更新积分记录
	$learncredit_record=array('uid' => $uid,'username' => $regname,'type' => $type,'mode' => $mode,'objectid' => $optionid,'credit' => $credit,'dateline'=>time());
	DB :: insert("learncredit_record", $learncredit_record);
}
?>