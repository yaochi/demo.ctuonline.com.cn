<?php
require './source/class/class_core.php';
require './source/function/function_group.php';

$discuz = & discuz_core::instance();
$discuz->init();
if(submitcheck('ybnwktsubmit')){
	$visitsql = ' 1=1 ';
	$visitsql .= $_G['gp_starttime'] != '' ? " AND lastvisit>'".strtotime($_G['gp_starttime']."0:00:0")."'" : '';
	$visitsql .= $_G['gp_starttime'] != '' ? " AND lastvisit<='".strtotime($_G['gp_starttime']."24:00:0")."' " : '';
	$visitnum = DB::result_first("SELECT count(*) FROM ".DB::table('common_member_status')." WHERE $visitsql");

	$tagsql =' 1=1 ';
	$tagsql .=$_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime']."0:00:0")."'" : '';
	$tagsql .=$_G['gp_starttime'] != '' ? " AND dateline<='".strtotime($_G['gp_starttime']."24:00:0")."' " : '';
	$newtag = DB::result_first("SELECT count(*) FROM ".DB::table('home_tag')." WHERE $tagsql");

    $commentpostsql = ' 1=1 ';
    $commentpostsql .=$_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime']."0:00:0")."'" : '';
    $commentpostsql .=$_G['gp_starttime'] != '' ? " AND dateline<='".strtotime($_G['gp_starttime']."24:00:0")."'" : '';
    $commentpostsql .=" and icon in('blog','share','album') and  idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");

    $commentfeedsql = ' 1=1 ';
    $commentfeedsql .=$_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime']."0:00:0")."'" : '';
    $commentfeedsql .=$_G['gp_starttime'] != '' ? " AND dateline<='".strtotime($_G['gp_starttime']."24:00:0")."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum = DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");

    $commentsql = ' 1=1 ';
    $commentsql .=$_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime']."0:00:0")."'" : '';
    $commentsql .=$_G['gp_starttime'] != '' ? " AND dateline<='".strtotime($_G['gp_starttime']."24:00:0")."'" :'';
    $commentsql .=" and icon in('blog','share','album') and  idtype!='feed'";
    $commentnum = DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");

    $taghotsql = ' 1=1 ';
    $taghotsql .=$_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime']."0:00:0")."'" : '';
    $taghotsql .=" AND dateline<='".strtotime($_G['gp_starttime']."24:00:0")."' ";
    $hottag = DB::result_first("SELECT content FROM ".DB::table('home_tag')." WHERE $taghotsql");
//   while ($hottaginfo= DB::fetch($hottag)) {
//   $hottagdata[]=$hottaginfo;
//  }

    $filename="平台运营表";
    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=".$filename.".xls");
    echo   mb_convert_encoding("翼博登录用户数",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("原创内容发表数",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("转发数",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("评论数",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("新增标签数",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("标签热度",'GB2312','UTF-8')."\t";
	echo   "\n";
    echo $visitnum."\t";
	echo $commentpostnum."\t";
	echo $commentfeednum."\t";
	echo $commentnum."\t";
	echo $newtag."\t";
	echo $hottag."\t";
	//foreach($hottagdata as $hottag){
    //
	//}
	dexit();
	//$list=array('翼博登录用户数'=>$visitnum,'新增标签数'=>$newtag,'原创内容发表数'=>$commentpostnum,'原创内容转发数'=>$commentfeednum,'原创内容评论数'=>$commentnum);
}
if(submitcheck('activesubmit')){
	if($_G['gp_username']){
		$info=DB::query("select uid as uid from pre_common_member where username='".$_G['gp_username']."'");
		$value=DB::fetch($info);
		$uid=$value[uid];
	}
    $commentpostsql = ' 1=1 ';
    $commentpostsql .=$_G['gp_username'] != '' ? " AND uid='".$uid."'" : '';
    $commentpostsql .=$_G['gp_activestarttime'] != '' ? " AND dateline>'".strtotime($_G['gp_activestarttime']."8:00:0")."'" : '';
    $commentpostsql .=$_G['gp_activestarttime'] != '' ? " AND dateline<='".strtotime($_G['gp_activestarttime']."18:00:0")."'" : '';
    $commentpostsql .=" and icon in('blog','share','album') and  idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");

    $commentfeedsql = ' 1=1 ';
    $commentfeedsql .=$_G['gp_username'] != '' ? " AND uid='".$uid."'" : '';
    $commentfeedsql .=$_G['gp_activestarttime'] != '' ? " AND dateline>'".strtotime($_G['gp_activestarttime']."8:00:0")."'" : '';
    $commentfeedsql .=$_G['gp_activestarttime'] != '' ? " AND dateline<='".strtotime($_G['gp_activestarttime']."18:00:0")."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");

    $commentsql = ' 1=1 ';
    $commentsql .=$_G['gp_username'] != '' ? " AND uid='".$uid."'" : '';
    $commentsql .=$_G['gp_activestarttime'] != '' ? " AND dateline>'".strtotime($_G['gp_activestarttime']."8:00:0")."'" : '';
    $commentsql .=$_G['gp_activestarttime'] != '' ? " AND dateline<='".strtotime($_G['gp_activestarttime']."18:0:0")."'" : '';
    $commentsql .=" and icon in('blog','share','album') and  idtype!='feed'";
    $commentnum = DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    $filename="活跃用户统计表";
    header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=".$filename.".xls");
	echo   mb_convert_encoding("原创内容发表数",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("转发数",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("评论数",'GB2312','UTF-8')."\t";
	echo   "\n";
    echo $commentpostnum."\t";
	echo $commentfeednum."\t";
	echo $commentnum."\t";
	dexit();
    //$activelist=array('原创内容发表数'=>$commentpostnum,'原创内容转发数'=>$commentfeednum,'原创内容评论数'=>$commentnum);
}

include_once template("home/ybstics");
?>
