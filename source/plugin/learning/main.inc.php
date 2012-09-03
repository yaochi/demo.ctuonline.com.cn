<?php
/*
 * Created on 2011-11-29
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once (dirname(dirname(dirname(__FILE__))) . "/joinplugin/pluginboot.php");



function optionview(){
global $_G;
require_once (dirname(__FILE__) . "/function/function_learning.php");
	$learnid = $_GET[learnid];
	$status=getlearstatus($learnid);
	if($status==3){
    $url = "forum.php?mod=group&action=plugin&fid=" . $_G['fid'] . "&plugin_name=learning&plugin_op=groupmenu&learning_action=achieview&learnid=".$learnid."&tap=2";
   	header("Location:".$url);
	}
	$tap =  intval($_GET[tap])? intval($_GET[tap]):2;
	$examstatus=intval($_GET[examinestatus]) ? intval($_GET[examinestatus]):2;
	$learnmv = getlearnmv($learnid);
	/**
	 * 学习来源，学习收获，学习行动 学习成果建议的获取
	 * */
	$learsorcearr= learingoption($learnid,1);
	$learnharvestarr= learingoption($learnid,2);
	$learnactiontarr= learingoption($learnid,3);
	$learnachievarr= learingoption($learnid,4);
	$learnnamearr= learingoption($learnid,5);

	$attachments = learngetattachs($learnid,1);
	$attachlist = $attachments['attachs'];
    $sorceimagelist = $attachments['imgattachs'];
	$attaharchments = learngetattachs($learnid,2);//学习收获的附件
	$attharachlist = $attaharchments['attachs'];
	$harestimagelist = $attaharchments['imgattachs'];
	$attchmentsaction = learngetattachs($learnid,3);//学习行为的附件
	$attharachlistaction = $attchmentsaction['attachs'];
	$imagelistaction = $attchmentsaction['imgattachs'];
	$attchmentshievements = learngetattachs($learnid,4);//学习成果的附件
	$attharachlisthievements = $attchmentshievements['attachs'];
	$imagelistevements = $attchmentshievements['imgattachs'];
	$learsorcearr= learingoption($learnid,1);
	$learnharvestarr= learingoption($learnid,2);
	$learnactiontarr= learingoption($learnid,3);
	$learnachievarr= learingoption($learnid,4);
	$learnnamearr= learingoption($learnid,5);
