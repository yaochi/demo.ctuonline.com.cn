<?php
require './source/class/class_core.php';
require './source/function/function_group.php';

$discuz = & discuz_core::instance();
$discuz->init();
if($_GET['operation']=='exporoptionfrom'){
	$ybstarttime=$_GET['ybstarttime'];
	$ybendtime=$_GET['ybendtime'];
	if($ybendtime){
	$visitsql = ' 1=1 ';
	$visitsql .= $ybstarttime != '' ? " AND lastvisit>'".strtotime($ybstarttime)."'" : '';
	$visitsql .= $ybendtime != '' ? " AND lastvisit<='".strtotime($ybendtime)."' " : '';
	$visitnum = DB::result_first("SELECT count(*) FROM ".DB::table('common_member_status')." WHERE $visitsql");
    $commentpostsql = ' 1=1 ';
    $commentpostsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
    $commentpostsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."'" : '';
    $commentpostsql .=" and icon in('blog','share','album') and  idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    $commentfeedsql = ' 1=1 ';
    $commentfeedsql .$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
    $commentfeedsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."'" : '';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum = DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    $commentsql = ' 1=1 ';
    $commentsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
    $commentsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."'" : '';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum = DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    $tagsql =' 1=1 ';
	$tagsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
	$tagsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."'" : '';
	$newtag = DB::result_first("SELECT count(*) FROM ".DB::table('home_tag')." WHERE $tagsql");
	$hotsql= '1=1 ';
	$hotsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime)."'" : '';
	$hotsql .=$ybendtime != '' ? " AND dateline<='".strtotime($ybendtime)."' " : '';
	$hotsql .=" order by content desc limit 0,20";
	$tagquery = DB::query("SELECT tagname ,content FROM ".DB::table('home_tag')." WHERE $hotsql");
	if(empty($commentnum)){
		$commentnum=0;
	}
	}else{
	$visitsql = ' 1=1 ';
	$visitsql .= $ybstarttime != '' ? " AND lastvisit>'".strtotime($ybstarttime."0:00:0")."'" : '';
	$visitsql .= $ybstarttime != '' ? " AND lastvisit<='".strtotime($ybstarttime."23:59:59")."' " : '';
	$visitnum = DB::result_first("SELECT count(*) FROM ".DB::table('common_member_status')." WHERE $visitsql");
    $commentpostsql = ' 1=1 ';
    $commentpostsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
    $commentpostsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."'" : '';
    $commentpostsql .=" and icon in('blog','share','album') and  idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    $commentfeedsql = ' 1=1 ';
    $commentfeedsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
    $commentfeedsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum = DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    $commentsql = ' 1=1 ';
    $commentsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
    $commentsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."'" :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum = DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    $tagsql =' 1=1 ';
	$tagsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
	$tagsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."' " : '';
	$newtag = DB::result_first("SELECT count(*) FROM ".DB::table('home_tag')." WHERE $tagsql");
	$hotsql = '1=1';
	$hotsql .=$ybstarttime != '' ? " AND dateline>'".strtotime($ybstarttime."0:00:0")."'" : '';
	$hotsql .=$ybstarttime != '' ? " AND dateline<='".strtotime($ybstarttime."23:59:59")."' " : '';
	$hotsql .=" order by content desc limit 0,20";
	$tagquery = DB::query("SELECT tagname ,content FROM ".DB::table('home_tag')." WHERE $hotsql");
	if(empty($commentnum)){
		$commentnum=0;
	}
	}
	while($value=DB::fetch($tagquery)){
		$valtag[tagname]=$value[tagname];
		$valtag[content]=$value[content];
		$valtagdata[]=$valtag;
	}
	$filename=mb_convert_encoding("平台运营表",'GB2312','UTF-8');
	header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=".$filename.".xls");
    echo  mb_convert_encoding("翼博登录用户数",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("原创内容发表数",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("转发数",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("评论数",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("新增标签数",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("标签的名称",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("标签热度",'GB2312','UTF-8')."\t";
	echo   "\r\n";
	echo $visitnum."\t";
	echo $commentpostnum."\t";
	echo $commentfeednum."\t";
	echo $commentnum."\t";
	echo $newtag."\t";
 for ($i = 0; $i < sizeof($valtagdata); $i++) {
 	if($i==0){
 		echo mb_convert_encoding($valtagdata[$i][tagname],'GB2312','UTF-8')."\t";
		echo $valtagdata[$i][content]."\t";
		echo   "\r\n";
	    echo "\t";
	    echo "\t";
	    echo "\t";
	    echo "\t";
	    echo "\t";
 	}else{
	    echo mb_convert_encoding($valtagdata[$i][tagname],'GB2312','UTF-8')."\t";
		echo $valtagdata[$i][content]."\t";
        echo   "\r\n";
	    echo "\t";
	    echo "\t";
	    echo "\t";
	    echo "\t";
	    echo "\t";
 	}
	}
}
	elseif($_GET['operation']=='exptactivefrom'){
    $username=$_GET['username'];
	$activestarttime=$_GET['activestarttime'];
	$activeendtime=$_GET['activeendtime'];
	if($username){
    $info=DB::query("select uid as uid from pre_common_member where username='".$_GET['username']."'");
	$value=DB::fetch($info);
	$str=$value[uid];
	if(!$str){
		$str=$_GET[uid];
	}
	if($activeendtime)
	{
 	$commentpostsql = ' 1=1 ';
    $commentpostsql .=" AND uid in($str)";
    $commentpostsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentpostsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " : '';
    $commentpostsql .=" and icon in('blog','share','album') and idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    $commentfeedsql = ' 1=1 ';
    $commentfeedsql .=" AND uid in($str)";
    $commentfeedsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentfeedsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    $commentsql = ' 1=1 ';
    $commentsql .=" AND uid in($str)";
    $commentsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum =DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    if(empty($commentnum)){
    	$commentnum=0;
    }
	}else{
	$commentpostsql = ' 1=1 ';
    $commentpostsql .=" AND uid in($str)";
    $commentpostsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentpostsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " : '';
    $commentpostsql .=" and icon in('blog','share','album') and idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    $commentfeedsql = ' 1=1 ';
    $commentfeedsql .=" AND uid in($str)";
    $commentfeedsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentfeedsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    $commentsql = ' 1=1 ';
    $commentsql .=" AND uid in($str)";
    $commentsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum =DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    if(empty($commentnum)){
    	$commentnum=0;
    }
	}
	}else{
if($activeendtime)
	{
	$visitsql = ' 1=1 ';
	$visitsql .= $activestarttime != '' ? " AND lastvisit>'".strtotime($activestarttime)."'" : '';
	$visitsql .= $activeendtime != '' ? " AND lastvisit<='".strtotime($activeendtime)."' " : '';
	$visquer = DB::query("SELECT uid FROM ".DB::table('common_member_status')." WHERE $visitsql");
    if($visquer){
	while($visinfo=DB::fetch($visquer)){
		$visval[uid]=$visinfo[uid];
		$visdata[]=$visval;
    }
    }
    if (count($visdata) != 0) {
	  for ($i = 0; $i < sizeof($visdata); $i++) {
			if($i==sizeof($visdata)-1){
				$str.=$visdata[$i][uid];
			}else{
			    $str.=$visdata[$i][uid].",";
			    }
			}
		}
		if(!$str){
			$str=$_G[uid];
		}
	$commentpostsql = ' 1=1 ';
    $commentpostsql .=" AND uid in($str)";
    $commentpostsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentpostsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " : '';
    $commentpostsql .=" and icon in('blog','share','album') and idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    $commentfeedsql = ' 1=1 ';
    $commentfeedsql .=" AND uid in($str)";
    $commentfeedsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentfeedsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    $commentsql = ' 1=1 ';
    $commentsql .=" AND uid in($str)";
    $commentsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime)."'" : '';
    $commentsql .=$activeendtime != '' ? " AND dateline<='".strtotime($activeendtime)."' " :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum =DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    if(empty($commentnum)){
    	$commentnum=0;
    }
	}else{
    $visitsql = ' 1=1 ';
	$visitsql .= $activestarttime != '' ? " AND lastvisit>'".strtotime($activestarttime."0:00:0")."'" : '';
	$visitsql .= $activestarttime != '' ? " AND lastvisit<='".strtotime($activestarttime."23:59:59")."' " : '';
	$visquer = DB::query("SELECT uid FROM ".DB::table('common_member_status')." WHERE $visitsql");
    if($visquer){
	while($visinfo=DB::fetch($visquer)){
		$visval[uid]=$visinfo[uid];
		$visdata[]=$visval;
    }
    }
    if (count($visdata) != 0) {
	  for ($i = 0; $i < sizeof($visdata); $i++) {
			if($i==sizeof($visdata)-1){
				$str.=$visdata[$i][uid];
			}else{
			    $str.=$visdata[$i][uid].",";
			    }
			}
		}
		if(!$str){
			$str=$_G[uid];
		}
    $commentpostsql = ' 1=1 ';
    $commentpostsql .=" AND uid in($str)";
    $commentpostsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentpostsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " : '';
    $commentpostsql .=" and icon in('blog','share','album') and idtype!='feed'";
    $commentpostnum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentpostsql");
    $commentfeedsql = ' 1=1 ';
    $commentfeedsql .=" AND uid in($str)";
    $commentfeedsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentfeedsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " :'';
    $commentfeedsql .=" and icon in('blog','share','album') and  idtype='feed'";
    $commentfeednum=DB::result_first("SELECT count(*) FROM ".DB::table('home_feed')." WHERE $commentfeedsql");
    $commentsql = ' 1=1 ';
    $commentsql .=" AND uid in($str)";
    $commentsql .=$activestarttime != '' ? " AND dateline>'".strtotime($activestarttime."0:00:0")."'" : '';
    $commentsql .=$activestarttime != '' ? " AND dateline<='".strtotime($activestarttime."23:59:59")."' " :'';
    $commentsql .=" and icon in('blog','share','album')";
    $commentnum =DB::result_first("SELECT sum(commenttimes) FROM ".DB::table('home_feed')." WHERE $commentsql");
    if(empty($commentnum)){
    	$commentnum=0;
    }
	}
	}
	$filename=mb_convert_encoding("活跃用户统计",'GB2312','UTF-8');
	header("Content-type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=".$filename.".xls");

	echo   mb_convert_encoding("原创内容发表数",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("转发数",'GB2312','UTF-8')."\t";
	echo   mb_convert_encoding("评论数",'GB2312','UTF-8')."\t";
	echo   "\r\n";
	echo $commentpostnum."\t";
	echo $commentfeednum."\t";
	echo $commentnum."\t";
	}
	dexit();
?>
