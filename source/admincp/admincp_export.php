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
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%����%' ".$sql);
			}elseif($typeid=='link'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%��ַ%' ".$sql);
			}elseif($typeid=='video'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%��Ƶ%' ".$sql);
			}elseif($typeid=='doc'||$typeid=='class'||$typeid=='case'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='".$typeid."' and  idtype!='".$typeid."' ".$sql);
			}else{
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='".$typeid."'".$sql);
			}
		}elseif($actid==1){
			if($typeid=='music'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%����%'  and idtype!='feed'".$sql);
			}elseif($typeid=='link'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%��ַ%'  and idtype!='feed'".$sql);
			}elseif($typeid=='video'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%��Ƶ%'  and idtype!='feed'".$sql);
			}elseif($typeid=='doc'||$typeid=='class'||$typeid=='case'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='".$typeid."' and  idtype!='".$typeid."'  and idtype!='feed'".$sql);
			}else{
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='".$typeid."'  and idtype!='feed'".$sql);
			}
		}else{
			if($typeid=='music'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%����%'  and idtype='feed'".$sql);
			}elseif($typeid=='link'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%��ַ%'  and idtype='feed'".$sql);
			}elseif($typeid=='video'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='share' and title_template like '%��Ƶ%'  and idtype='feed'".$sql);
			}elseif($typeid=='doc'||$typeid=='class'||$typeid=='case'){
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='".$typeid."' and  idtype!='".$typeid."'  and idtype='feed'".$sql);
			}else{
				$query=DB::query("select feedid,id,uid,icon,idtype,dateline from ".DB::TABLE("home_feed")." where icon='".$typeid."'  and idtype='feed'".$sql);
			}
		}
		
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=".$typeid.".xls");
		
		//����������£�  
		echo   "��̬id\t";  
		echo   "�û��˺�\t";
		echo   "����id\t";  
		echo   "��������\t";
		echo   "��������\t";
		echo   "ʱ��\t";
		while($value=DB::fetch($query)){ 
			echo   "\n";  
			echo   $value[feedid]."\t";  
			$userarr=getuserbyuid($value[uid]);
			$username=$userarr[username];
			echo   $username."\t"; 
			echo   $value[id]."\t";
			if($typeid=='blog'){
				echo  "��־\t";
			}elseif($typeid=='album'){
				echo  "ͼƬ\t";
			}elseif($typeid=='activitys'){
				echo  "�\t";
			}elseif($typeid=='doc'){
				echo  "�ĵ�\t";
			}elseif($typeid=='group'){
				echo  "ר��\t"; 
			}elseif($typeid=='notice'){
				echo  "֪ͨ����\t";
			}elseif($typeid=='nwkt'){
				echo  "���ҿ���\t";
			}elseif($typeid=='poll'){
				echo  "ͶƱ\t";
			}elseif($typeid=='thread'){
				echo  "����\t";
			}elseif($typeid=='live'){
				echo  "ֱ��\t";
			}elseif($typeid=='questionary'){
				echo  "�ʾ�\t";
			}elseif($typeid=='resourcelist'){
				echo  "��Դ�б�\t";
			}elseif($typeid=='reward'){
				echo  "���ʰ�\t";
			}elseif($typeid=='link'){
				echo  "����\t";
			}elseif($typeid=='video'){
				echo  "��Ƶ\t";
			}elseif($typeid=='music'){
				echo  "����\t";
			}elseif($typeid=='class'){
				echo  "�γ�\t";
			}elseif($typeid=='case'){
				echo  "����\t";
			}
			if($value[idtype]=='feed'){
				echo "ת��\t";
			}else{
				echo "ԭ��\t";
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
			
			//����������£�  
			echo   "��̬id\t";  
			echo   "�û��˺�\t";
			echo   "����id\t";  
			echo   "��������\t";
			echo   "��������\t";
			echo   "ʱ��\t";
			while($value=DB::fetch($query)){ 
				echo   "\n";  
				echo   $value[feedid]."\t";  
				$userarr=getuserbyuid($value[uid]);
				$username=$userarr[username];
				echo   $username."\t"; 
				echo   $contentid."\t";
				if($typeid=='blog'){
					echo  "��־\t";
				}elseif($typeid=='album'){
					echo  "ͼƬ\t";
				}elseif($typeid=='activitys'){
					echo  "�\t";
				}elseif($typeid=='doc'){
					echo  "�ĵ�\t";
				}elseif($typeid=='group'){
					echo  "ר��\t"; 
				}elseif($typeid=='notice'){
					echo  "֪ͨ����\t";
				}elseif($typeid=='nwkt'){
					echo  "���ҿ���\t";
				}elseif($typeid=='poll'){
					echo  "ͶƱ\t";
				}elseif($typeid=='thread'){
					echo  "����\t";
				}elseif($typeid=='live'){
					echo  "ֱ��\t";
				}elseif($typeid=='questionary'){
					echo  "�ʾ�\t";
				}elseif($typeid=='resourcelist'){
					echo  "��Դ�б�\t";
				}elseif($typeid=='reward'){
					echo  "���ʰ�\t";
				}elseif($typeid=='link'){
					echo  "����\t";
				}elseif($typeid=='video'){
					echo  "��Ƶ\t";
				}elseif($typeid=='music'){
					echo  "����\t";
				}elseif($typeid=='class'){
					echo  "�γ�\t";
				}elseif($typeid=='case'){
					echo  "����\t";
				}
				if($value[idtype]=='feed'){
					echo "ת��\t";
				}else{
					echo "ԭ��\t";
				}
				echo dgmdate($value[dateline],'y-m-d')."\t";
			}
		}
}

?>