//	$imagelist = $attachments['imgattachs'];

	return array (
		"learnmv" => $learnmv,
		'tap' => $tap,
		'examstatus'=>$examstatus,
		'learsorcearr'=>$learsorcearr,
		'learnharvestarr'=>$learnharvestarr,
		'learnactiontarr'=>$learnactiontarr,
		'learnachievarr'=>$learnachievarr,
		'learnnamearr'=>$learnnamearr,
		'attachments'=>$attachments,
		'attachlist'=>$attachlist,
		'attharachlist'=>$attharachlist,
		'attharachlistaction'=>$attharachlistaction,
		'attharachlisthievements'=>$attharachlisthievements,

	);

}
function achieview() {
	global $_G;
	$learnid = $_GET['learnid'];
	$tap = $_GET['tap'];
	$perpage = 20;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page -1) * $perpage;
	require_once (dirname(__FILE__) . "/function/function_learning.php");
	$learnmv = getlearnmv($learnid);
	$replynum = viewreplynum($learnid,$tap);
	$sql = "select * from pre_opinion_reply where learnmvid=" . $learnid . " and tap=2  ORDER BY replydateline desc  LIMIT $start,$perpage";
	$query = DB :: query($sql);
	$getcount = replyoptioncount($learnid,$tap);
	$url = "forum.php?mod=" . $_G['mod'] . "&action=plugin&fid=" . $_G['fid'] . "&plugin_name=learning&plugin_op=groupmenu&learning_action=achieview&learnid=".$learnid."&tap=".$tap;
	if ($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}
	if($query==false){
		return 0;
	}else {
		while ($value=DB::fetch($query)) {
              $arrreply[]=$value;
		}
	}
	foreach($arrreply as $id => $post) {
		$arrreply[$id] = viewoption_procpost($post);
	}
	$attachments = learngetattachs($learnid,1);
	$attachlist = $attachments['attachs'];
    $sorceimagelist = $attachments['imgattachs'];

	$attaharchments = learngetattachs($learnid,2);//学习收获的附件
	$attharachlist = $attaharchments['attachs'];
	$harestimagelist = $attaharchments['imgattachs'];

	$attchmentsaction = learngetattachs($learnid,3);//学习行为的附件
	$attharachlistaction = $attchmentsaction['attachs'];
	$imagelistaction = $attchmentsaction['imgattachs'];

	$attchmentshievements = learngetattachs($learnid,4);//学习成果的附件
	$attharachlisthievements = $attchmentshievements['attachs'];
	$imagelistevements = $attchmentshievements['imgattachs'];
	$learsorcearr= learingoption($learnid,1);
	$learnharvestarr= learingoption($learnid,2);
	$learnactiontarr= learingoption($learnid,3);
	$learnachievarr= learingoption($learnid,4);
	$learnnamearr= learingoption($learnid,5);
    DB::query("update pre_learning_excitation set pageviews=pageviews+1 where id=".$learnid);
	return array (
		"learnmv" => $learnmv,
		"replynum" => $replynum,
		"arryreply" =>$arrreply,
		'tap' => $tap,
		'learnid'=>$learnid,
		'username'=>$learnmv[subusername],
		'multipage'=>$multipage,
		'attachlist'=>$attachlist,
		'attharachlist'=>$attharachlist,
		'attharachlistaction'=>$attharachlistaction,
		'attharachlisthievements'=>$attharachlisthievements,
		 'learsorcearr'=>$learsorcearr,
        'learnharvestarr'=>$learnharvestarr,
        'learnactiontarr'=>$learnactiontarr,
        'learnachievarr'=>$learnachievarr,
        'learnnamearr'=>$learnnamearr
	);
}
function replyoptioncount($learnid,$tap){
	$query = DB::query("select count(*) from pre_opinion_reply where learnmvid=" . $learnid." and tap=".$tap."");
	return DB::result($query);
}
function viewoption_procpost($post) {
global $_G;
$post['replmessage'] = discuzcode($post['replmessage'], '', '', '', $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'], ($_G['forum']['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $_G['forum']['allowhtml'], ($_G['forum']['jammer'] && $post['authoreruid'] != $_G['uid'] ? 1 : 0), 0, $post['authoreruid'], 1, $post['id']);
return $post;
}

