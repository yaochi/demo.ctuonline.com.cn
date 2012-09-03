<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_blog.php 9014 2010-04-26 05:56:45Z liguode $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function blog_post($POST, $olds=array()) {
	global $_G, $space;

	$isself = 1;
	if(!empty($olds['uid']) && $olds['uid'] != $_G['uid']) {
		$isself = 0;
		$__G = $_G;
		$_G['uid'] = $olds['uid'];
		$_G['username'] = addslashes($olds['username']);
	}
	if($POST['subject']){
		$POST['subject'] = getstr(trim($POST['subject']), 80, 1, 1, 1);
	}elseif($POST['titleinput']){
		if($POST['titleinput']!="记录标题（可选）"){
			$POST['subject'] = getstr(trim($POST['titleinput']), 80, 1, 1, 1);
		}else{
			$POST['subject'] ='推荐记录';
		}
	}
	//if(strlen($POST['subject'])<1) $POST['subject'] = dgmdate($_G['timestamp'], 'Y-m-d');
	$POST['friend'] = intval($POST['friend']);

	$POST['target_ids'] = '';
	if($POST['friend'] == 2) {
		$uids = array();
		$prenames = empty($_POST['target_names'])?array():explode(',', preg_replace("/(\s+)/s", ',', $_POST['target_names']));
		$realnames = empty($_POST['target_realnames'])?array():explode(',', preg_replace("/(\s+)/s", ',', $_POST['target_realnames']));
		foreach($prenames as $key=>$value){
			$realvalue=user_get_user_name_by_username($value);
			if(in_array($realvalue,$realnames)){
				$names[]=$value;
			}
		}
		if($names) {
			$query = DB::query("SELECT uid FROM ".DB::table('common_member')." WHERE username IN (".dimplode($names).")");
			while ($value = DB::fetch($query)) {
				$uids[] = $value['uid'];
			}
		}
		if(empty($uids)) {
			$POST['friend'] = 3;
		} else {
			$POST['target_ids'] = implode(',', $uids);
		}
	} elseif($POST['friend'] == 4) {
		$POST['password'] = trim($POST['password']);
		if($POST['password'] == '') $POST['friend'] = 0;
	}
	if($POST['friend'] !== 2) {
		$POST['target_ids'] = '';
	}
	if($POST['friend'] !== 4) {
		$POST['password'] == '';
	}

	$POST['tag'] = dhtmlspecialchars(trim($POST['tag']));
	$POST['tag'] = getstr($POST['tag'], 500, 1, 1, 1);

	if($_G['mobile']) {
		$POST['message'] = getstr($POST['message'], 0, 1, 0, 1, 1);
	} elseif($POST['message']) {
		$POST['message'] = checkhtml($POST['message']);
		$POST['message'] = getstr($POST['message'], 0, 1, 0, 1, 0, 1);
		$POST['message'] = preg_replace(array(
			"/\<div\>\<\/div\>/i",
			"/\<a\s+href\=\"([^\>]+?)\"\>/i"
		), array(
			'',
			'<a href="\\1" target="_blank">'
		), $POST['message']);
	}else{
		$POST['message'] = checkhtml($POST['msginput']);
		$POST['message'] = getstr($POST['message'], 0, 1, 0, 1, 0, 1);
		$POST['message'] = preg_replace(array(
			"/\<div\>\<\/div\>/i",
			"/\<a\s+href\=\"([^\>]+?)\"\>/i"
		), array(
			'',
			'<a href="\\1" target="_blank">'
		), $POST['message']);
	}
	$message = $POST['message'];
	$mesarray=explode('<img',$message);
	for($i=0;$i<count($mesarray);$i++){
		if($i==0){
			$newmessage=$mesarray[$i];
		}else{
			$newmessage=$newmessage.' <img'.$mesarray[$i];
		}
	}
	$message=$newmessage;
	
	//@和#号的拼接
	if($POST['atinput']){
		$atarray=explode(' ',$POST['atinput']);
		for($i=0;$i<count($atarray);$i++){
			$message='@'.$atarray[$i].' '.$message;
		}
	}
	if($POST['taginput']){
		$tagarray=explode(' ',$POST['taginput']);
		for($i=0;$i<count($tagarray);$i++){	
				if($i==0){
					$message=$message.' #'.$tagarray[$i].'#';
				}else{
					$message=$message.'#'.$tagarray[$i].'#';
				}		
				
		}
	}
	//print_r($message);
	$postmessage=$message;
	//@解析
	if($POST['operate']){
		if($POST['atjson']){
			$resarr=parseat1($message,$_G['uid'],$POST['atjson']);
		}else{
			$resarr=parseat($message,$_G['uid']);
		}
	}else{
		if($POST['atjson']){
			$resarr=parseat1($message,$_G['uid'],$POST['atjson']);
		}else{
			$resarr=parseat($message,$_G['uid'],1);
		}
		if($resarr['atfids']){
			for($i=0;$i<count($resarr['atfids']);$i++){
				group_add_empirical_by_setting($_G['uid'],$resarr['atfids'][$i], 'at_group', $resarr['atfids'][$i]);
			}
		}
	}
	$message=$resarr['message'];
	
	//print_r($resarr);exit;
	
	if(empty($olds['classid']) || $POST['classid'] != $olds['classid']) {
		if(!empty($POST['classid']) && substr($POST['classid'], 0, 4) == 'new:') {
			$classname = dhtmlspecialchars(trim(substr($POST['classid'], 4)));
			$classname = getstr($classname, 0, 1, 1, 1);
			if(empty($classname)) {
				$classid = 0;
			} else {
				$classid = DB::result(DB::query("SELECT classid FROM ".DB::table('home_class')." WHERE classname='$classname' AND uid='$_G[uid]'"));
				if(empty($classid)) {
					$setarr = array(
						'classname' => $classname,
						'uid' => $_G['uid'],
						'dateline' => $_G['timestamp']
					);
					$classid = DB::insert('home_class', $setarr, 1);
				}
			}
		} else {
			$classid = intval($POST['classid']);

		}
	} else {
		$classid = $olds['classid'];
	}
	if($classid && empty($classname)) {
		$classname = DB::result(DB::query("SELECT classname FROM ".DB::table('home_class')." WHERE classid='$classid' AND uid='$_G[uid]'"));
		if(empty($classname)) $classid = 0;
	}
	
	$blogarr = array(
		'subject' => $POST['subject'],
		'classid' => $classid,
		'friend' => $POST['friend'],
		'password' => $POST['password'],
		'noreply' => empty($POST['noreply'])?0:1,
		'catid' => intval($POST['catid']),
		
	);
	

	$titlepic = '';

	$uploads = array();
	if(!empty($POST['picids'])) {
		$picids = array_keys($POST['picids']);
		$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE picid IN (".dimplode($picids).") AND uid='$_G[uid]'");
		while ($value = DB::fetch($query)) {
			if(empty($titlepic) && $value['thumb']) {
				$titlepic = $value['filepath'].'.thumb.jpg';
				$blogarr['picflag'] = $value['remote']?2:1;
			}
			$uploads[$POST['picids'][$value['picid']]] = $value;
		}
		if(empty($titlepic) && $value) {
			$titlepic = $value['filepath'];
			$blogarr['picflag'] = $value['remote']?2:1;
		}
	}

	if($uploads) {
		preg_match_all("/\[imgid\=(\d+)\]/i", $message, $mathes);
		if(!empty($mathes[1])) {
			$searchs = $replaces = array();
			foreach ($mathes[1] as $key => $value) {
				if(!empty($uploads[$value])) {
					$picurl = pic_get($uploads[$value]['filepath'], 'album', $uploads[$value]['thumb'], $uploads[$value]['remote'], 0);
					$searchs[] = "[imgid=$value]";
					$replaces[] = "<img src=\"$picurl\">";
					unset($uploads[$value]);
				}
			}
			if($searchs) {
				$message = str_replace($searchs, $replaces, $message);
			}
		}
		foreach ($uploads as $value) {
			$picurl = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote'], 0);
			$message .= "<div class=\"uchome-message-pic\"><img src=\"$picurl\"><p>$value[title]</p></div>";
		}
	}

	$ckmessage = preg_replace("/(\<div\>|\<\/div\>|\s|\&nbsp\;|\<br\>|\<p\>|\<\/p\>)+/is", '', $message);
	if(empty($ckmessage)) {
		return false;
	}

	if(empty($titlepic)) {
		$titlepic = getmessagepic($message);
		$blogarr['picflag'] = 0;
	}
	$message = addslashes($message);
	if(checkperm('manageblog')) {
		$blogarr['hot'] = intval($POST['hot']);
	}
	
	if($olds['blogid']) {

		if($blogarr['catid'] != $olds['catid']) {
			if($olds['catid']) {
				db::query("UPDATE ".db::table('home_blog_category')." SET num=num-1 WHERE catid='$olds[catid]' AND num>0");
			}
			if($blogarr['catid']) {
				db::query("UPDATE ".db::table('home_blog_category')." SET num=num+1 WHERE catid='$blogarr[catid]'");
			}
		}
		$istop = $POST['istop'];
		if($istop)
		{
			$topdateline = time();
			$blogarr['istop'] = $istop;
			$blogarr['topdateline'] = $topdateline;
		}
		
		$blogid = $olds['blogid'];
		DB::update('home_blog', $blogarr, array('blogid'=>$blogid));

		$fuids = array();

		$blogarr['uid'] = $olds['uid'];
		$blogarr['username'] = $olds['username'];
	} else {

		if($blogarr['catid']) {
			db::query("UPDATE ".db::table('home_blog_category')." SET num=num+1 WHERE catid='$blogarr[catid]'");
		}
		$istop = $POST['istop'];
		if($istop)
		{
			$topdateline = time();
			$blogarr['istop'] = $istop;
			$blogarr['topdateline'] = $topdateline;
		}
		
		if(!$_G['uid']){
			$_G['uid']=$POST['uid'];
		}
		if($POST['anonymity']){
			$blogarr['anonymity']=$POST['anonymity'];
		}else{
			$blogarr['anonymity']=empty($_G[member][repeatsstatus])?'0':$_G[member][repeatsstatus];
		}
		$blogarr['uid'] = $_G['uid'];
		$blogarr['username'] = user_get_user_name($_G[uid]);
		$blogarr['dateline'] = empty($POST['dateline'])?$_G['timestamp']:$POST['dateline'];
		$blogid = DB::insert('home_blog', $blogarr, 1);

		DB::update('common_member_field_home', array('recentnote'=>$POST['subject']), array('uid'=>$_G['uid']));
		
	}

	$blogarr['blogid'] = $blogid;

	$fieldarr = array(
		'message' => $message,
		'postip' => $_G['clientip'],
		'target_ids' => $POST['target_ids'],
		'tag' => $POST['tag'],
		'pic' => $titlepic
	);

	if($olds) {
		DB::update('home_blogfield', $fieldarr, array('blogid'=>$blogid));
	} else {
		$fieldarr['blogid'] = $blogid;
		$fieldarr['uid'] = $blogarr['uid'];
		DB::insert('home_blogfield', $fieldarr);
	}

	if($isself && !$olds) {
		updatecreditbyaction('publishblog', 0, array('blogs' => 1));

		include_once libfile('function/stat');
		updatestat('blog');
	}
	
	//if($POST['makefeed']) {
	include_once libfile('function/feed');
	//if($POST[fromwhere]!='3'){
	feed_publish($blogid, 'blogid', $olds?0:1,$POST[fromwhere],$POST[atjson],$postmessage);
	//}
	if($blogarr['anonymity']=='0'){
		DB::query("update ".DB::TABLE("common_member_status")." set blogs=blogs+1 where uid=".$_G[uid]);
	}
	//}
	if($POST['isnotify'])
	{
		//发送全站通知
		$touid = 0; //发给全站
		$type = 'gfblog_notice';//通知类型
		$note = 'gfblog_notice';//通知模板类型
		$uid = getOfficialBlogUid();//官方博客uid
		$note_values = array('url' => "home.php?mod=space&uid=$uid&from=space&do=blog&id=$blogarr[blogid]", 'title'=>"$blogarr[subject]");
		
//待完善，要设置为官方通知
		notification_add($touid, $type, $note, $note_values,0,1);
	}

	
	//如果是官方博客发表日志，更新首页官方博客cache
	//print_r($blogarr['uid']."================");
	if($blogarr['uid'] == getOfficialBlogUid()){
		//更新首页缓存  add by songsp 2011-3-14 16:28:05
		
		//print_r("<br>=====================官方博客cache更新============================<br>");
		flushBlog4PortalCache();
		
		//exit('----'.$blogarr['uid']);
	}
	
	
	
	
	
	
	
	if(!empty($__G)) $_G = $__G;

	return $blogarr;
}

