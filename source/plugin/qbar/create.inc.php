<?php
require_once(dirname(dirname(dirname(__FILE__)))."/joinplugin/pluginboot.php");

/**
 * http打开新建页面请求
 */
function index(){
	global $_G;
	$fid=$_G['fid'];
  	$empiricalvalue=group_get_empirical($_G['uid'],$_G['fid']);//用户在当前专区的经验值（该函数在function_group.php文件中）    
  	$_G['empiricalvalue']=$empiricalvalue;
 	require_once libfile("function/category");
    $is_enable_category = false;
    $pluginid = $_GET["plugin_name"];
    if(common_category_is_enable($_G["fid"], $pluginid)){
        $is_enable_category = true;
        $categorys = common_category_get_category($_G["fid"], $pluginid);
    }
	$url = "forum.php?mod=post&action=newthread&fid=$fid&special=3&plugin_name=qbar&is_enable_category=$is_enable_category";
    header("Location:".$url); 
}

function save(){
	require_once libfile("function/group");
	require_once libfile('function/post');
    global $_G;
    
	/*
     * undo权限判断
     */
    $privilege=1;
    if(!$privilege){
    	showmessage('您没有权限在当前专区中发表提问',join_plugin_action("index"));
    }
    
    $special=3;//帖子类型，3代表提问
    $subject = isset($_G['gp_subject']) ? dhtmlspecialchars(censor(trim($_G['gp_subject']))) : '';
	$subject = !empty($subject) ? str_replace("\t", ' ', $subject) : $subject;
	$message = isset($_G['gp_message']) ? censor($_G['gp_message']) : '';
	$readperm = isset($_G['gp_readperm']) ? intval($_G['gp_readperm']) : 0;
	$price = isset($_G['gp_price']) ? intval($_G['gp_price']) : 0;
	if($post_invalid = checkpost($subject, $message, ($special || $sortid))) {//checkpost函数位于function_post.php文件中
		showmessage($post_invalid, '', array('minpostsize' => $_G['setting']['minpostsize'], 'maxpostsize' => $_G['setting']['maxpostsize']));
	}
    //$subject=$_POST['subject'];//问题标题
    $posttableid =0;
    //$price=inval($_POST['price']);//问题被采纳为最佳答案获取的经验值
    $price=0-$price;
    $typeid=0;
    $sortid=0;
    $author=$_G['username'];//用户登录名称
    $displayorder=0;
    $digest=0;//是否精华
    $isgroup=1;//是否在专区中
    
    
    
    DB::query("INSERT INTO ".DB::table('forum_thread')." (fid, posttableid, readperm, price, typeid, sortid, author, authorid, subject, dateline, lastpost, lastposter, displayorder, digest, special, attachment, moderated, status, isgroup)
		VALUES ('$_G[fid]', '$posttableid', '$readperm', '$price', '$typeid', '$sortid', '$author', '$_G[uid]', '$subject', '$_G[timestamp]', '$_G[timestamp]', '$author', '$displayorder', '$digest', '$special', '0', '$moderated', '$thread[status]', '$isgroup')");
	$tid = DB::insert_id();	//新插入的提问id
	DB::update('common_member_field_home', array('recentnote'=>$subject), array('uid'=>$_G['uid']));
	
	$pinvisible = $modnewthreads ? -2 : 0;
	$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);
	$bbcodeoff = checkbbcodes($message, !empty($_G['gp_bbcodeoff']));
	$smileyoff = checksmilies($message, !empty($_G['gp_smileyoff']));
	$parseurloff = !empty($_G['gp_parseurloff']);
	$isanonymous = ($_G['group']['allowanonymous'] && $_G['gp_isanonymous']) ? 1 : 0;//是否匿名
	$htmlon = bindec(($_G['setting']['tagstatus'] && !empty($tagoff) ? 1 : 0).($_G['group']['allowhtml'] && !empty($_G['gp_htmlon']) ? 1 : 0));
	$pid = insertpost(array(
		'fid' => $_G['fid'],
		'tid' => $tid,
		'first' => '1',
		'author' => $_G['username'],
		'authorid' => $_G['uid'],
		'subject' => $subject,
		'dateline' => $_G['timestamp'],
		'message' => $message,
		'useip' => $_G['clientip'],
		'invisible' => $pinvisible,
		'anonymous' => $isanonymous,
		'usesig' => $_G['gp_usesig'],
		'htmlon' => $htmlon,
		'bbcodeoff' => $bbcodeoff,
		'smileyoff' => $smileyoff,
		'parseurloff' => $parseurloff,
		'attachment' => '0',
		'tags' => '',
	));
	
	if($price){
		group_add_empirical($_G['uid'],$_G['fid'],$price);//更新用户在专区的经验值
	}
	
	//group_add_empirical_by_setting($_G['uid'],$_G['fid'],"question_publish");//用户发布问题是更改经验值
	
	
	
	/*
	 * undo 产生动态
	 */
	

    showmessage('提问发表成功！', "forum.php?mod=activity&fid=$newfid");
    
    
    
}

?>