function index() {
	global $_G;
	require_once (dirname(__FILE__) . "/function/function_learning.php");
	$examinestatus = $_GET[examinestatus];
	$orderby = $_GET[orderby];
	$dataline=$_GET[creatdataline];
	$examtime=$_GET[examtime];
	if($dataline==''&&$examtime=='') $examtime='DESC';
	$type=$_GET[type];
	$keywords=trim($_GET[keywords]);
	if($keywords){
		$keywordssql=" and  (learnname like '%".$keywords."%' or subrealname  like'%".$keywords."%' or Witnessrealname like '%".$keywords."%')";
		$keywordurl="&keywords=".$keywords;
	}else{
     $keywordurl="";
	}
	if ($orderby) {
		$orderbyurl = "&orderby=" . $orderby;
		$orderbysql = " ORDER BY examinedateline $orderby";
	} else {
		$orderbysql = "";
	}
	if($dataline){
		$datalineurl="&subdateline=".$dataline;
        $datalinesql=" ORDER BY subdateline $dataline";
	}else{
		$datalineurl="";
	}if($examtime){
	$examtimesql=" ORDER BY examinedateline $examtime";
	$examtimeurl="&examtime=".$examtime;
	}else{
		$examtimesql="";
	}
	if($type=='user'){
		$usersql=" and uid=".$_G[uid]."";
		$userurl="&type=".$type;
	}else{
		$type='all';
      	$allsql="";
      	$allurl="&type=".$type;
	}
	$addsql=$keywordssql.$usersql.$orderbysql.$datalinesql.$examtimesql.$allsql;
	$addurl =$keywordurl.$userurl.$orderbyurl.$datalineurl.$examtimeurl.$allsql;
	$examinestatus = $_GET["examinestatus"] ? $_GET["examinestatus"] : 3;
	$learnexcit = getlearnExcitation($examinestatus, $addurl,$addsql);
	return array (
		"multipage" => $learnexcit[multipage],
		"learnexcit" => $learnexcit[learnexcit],
		"examinestatus" => $examinestatus,
		"getrol" =>$orderby,
		"getexamtimerol"=>$examtime,
		"getcreatdate"=>$dataline,
		"usertype"=>$type
	);
}
function create(){
global $_G;
require_once (dirname(__FILE__) . "/function/function_learning.php");
$usernews=getusenews();
$pubisharr=publishnolear();
return array("usernews"=>$usernews,"pubisharr"=>$pubisharr[value],"punlishnum"=>$pubisharr[learnnum]);
}
function save() {
	global $_G;
	require_once (dirname(__FILE__) . "/function/function_learning.php");
		$type=$_POST[savetype];
	$subrealname=$_POST['subrealname'];
	$subusername=$_POST['subusername'];
	$subdeptname=$_POST['subdept'];
	$subcompanyname=$_POST['subcompany'];
	$subtel=$_POST['subtel'];
	$subPost=$_POST['subpost'];
	$learnsource=$_POST['learnsource'];
	$learnHarvest= $_POST['learnHarvest'];
	$learnaction=$_POST['learnaction'];
	$learnachievements=$_POST['learnachievements'];
	$learnname =$_POST['learnname'];
	$Witnessrealname=$_POST['witnessrealname'];
	$Witnessusername =$_POST['witnessusername'];
	$Witnessdeptname=$_POST['witnessdeptname'];
	$WitnessPost=$_POST['witnesspost'];
	$Witnesscompanyname =$_POST['witnesscompanyname'];
	$Witnesstel =$_POST['witnesstel'];
	$confidenceindex=$_POST['confidenceindex'];
	$sorceaid=$_POST['sorceaid'];
    $harvestaid=$_POST['harvestaid'];
    $actionaid=$_POST['actionaid'];
    $chievementsaid=$_POST['chievementsaid'];
    $attachentarr=array("sorceaid"=>$sorceaid,"harvestaid"=>$harvestaid,"actionaid"=>$actionaid,"chievementsaid"=>$chievementsaid);
	if($type=="savepost"){
     $savestatus=1;//保存状态
	 $info="保存成功，您可以随时完善后，提交审核，谢谢！";
	 $url="forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=learning&plugin_op=groupmenu&learning_action=create";
	$id=creatlarn($confidenceindex,$subrealname,$subusername,$subdeptname,$subcompanyname,$subtel,$subPost,$learnsource,$learnHarvest,$learnaction,$learnachievements
	 ,$learnname,$Witnessrealname,$Witnessrealname,$Witnessusername,$Witnessdeptname,$WitnessPost,$Witnesscompanyname,$Witnesstel,$savestatus);
	 updatelearattachent($attachentarr,$id);
	}if($type=="releasepost"){
     $savestatus=2;//发布状态 3 已审核
      $id=creatlarn($confidenceindex,$subrealname,$subusername,$subdeptname,$subcompanyname,$subtel,$subPost,$learnsource,$learnHarvest,$learnaction,$learnachievements
	 ,$learnname,$Witnessrealname,$Witnessrealname,$Witnessusername,$Witnessdeptname,$WitnessPost,$Witnesscompanyname,$Witnesstel,$savestatus);
      updatelearattachent($attachentarr,$id);
     $info="提交成功，点击意见回复查看审核意见，谢谢！";
     $url="forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=learning&plugin_op=groupmenu&learning_action=optionreply";
	}
	 /*$createcredit=10;
	if($savestatus==2){
		op_learncredit($_G['uid'],$_POST['subusername'],$_POST['subrealname'],1,1,$id,$createcredit,$_POST['subcompany']);//奖励积分
	}*/
    showmessage($info,$url);
}
function modify(){
global $_G;
	require_once (dirname(__FILE__) . "/function/function_learning.php");
	$savepublishtype=$_POST[savepublishtype];
	    $subdept=$_POST[subdept];
		$subcompanyname=$_POST[subcompany];
		$subtel=$_POST[subtel];
		$subPost=$_POST[subpost];
		$learnsource=$_POST[learnsource];
		$learnHarvest=$_POST[learnHarvest];
		$learnaction=$_POST[learnaction];
		$learnachievements=$_POST[learnachievements];
		$learnname=$_POST[learnname];
		$witnessrealname=$_POST[witnessrealname];
		$witnessusername=$_POST[witnessusername];
		$witnessdeptname=$_POST[witnessdeptname];
		$witnesscompanyname=$_POST[witnesscompanyname];
		$witnesstel=$_POST[witnesstel];
		$Witnesspost=$_POST[witnesspost];
		$learid=$_POST[learid];
		$learnsourceoption=trim($_POST[learnsourceoption]);
		$learnHarvestoption=trim($_POST[learnHarvestoption]);
		$learnactionoption=trim($_POST[learnactionoption]);
		$learnachievoption=trim($_POST[learnachievoption]);
		$learnoptionname=trim($_POST[learnoptionname]);
		$author=$_G[username];
		$tap=$_POST[tap];
		$examstatus=$_POST[examstatus];
		$confidenceindex=$_POST[confidenceindex];
		$realname=getrealname($_G[uid]);
	if($savepublishtype=="savechange"){//保存修改
          DB::query("UPDATE pre_learning_excitation set subdeptname='".$subdept."' ,subcompanyname='".$subcompanyname."',subtel='".$subtel."'," .
          		"subPost='".$subPost."',learnsource='".$learnsource."',learnHarvest='".$learnHarvest."',learnaction='".$learnaction."',learnachievements='".$learnachievements."'" .
          		",learnname='".$learnname."',Witnessrealname='".$witnessrealname."',Witnessusername='".$witnessusername."',Witnessdeptname='".$witnessdeptname."'" .
          		",Witnesscompanyname='".$witnesscompanyname."',Witnesstel='".$witnesstel."',Witnesspost='".$Witnesspost."',examinestatus=$examstatus,subdateline=".time().",confidenceindex=$confidenceindex where id=$learid");
	if($learnsourceoption){
		learnoption($learid,1,$learnsourceoption,2);
	}
	if($learnHarvestoption){
	    learnoption($learid,2,$learnHarvestoption,2);
	}
	if($learnactionoption){
	    learnoption($learid,3,$learnactionoption,2);
	}
	if($learnachievoption){
		learnoption($learid,4,$learnachievoption,2);
	}
	if($learnoptionname){
		learnoption($learid,5,$learnoptionname,2);
	}
	}
	else if($savepublishtype=="resubmit"){//再次提交
		DB::query("UPDATE pre_learning_excitation set subdeptname='".$subdept."' ,subcompanyname='".$subcompanyname."',subtel='".$subtel."'," .
          		"subPost='".$subPost."',learnsource='".$learnsource."',learnHarvest='".$learnHarvest."',learnaction='".$learnaction."',learnachievements='".$learnachievements."'" .
          		",learnname='".$learnname."',Witnessrealname='".$witnessrealname."',Witnessusername='".$witnessusername."',Witnessdeptname='".$witnessdeptname."'" .
          		",Witnesscompanyname='".$witnesscompanyname."',Witnesstel='".$witnesstel."',Witnesspost='".$Witnesspost."',confidenceindex=$confidenceindex,examinestatus=$examstatus ,remindstat=1 where id=$learid");
    if($learnsourceoption){
	    learnoption($learid,1,$learnsourceoption,2);
	}
	if($learnHarvestoption){
	    learnoption($learid,2,$learnHarvestoption,2);
	}
	if($learnactionoption){
	    learnoption($learid,3,$learnactionoption,2);
	}
	if($learnachievoption){
		learnoption($learid,4,$learnachievoption,2);
	}
	if($learnoptionname){
		learnoption($learid,5,$learnoptionname,2);
	}
	}
	else if($savepublishtype=="saveoption"){//保存  //管理员可操作
      DB::query("UPDATE pre_learning_excitation set subdeptname='".$subdept."' ,subcompanyname='".$subcompanyname."',subtel='".$subtel."'," .
          		"subPost='".$subPost."',learnsource='".$learnsource."',learnHarvest='".$learnHarvest."',learnaction='".$learnaction."',learnachievements='".$learnachievements."'" .
          		",learnname='".$learnname."',Witnessrealname='".$witnessrealname."',Witnessusername='".$witnessusername."',Witnessdeptname='".$witnessdeptname."'" .
          		",Witnesscompanyname='".$witnesscompanyname."',Witnesstel='".$witnesstel."',Witnesspost='".$Witnesspost."',examinestatus=$examstatus,subdateline=".time().",confidenceindex=$confidenceindex where id=$learid");
    if($learnsourceoption){
	    learnoption($learid,1,$learnsourceoption,1);
	}
	if($learnHarvestoption){
	    learnoption($learid,2,$learnHarvestoption,1);
	}
	if($learnactionoption){
	    learnoption($learid,3,$learnactionoption,1);
	}
	if($learnachievoption){
		learnoption($learid,4,$learnachievoption,1);
	}
	if($learnoptionname){
		learnoption($learid,5,$learnoptionname,1);
	}
	}
	  $url="forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=$_G[gp_plugin_name]&plugin_op=groupmenu&learning_action=optionreply";
	  showmessage("保存成功", $url);
}
function optionreply() {
	global $_G;
	$fid = $_G["fid"];
	$mod = $_G["mod"];
	$perpage = 20;
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page -1) * $perpage;
		if($_G['forum']['ismoderator']==1){

 			 $query = "SELECT * FROM " . DB :: table('learning_excitation') . " WHERE fid=" . $fid . " and examinestatus=2 and remindstat=1 LIMIT $start,$perpage";
			 $getcount = DB :: result_first("SELECT count(*) FROM " . DB :: table('learning_excitation') . " WHERE fid=" . $fid . " and examinestatus=2 and remindstat=1 ");
			 $examinestatusurl="";
		     $url = "forum.php?mod=". $mod . "&action=plugin&fid=" . $fid . "&plugin_name=learning&plugin_op=groupmenu&learning_action=optionreply";

		}else{
			$query = "SELECT * FROM " . DB :: table('learning_excitation') . " WHERE fid=" . $fid . " and examinestatus=2 and uid=".$_G[uid]."  LIMIT $start,$perpage";
		    $getcount = DB :: result_first("SELECT count(*) FROM " . DB :: table('learning_excitation') . " WHERE fid=" . $fid . " and examinestatus=2 and uid=".$_G[uid]." ");
 		    $url = "forum.php?mod=" . $mod . "&action=plugin&fid=" . $fid . "&plugin_name=learning&plugin_op=groupmenu&learning_action=optionreply";

		}
	$learnexcit = array ();
	$info = DB :: query($query);
	if ($info == False) {
		return 0;
	} else {
		while ($value = DB :: fetch($info)) {
			$learnexcit[] = $value;
		}
	}
	if ($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}
	return array (
		"multipage" => $multipage,
		"learnexcit" => $learnexcit,
		'mod' => $mod,
		"getcount" => $getcount
	);
}
//查询积分排行榜
function leaderboard() {
	global $_G;
	$perpage = 20;

	$realname=trim($_GET[realname]);

	if($realname){
      $addsql=" where realname like'%".$realname."%'";
  	  $addurl="&realname=".$realname;
	}else{
	$addurl="";
	}
	$page = intval($_GET["page"]) ? intval($_GET["page"]) : 1;
	$start = ($page -1) * $perpage;

	$sql = "select * from " . DB :: table("learn_credit") . " $addsql order by exchangecredit desc,dateline LIMIT $start,$perpage";

	$info = DB :: query($sql);
	if ($info == false)	return 0;
	else while ($value = DB :: fetch($info)) $list[] = $value;

	$sqls = "select * from " . DB :: table("learn_credit") . "  where uid =" . $_G[uid];
	$infos = DB :: query($sqls);

	$mytop = DB :: fetch($infos);
	if(!$mytop)	$mytop=array("uid"=>$_G[uid],"username"=>$_G[username],"totalcredit"=>0,"exchangecredit"=>0);
	$top=DB :: fetch(DB :: query("select count(*) as top from " . DB :: table("learn_credit") . "  where totalcredit >" . $mytop[totalcredit]));

	$mytop['top']=$top['top']+1;

	$getcount = DB :: result_first("select count(*) from " . DB :: table("learn_credit")." $addsql");
	$url = "forum.php?mod=group&action=plugin&fid=" . $_G['fid'] ."&plugin_name=learning&plugin_op=groupmenu&learning_action=leaderboard".$addurl;
	if ($getcount) {
		$multipage = multi($getcount, $perpage, $page, $url);
	}
	return array (
		"multipage" => $multipage,
		"list" => $list,
		"getcount" => $getcount,
		"mytop" => $mytop
	);
}