function getmessagepic($message) {
	$pic = '';
	$messagearray=explode('<img',$message);
	if($messagearray[1]){
		$pic=substr($messagearray[1],strpos($messagearray[1],'src=')+5);
		$pic=str_replace("'","\"",$pic);
		$pic=substr($pic,0,strpos($pic,'"'));
	}
	return addslashes($pic);
}

function checkhtml($html) {
	$html = dstripslashes($html);
	if(!checkperm('allowhtml')) {

		preg_match_all("/\<([^\<]+)\>/is", $html, $ms);

		$searchs[] = '<';
		$replaces[] = '&lt;';
		$searchs[] = '>';
		$replaces[] = '&gt;';

		if($ms[1]) {
			$allowtags = 'img|a|font|div|table|tbody|caption|tr|td|th|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote|object|param|embed';
			$ms[1] = array_unique($ms[1]);
			foreach ($ms[1] as $value) {
				$searchs[] = "&lt;".$value."&gt;";

				$value = str_replace('&', '_uch_tmp_str_', $value);
				$value = dhtmlspecialchars($value);
				$value = str_replace('_uch_tmp_str_', '&', $value);

				$value = str_replace(array('\\','/*'), array('.','/.'), $value);
				$value = preg_replace(array("/(javascript|script|eval|behaviour|expression|style|class)/i", "/(\s+|&quot;|')on/i"), array('.', ' .'), $value);
				if(!preg_match("/^[\/|\s]?($allowtags)(\s+|$)/is", $value)) {
					$value = '';
				}
				$replaces[] = empty($value)?'':"<".str_replace('&quot;', '"', $value).">";
			}
		}
		$html = str_replace($searchs, $replaces, $html);
	}
	$html = addslashes($html);

	return $html;
}

