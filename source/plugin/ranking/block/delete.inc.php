<?php
/**
 * 2010-11-29 15:34:18 yyang
 */
class block_delete_ranking{
	function deletedata($ids,$contenttype,$fid){
	 	if($contenttype=='topic'){
			 require dirname(dirname(dirname(__FILE__))).'/topic/block/delete.inc.php';
			 $topic=new block_delete_topic();
			 $res=$topic->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='album'){
			 require dirname(dirname(dirname(__FILE__))).'/groupalbum2/block/delete.inc.php';
			 $album=new block_delete_album();
			 $res=$album->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='qbar'){
			 require dirname(dirname(dirname(__FILE__))).'/qbar/block/delete.inc.php';
			 $qbar=new block_delete_qbar();
			 $res=$qbar->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='questionary'){
			 require dirname(dirname(dirname(__FILE__))).'/questionary/block/delete.inc.php';
			 $questionary=new block_delete_questionary();
			 $res=$questionary->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='poll'){
			 require dirname(dirname(dirname(__FILE__))).'/poll/block/delete.inc.php';
			 $poll=new block_delete_poll();
			 $res=$poll->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='live'){
		 	require dirname(dirname(dirname(__FILE__))).'/grouplive/block/delete.inc.php';
			 $live=new block_delete_live();
			 $res=$live->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='resourcelist'){
			 require dirname(dirname(dirname(__FILE__))).'/resourcelist/block/delete.inc.php';
			 $resourcelist=new block_delete_resourcelist();
			 $res=$resourcelist->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='doc'){
			 require dirname(dirname(dirname(__FILE__))).'/groupdoc/block/delete.inc.php';
			 $doc=new block_delete_doc();
			 $res=$doc->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='activity'){
			 require dirname(dirname(dirname(__FILE__))).'/activity/block/delete.inc.php';
			 $activity=new block_delete_activity();
			 $res=$activity->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='selection'){
			 require dirname(dirname(dirname(__FILE__))).'/selection/block/delete.inc.php';
			 $activity=new block_delete_selection();
			 $res=$activity->deletedata($ids,$contenttype,$fid);
		}else{
			
		}
		return $res;
	}
}
?>