function getachilearncount($startdatelinesql,$enddatelinesql){
	$query = DB::query("select count(*) from " . DB :: table("learning_excitation") . " where examinestatus=3 ".$startdatelinesql.$enddatelinesql."");
	return DB::result($query);
}
function getoptioncount($userrealsql,$companysql,$deptsql,$type){
	if($type==1){
	$query = DB::query("select count(*) from " . DB :: table("learning_excitation") . " where examinestatus=2 ".$userrealsql.$companysql.$deptsql."");
	}if($type==0){
    $query = DB::query("select count(*) from " . DB :: table("learning_excitation") . " where examinestatus=2 or examinestatus=1 ".$userrealsql.$companysql.$deptsql."");
	}
	return DB::result($query);
}
function learoper(){
	require_once (dirname(__FILE__) . "/function/function_learning.php");
	$arr=publishnolear();
	return array("publeararr"=>$arr[value],"pubnoexam"=>$arr[learnnum]);
}
function alter(){
	global $_G;
	$learnid = $_GET[learnid];
	require_once (dirname(__FILE__) . "/function/function_learning.php");
	$usernews=getusenews();
	$pubisharr=publishnolear();
	$learnmv = getlearning($learnid);
	$attachments = learngetattachs($learnid,1);
	$attachlist = $attachments['attachs'];
    $sorceimagelist = $attachments['imgattachs'];

	$attaharchments = learngetattachs($learnid,2);//学习收获的附件
	$attharachlist = $attaharchments['attachs'];
	$harestimagelist = $attaharchments['imgattachs'];

	$attchmentsaction = learngetattachs($learnid,3);//学习行为的附件
	$attharachlistaction = $attchmentsaction['attachs'];
	$imagelistaction = $attchmentsaction['imgattachs'];

	$attchmentshievements = learngetattachs($learnid,4);//学习成果的附件
	$attharachlisthievements = $attchmentshievements['attachs'];
	$imagelistevements = $attchmentshievements['imgattachs'];
	return array("usernews"=>$usernews,
				 "pubisharr"=>$pubisharr[value],
				 "punlishnum"=>$pubisharr[learnnum],
				 "learnmv" => $learnmv,
				 'attachments'=>$attachments,
				'attachlist'=>$attachlist,
				'attharachlist'=>$attharachlist,
				'attharachlistaction'=>$attharachlistaction,
		        'attharachlisthievements'=>$attharachlisthievements,
				 );
}