function blog_bbcode($message) {
	$message = preg_replace("/\[flash\=?(media|real)*\](.+?)\[\/flash\]/ie", "blog_flash('\\2', '\\1')", $message);
	return $message;
}
function blog_flash($swf_url, $type='') {
	$width = '520';
	$height = '390';
	if ($type == 'media') {
		$html = '<object classid="clsid:6bf52a52-394a-11d3-b153-00c04f79faa6" width="'.$width.'" height="'.$height.'">
			<param name="autostart" value="0">
			<param name="url" value="'.$swf_url.'">
			<embed autostart="false" src="'.$swf_url.'" type="video/x-ms-wmv" width="'.$width.'" height="'.$height.'" controls="imagewindow" console="cons"></embed>
			</object>';
	} elseif ($type == 'real') {
		$html = '<object classid="clsid:cfcdaa03-8be4-11cf-b84b-0020afbbccfa" width="'.$width.'" height="'.$height.'">
			<param name="autostart" value="0">
			<param name="src" value="'.$swf_url.'">
			<param name="controls" value="Imagewindow,controlpanel">
			<param name="console" value="cons">
			<embed autostart="false" src="'.$swf_url.'" type="audio/x-pn-realaudio-plugin" width="'.$width.'" height="'.$height.'" controls="controlpanel" console="cons"></embed>
			</object>';
	} else {
		$html = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="'.$width.'" height="'.$height.'">
			<param name="movie" value="'.$swf_url.'">
			<param name="allowscriptaccess" value="none">
			<param name="allowNetworking" value="none">
			<embed src="'.$swf_url.'" type="application/x-shockwave-flash" width="'.$width.'" height="'.$height.'" allowfullscreen="true" allowscriptaccess="always"></embed>
			</object>';
	}
	return $html;
}








