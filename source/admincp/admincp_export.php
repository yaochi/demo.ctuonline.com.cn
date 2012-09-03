<?php

/**
 *
 *      $Id: admincp_statistics.php 2011-3-8 yangyang $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if($operation == 'exportfeedblog') {
		$actid=$_G['gp_actid'];
		$typeid=$_G['gp_typeid'];
		$sql .= $_G['gp_starttime'] != '' ? " AND dateline>'".strtotime($_G['gp_starttime'])."'" : '';
		$sql .= $_G['gp_endtime'] != '' ? " AND dateline<='".strtotime($_G['gp_endtime'])."'" : '';
		if($actid==0){
			if($typeid=='music'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%音乐%' ".$sql);
			}elseif($typeid=='link'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%网址%' ".$sql);
			}elseif($typeid=='video'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%视频%' ".$sql);
			}elseif($typeid=='doc'||$typeid=='class'||$typeid=='case'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='".$typeid."' and  idtype!='".$typeid."' ".$sql);
			}else{
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='".$typeid."'".$sql);
			}
		}elseif($actid==1){
			if($typeid=='music'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%音乐%'  and idtype!='feed'".$sql);
			}elseif($typeid=='link'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%网址%'  and idtype!='feed'".$sql);
			}elseif($typeid=='video'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%视频%'  and idtype!='feed'".$sql);
			}elseif($typeid=='doc'||$typeid=='class'||$typeid=='case'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='".$typeid."' and  idtype!='".$typeid."'  and idtype!='feed'".$sql);
			}else{
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='".$typeid."'  and idtype!='feed'".$sql);
			}
		}else{
			if($typeid=='music'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%音乐%'  and idtype='feed'".$sql);
			}elseif($typeid=='link'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%网址%'  and idtype='feed'".$sql);
			}elseif($typeid=='video'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%视频%'  and idtype='feed'".$sql);
			}elseif($typeid=='doc'||$typeid=='class'||$typeid=='case'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='".$typeid."' and  idtype!='".$typeid."'  and idtype='feed'".$sql);
			}else{
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='".$typeid."'  and idtype='feed'".$sql);
			}
		}
		
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=".$typeid.".xls");
		
		//输出内容如下：  
		echo   "动态id\t";  
		echo   "用户账号\t";
		echo   "内容id\t";  
		echo   "内容类型\t";
		echo   "动作类型\t";
		echo   "时间\t";
		while($value=DB::fetch($query)){ 
			echo   "\n";  
			echo   $value[feedid]."\t";  
			$userarr=getuserbyuid($value[uid]);
			$username=$userarr[username];
			echo   $username."\t"; 
			echo   $value[id]."\t";
			if($typeid=='blog'){
				echo  "日志\t";
			}elseif($typeid=='album'){
				echo  "图片\t";
			}elseif($typeid=='activitys'){
				echo  "活动\t";
			}elseif($typeid=='doc'){
				echo  "文档\t";
			}elseif($typeid=='group'){
				echo  "专区\t"; 
			}elseif($typeid=='notice'){
				echo  "通知公告\t";
			}elseif($typeid=='nwkt'){
				echo  "你我课堂\t";
			}elseif($typeid=='poll'){
				echo  "投票\t";
			}elseif($typeid=='thread'){
				echo  "话题\t";
			}elseif($typeid=='live'){
				echo  "直播\t";
			}elseif($typeid=='questionary'){
				echo  "问卷\t";
			}elseif($typeid=='resourcelist'){
				echo  "资源列表\t";
			}elseif($typeid=='reward'){
				echo  "提问吧\t";
			}elseif($typeid=='link'){
				echo  "链接\t";
			}elseif($typeid=='video'){
				echo  "视频\t";
			}elseif($typeid=='music'){
				echo  "音乐\t";
			}elseif($typeid=='class'){
				echo  "课程\t";
			}elseif($typeid=='case'){
				echo  "案例\t";
			}
			if($value[idtype]=='feed'){
				echo "转发\t";
			}else{
				echo "原创\t";
			}
			echo dgmdate($value[dateline],'y-m-d')."\t";
		}
}elseif($operation == 'exportsinglefeed') {
		$contentid=intval($_G['gp_contentid']);
		$typeid=$_G['gp_typeid'];
		if($contentid){
			if($typeid!='music'||$typeid!='video'||$typeid!='link'){
				$feedid=DB::result_first("select feedid from ".DB::TABLE("home_feed")." where icon='".$typeid."' and id=".$contentid);
				if($feedid){
					$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='".$typeid."' and idtype='feed' and id=".$feedid);
				}
			}elseif($typeid=='music'||$typeid=='video'||$typeid=='link'){
				$feedid=DB::result_first("select feedid from ".DB::TABLE("home_feed")." where icon='share' and id=".$contentid);
				if($feedid){
					$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and idtype='feed' and id=".$feedid);
				}
			}
			header("Content-type:application/vnd.ms-excel");
			header("Content-Disposition:attachment;filename=".$typeid.".xls");
			
			//输出内容如下：  
			echo   "动态id\t";  
			echo   "用户账号\t";
			echo   "内容id\t";  
			echo   "内容类型\t";
			echo   "动作类型\t";
			echo   "时间\t";
			while($value=DB::fetch($query)){ 
				echo   "\n";  
				echo   $value[feedid]."\t";  
				$userarr=getuserbyuid($value[uid]);
				$username=$userarr[username];
				echo   $username."\t"; 
				echo   $contentid."\t";
				if($typeid=='blog'){
					echo  "日志\t";
				}elseif($typeid=='album'){
					echo  "图片\t";
				}elseif($typeid=='activitys'){
					echo  "活动\t";
				}elseif($typeid=='doc'){
					echo  "文档\t";
				}elseif($typeid=='group'){
					echo  "专区\t"; 
				}elseif($typeid=='notice'){
					echo  "通知公告\t";
				}elseif($typeid=='nwkt'){
					echo  "你我课堂\t";
				}elseif($typeid=='poll'){
					echo  "投票\t";
				}elseif($typeid=='thread'){
					echo  "话题\t";
				}elseif($typeid=='live'){
					echo  "直播\t";
				}elseif($typeid=='questionary'){
					echo  "问卷\t";
				}elseif($typeid=='resourcelist'){
					echo  "资源列表\t";
				}elseif($typeid=='reward'){
					echo  "提问吧\t";
				}elseif($typeid=='link'){
					echo  "链接\t";
				}elseif($typeid=='video'){
					echo  "视频\t";
				}elseif($typeid=='music'){
					echo  "音乐\t";
				}elseif($typeid=='class'){
					echo  "课程\t";
				}elseif($typeid=='case'){
					echo  "案例\t";
				}
				if($value[idtype]=='feed'){
					echo "转发\t";
				}else{
					echo "原创\t";
				}
				echo dgmdate($value[dateline],'y-m-d')."\t";
			}
		}
}

?>