function topcredit(){
	global $_G;
	$groupinfo=array("membernum"=>$_G[forum][membernum],"viewnum"=>$_G[forum][viewnum],"my_empirical"=>$_G[forum][my_empirical]);

	/*
	$mem=1;	//启用cache
	$key="learning_credit_top6";
	if($mem==1){
		require_once(dirname(dirname(dirname(__FILE__))).'/api/common/memcache_util.php');
		$cache = memory("get", $key);
		if(!empty($cache)){
			$array=array("topcredit"=>unserialize($cache),"groupinfo"=>$groupinfo);
			$arraydata=array("s"=>$array);
    		$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    		$jsondata = json_encode ($arraydata);
			echo "$callback($jsondata)";
			exit();
		}
	}
	*/

	$info = DB :: query("select realname,totalcredit,exchangecredit,uid,company from pre_learn_credit order by exchangecredit desc,dateline limit 0,6;");
	if ($info == False) {
		$obj = null;
	} else {
		$i=1;
		while ($value = DB :: fetch($info)) {
			$obj["top".$i] = $value;
			$i++;
		}
	}
	$topcredit=$obj;
	/*
	if(($mem==1)){	//将信息放入memcache中
			memory("set", $key, serialize($topcredit),21600);
   	}
   	*/
	$array=array("topcredit"=>$topcredit,"groupinfo"=>$groupinfo);
	$arraydata=array("s"=>$array);
    $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";
	exit();
}