/*
 * add by songsp 2011-3-14 16:31:36
 * 社区首页性能优化
 * 社区首页官方博客信息从cache中的获得，如果cache中没有，从DB中查询，并set到cache中
 */
function getBlog4PortalFromCache(){
	
	//从cache 中获取
	$allowmem = memory('check');
	$cache_key = 'portal_gblog' ; //UezBhH_
	
	if($allowmem){
		$cache = memory("get", $cache_key);
		if(!empty($cache)){
			$official_blog_info=unserialize($cache);
			return $official_blog_info;
		}
	}
	
	//从DB中获取
	$official_blog_info = getBlog4Portal();
	
	if($allowmem){
		memory("set", $cache_key, serialize($official_blog_info));
	}


	
	return $official_blog_info;
	
}



/*
 * add by songsp 2011-3-14 16:36:44
 * 
 * 更新社区首页官方博客cache中的信息
 * 
 */
function flushBlog4PortalCache(){
	$allowmem = memory('check');
	$cache_key = 'portal_gblog' ; //UezBhH_
	
	if($allowmem){
		memory("rm", $cache_key);
		
		//从DB中重新获取
		$official_blog_info = getBlog4Portal();
		
		memory("set", $cache_key, serialize($official_blog_info));
		
	}
	
	
}






/*
 * add by songsp 2010-8-16 11:56:38 
 * 获得首页上显示的有关官方博客的信息
 * 返回数组 
 *  rs['top'] 为置顶的两条官方日志
 *  rs['new'] 为最新的六条官方日志
 */
function getBlog4Portal(){
	
	$official_blog_info = $topblog = $newblog =  array();
	
	$today = "今天";
	$yesterday = "昨天";
	$dateformat = "m月d日";
	$today_dateline= strtotime("today"); //今天的时间
	
	//得到官方博客uid
	$officialBlogUid = getOfficialBlogUid();
	
	//获得分类
	$query = DB::query("SELECT classid, classname FROM ".DB::table('home_class')." WHERE uid='$officialBlogUid'");
	while ($value = DB::fetch($query)) {
		$classarr[$value['classid']] = $value['classname'];
	}
	
	$classarr['0'] = "其他";
	
	//获得两条置顶的，如果没有置顶的取最新的两条。
	$query = DB::query("SELECT blog.* ,bf.message FROM ".DB::table('home_blog')." blog left join ".DB::table('home_blogfield')." bf on bf.blogid = blog.blogid WHERE blog.uid='$officialBlogUid' ORDER BY blog.istop DESC ,blog.topdateline DESC,blog.dateline DESC LIMIT 0,2");
	
	//记录前面显示的两条官方博客日志id
	$ids = array();
	require_once libfile('function/post');
	while ($value = DB::fetch($query)) {
		$ids[] = $value['blogid'];
		$value['classname'] = $classarr[$value['classid']];
		$value['dateline'] = dgmdate($value['dateline'],'Y-m-d');
		
		$value['message']=trim($value['message']);	
		$value['message']=str_replace("&nbsp;","",$value['message']);
		$value['message']=messagecutstr($value['message'],160);
		
	 
		$topblog[] = $value;	
	}
	
	$sub_where = "";
	if($ids){
		$sub_where = " AND blogid NOT IN (".dimplode($ids).") ";
	}
	//获得除置顶的最新的4条
	//修改为 6 条
	$query = DB::query("SELECT * FROM ".DB::table('home_blog')." WHERE uid='$officialBlogUid' ".$sub_where." ORDER BY dateline DESC LIMIT 0,6");
	while ($value = DB::fetch($query)) {
		$value['classname'] = $classarr[$value['classid']];
		//$value['dateline'] = dgmdate($value['dateline']);
		
		//处理时间
		/*if($value['dateline']>=$today_dateline) {
			$dkey = $today;
		} elseif ($value['dateline']>=$today_dateline-3600*24) {
			$dkey = $yesterday;
		} else {
			$dkey = dgmdate($value['dateline'], $dateformat);
		}
		$value['dateline'] = $dkey;*/
		
		$value['dateline'] = dgmdate($value['dateline'],'m-d');
		
		$newblog[] = $value;	
	}
	
	$official_blog_info['top'] = $topblog;
	$official_blog_info['new'] = $newblog;
	
	$official_blog_info['gbloguid'] = $officialBlogUid; //官方博客uid
	
	return $official_blog_info;
}


function blog_offical_user($uid){
    $user = array(fuid=>$uid);
    $blog_uid = getOfficialBlogUid();
    
    //优化 by songsp  2011-3-25 14:40:46 
	$allowmem = memory('check');
	$cache_key = 'official_blog_userinfo' ; //UezBhH_
	
	if($allowmem){
		$cache = memory("get", $cache_key);
		if(!empty($cache)){
			$rs =  unserialize($cache);
		}
	}
	if(!$rs){
		$query = DB::query("SELECT * FROM ".DB::table("common_member")." WHERE uid=".$blog_uid);
        $rs = DB::fetch($query);
	}	
   
       
    if($rs){
         $user[uid] = $rs[uid];
         $user[username] = $rs[username];
         return $user;
    }
    
}
?>