//发送短消息
function senglearnmessage(){
	global $_G;
	require_once (dirname(__FILE__) . "/function/function_learning.php");
	$learnsourceoption=trim($_G['gp_learnsourceoption']);
	$learnHarvestoption=trim($_G['gp_learnHarvestoption']);
    $learnactionoption=trim($_G['gp_learnactionoption']);
    $learnachievoption=trim($_G['gp_learnachievoption']);
    $learnoptionname=trim($_G['gp_learnoptionname']);
	$touid=$_G['gp_uid'];//$_G['gp_uid'];//发送消息用户的id
	$subjectid=$_G['gp_learid'];//主题id
	$subject=$_G['gp_learnname'];//$_G['gp_learnname'];//主题
	//$torealname=user_get_user_name($_G[uid]);
	//$commandusername=user_get_user_name($touid);
	//$info="学习力评估名称为".$subject."被".$torealname."完善了建议,赶快去看看吧";
    if($learnsourceoption){
		learnoption($subjectid,1,$learnsourceoption,1);
	 }
	if($learnHarvestoption){
	    learnoption($subjectid,2,$learnHarvestoption,1);
	}
	if($learnactionoption){
	    learnoption($subjectid,3,$learnactionoption,1);
	}
	if($learnachievoption){
		learnoption($subjectid,4,$learnachievoption,1);
	}
	if($learnoptionname){
		learnoption($subjectid,5,$learnoptionname,1);
	}
	$remindstat=0;
	 DB::query("update pre_learning_excitation set remindstat=$remindstat where id=$subjectid");
	$arr= array('object' => '<a href="forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=learning&plugin_op=groupmenu&learning_action=optionview&learnid='.$subjectid.'">'.$subject.'</a>');
     notification_add($touid, 'learning', '[学习激励]管理员对您的学习力评估“'.$arr[object].'”提出了完善建议，赶快去看看吧',array(), 1);
    $arraydata=array("s"=>'1');
    $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
    $jsondata = json_encode ($arraydata);
	echo "$callback($jsondata)";
	exit();
}
function replyoption(){
	global $_G;
	$authoreruid=$_G[uid];
	$authorer=$_G[username];
	$tap=$_GET[tap];
	$type=2;
	$uid=$_POST[learuid];
	$username=$_POST[subusername];
	$message=$_POST[fastpostmessage];
	$learid=$_G['gp_learnid'];
	$replydateline=time();
	DB :: insert("opinion_reply", array (
		'uid' => $uid,
		'username' => $username,
		'replmessage' => $message,
	    'replydateline' => time(),
        'authorer' => $authorer, 'type' => 2, 'learnmvid' => $learid, 'authoreruid' => $authoreruid, 'tap' => $tap));
       $id = DB :: insert_id();
       if($id){
       $url = "forum.php?mod=group&action=plugin&fid=" . $_G['fid'] . "&plugin_name=learning&plugin_op=groupmenu&learning_action=achieview&learnid=$learid&tap=2#f_pst";
	   showmessage("意见评论创建成功", $url);
       }
}

function showupload(){
  global $_G;
  	require_once (dirname(__FILE__) . "/function/function_learning.php");
  	$learid=$_GET[learid];
    $type=$_G['gp_type'];
    $ajax=$_G['gp_ajax'];
    $attachlist = learngetattach($learid,$type);
	$attachs = $attachlist['attachs'];
	$imgattachs = $attachlist['imgattachs'];
	return array('learid'=>$learid,'type'=>$type,"attachlist"=>$attachlist,"attachs"=>$attachs,"imgattachs"=>$imgattachs,"ajax"=>$ajax);

}
function getfilename(){
	  	require_once (dirname(__FILE__) . "/function/function_learning.php");
	$aid=$_GET[aid];
	$learntype=$_GET[learntype];
	$attachment=learnviewgetattachs($aid,$learntype);
	$attachslist=$attachment['attachs'];
	$arraydate = array ("filename" =>$attachslist[0]['filename'],"aid"=>$aid,"learntype"=>$learntype,"attachsize"=>$attachslist[0]['attachsize'],"filetype"=>$attachslist[0]['filetype']);
	$callback = isset ($_GET['callback']) ? $_GET['callback'] : '';
	$jsondata = json_encode($arraydate);
	echo "$callback($jsondata)";
	exit ();
}
function delattach(){
	global $_G;
	$aid=$_G['gp_aid'];
	$learid=$_G['gp_learid'];
	$learntype=$_G['gp_learntype'];
	DB::query("delete from pre_learn_attachment where aid=$aid");
	$arraydate = array ("is"=>1,"learid"=>$learid,"learntype"=>$learntype);
	$callback = isset ($_GET['callback']) ? $_GET['callback'] : '';
	$jsondata = json_encode($arraydate);
	echo "$callback($jsondata)";
	exit ();
}
?>
