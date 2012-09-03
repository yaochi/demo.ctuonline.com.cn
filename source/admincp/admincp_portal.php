<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_group.php 11546 2010-06-08 02:20:35Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
require_once dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_home.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_portal.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_resource.php';

cpheader();


function deletedata($ids,$contenttype,$fid){
	 	if($contenttype=='topic'){
			 require dirname(dirname(dirname(__FILE__))).'/source/plugin/topic/block/delete.inc.php';
			 $topic=new block_delete_topic();
			 $res=$topic->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='album'){
			 require dirname(dirname(dirname(__FILE__))).'/source/plugin/groupalbum2/block/delete.inc.php';
			 $album=new block_delete_album();
			 $res=$album->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='qbar'){
			 require dirname(dirname(dirname(__FILE__))).'/source/plugin/qbar/block/delete.inc.php';
			 $qbar=new block_delete_qbar();
			 $res=$qbar->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='questionary'){
			 require dirname(dirname(dirname(__FILE__))).'/source/plugin/questionary/block/delete.inc.php';
			 $questionary=new block_delete_questionary();
			 $res=$questionary->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='poll'){
			 require dirname(dirname(dirname(__FILE__))).'/source/plugin/poll/block/delete.inc.php';
			 $poll=new block_delete_poll();
			 $res=$poll->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='live'){
		 	require dirname(dirname(dirname(__FILE__))).'/source/plugin/grouplive/block/delete.inc.php';
			 $live=new block_delete_live();
			 $res=$live->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='resourcelist'){
			 require dirname(dirname(dirname(__FILE__))).'/source/plugin/resourcelist/block/delete.inc.php';
			 $resourcelist=new block_delete_resourcelist();
			 $res=$resourcelist->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='doc'){
			 require dirname(dirname(dirname(__FILE__))).'/source/plugin/groupdoc/block/delete.inc.php';
			 $doc=new block_delete_doc();
			 $res=$doc->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='activity'){
			 require dirname(dirname(dirname(__FILE__))).'/source/plugin/activity/block/delete.inc.php';
			 $activity=new block_delete_activity();
			 $res=$activity->deletedata($ids,$contenttype,$fid);
		}elseif($contenttype=='selection'){
			 require dirname(dirname(dirname(__FILE__))).'/source/plugin/selection/block/delete.inc.php';
			 $activity=new block_delete_selection();
			 $res=$activity->deletedata($ids,$contenttype,$fid);
		}else  if($contenttype=='official_blog_info'){
			//官方博客
			 require dirname(dirname(dirname(__FILE__))).'/source/function/function_delete.php';
			 deleteblogs(array($ids));			 
		} else   if($contenttype=='nwkt'){
			//你我课堂
			require dirname(dirname(dirname(__FILE__))).'/source/function/function_nwkt.php';
			deletedata(array($ids),$contenttype);	
		}
		return $res;
	}
	
	
$discuz = & discuz_core::instance();

$cachelist = array('userapp', 'blockclass', 'portalcategory');
$discuz->cachelist = $cachelist;
$discuz->init();

runhooks();


$operation=getgpc('operation');



if($operation == 'indexmanager') {
	//获取首页内容
	require_once libfile("function/resource");

   require_once libfile("function/blog");

   $blocknames = array(
			'resourcelist'=>'资源列表',	
			'activity_list'=>'活动',
			'doc'=>'文档',	
            'nwkt'	=>'你我课堂'	,
            'live'=>'直播'	,
            'topic'=>'话题'	,
            'activity'=>' 活动'	,
            'groupdoc'=>' 专区文档'
		);
		
		//插件目录
		$blockpaths = array(
			'resourcelist'=>'resourcelist',
			'activity_list'=>'activity',			
			'doc'=>'doc',	
		    'nwkt'=>'nwkt',		
		    'live'=>'live',
		    'topic'=>'topic'	,	
		    'activity'=>'activity'	,
		    'groupdoc'=>'groupdoc'	,
		);
		
   //最新资源
   $newlist = getNewResourceList();
   //最热资源
   $hotlist = getHotResourceList();
   //官方博客相关信息
   //$official_blog_info = getBlog4Portal();   
   $official_blog_info = getBlog4PortalFromCache();	//从cache 中获取

   $indexList== array();
    
   // print_r($hotlist);
   
	foreach ($official_blog_info['top'] as $contenttype=>$rows ){				
		
		$listdatatemp['id']=$rows['blogid'];		
		$listdatatemp['url']='home.php?mod=space&uid='.$rows['uid'].'&do=blog&id='.$rows['blogid'].'&from=space';
		$listdatatemp['name']='['.$blocknames['official_blog_info'].']'.$rows['subject'];
		$listdatatemp['title']=$rows['subject'];
		$listdatatemp['contenttype']="official_blog_info";
		
		$temp[]=$listdatatemp ;	
		
	}
	
	foreach ($official_blog_info['new'] as $contenttype=>$rows ){				
		
		$listdatatemp['id']=$rows['blogid'];		
		$listdatatemp['url']='home.php?mod=space&uid='.$rows['uid'].'&do=blog&id='.$rows['blogid'].'&from=space';
		$listdatatemp['name']='['.$blocknames['official_blog_info'].']'.$rows['subject'];
		$listdatatemp['title']=$rows['subject'];
		$listdatatemp['contenttype']="official_blog_info";
		
		$temp[]=$listdatatemp ;	
		
	}
	
	$indexList[0]['blockclass']="official_blog_info";
	$indexList[0]['blockname']="官方博客";
	$indexList[0]['listdata']=$temp;
	
	
	
	//最新资源--课程,文档，案例
	$temp="";
	/*
	$ziyuan=array('newcourse','newsnsdoc','newcase');
	foreach ($ziyuan as $ziyuankey=>$ziyuanvalue ){	
		$listdatatemp['id']=$newlist[$ziyuanvalue]['id'];		
	    $listdatatemp['url']=$newlist[$ziyuanvalue]['titlelink'];	
	    $listdatatemp['name']=$newlist[$ziyuanvalue]['title'];
	    $listdatatemp['title']=$newlist[$ziyuanvalue]['title'];
	    $listdatatemp['contenttype']="resourcelist";
	    $temp[]=$listdatatemp ;	
	}	
	*/
	
	 
	$ziyuan=array('newcourse','newsnsdoc','newcase');
	
	foreach ($ziyuan as $ziyuankey=>$ziyuanvalue ){	
		
		for($i=1;$i<=3;$i++){
			//&newListTemp[$ziyuanvalue]['id']
			$listdatatemp['id']=$newlist[$ziyuanvalue.$i]['id'];		
	   		$listdatatemp['url']=$newlist[$ziyuanvalue.$i]['titlelink'];	
	   		$listdatatemp['name']='['.$blocknames['resourcelist'].']'.$newlist[$ziyuanvalue.$i]['title'];
	        $listdatatemp['title']=$newlist[$ziyuanvalue.$i]['title'];
	    	$listdatatemp['contenttype']="resourcelist";
	   		$temp[]=$listdatatemp ;		   
	   		
		}	    
	}	
	
	
	
	//最新资源--直播
	$listdatatemp['id']=$newlist['newlive']['liveid'];		
	$listdatatemp['url']=$newlist['newlive']['url'];	
	$listdatatemp['name']='['.$blocknames['grouplive'].']'.$newlist['newlive']['subject'];
	$listdatatemp['title']=$newlist['newlive']['title'];
	$listdatatemp['contenttype']="grouplive";
	$temp[]=$listdatatemp ;	
	//最新资源--你我课堂
	$listdatatemp['id']=$newlist['newnwkt']['nwktid'];		
	$listdatatemp['url']='home.php?mod=space&uid='.$newlist['newnwkt']['uid'].'&do=nwkt&id='.$newlist['newnwkt']['nwktid'];	
	$listdatatemp['name']='['.$blocknames['nwkt'].']'.$newlist['newnwkt']['subject'];
	$listdatatemp['title']=$newlist['newnwkt']['title'];
	$listdatatemp['contenttype']="nwkt";
	$temp[]=$listdatatemp ;	
	
	//最新资源--话题
	
	$listdatatemp['id']=$newlist['newtopic1']['tid'];		
	$listdatatemp['url']='forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&tid='.$newlist['newtopic1']['tid'];	
	$listdatatemp['name']='['.$blocknames['topic'].']'.$newlist['newtopic1']['subject'];
	$listdatatemp['title']=$newlist['newtopic1']['subject'];
	$listdatatemp['contenttype']="topic";
	$temp[]=$listdatatemp ;	
	
	//最新资源--活动
	
	$listdatatemp['id']=$newlist['newactivity']['fid'];		
	$listdatatemp['url']='forum.php?mod=group&fid='.$newlist['newactivity']['fid'];	
	$listdatatemp['name']='['.$blocknames['activity'].']'.$newlist['newactivity']['name'];
	$listdatatemp['title']=$newlist['newactivity']['name'];
	$listdatatemp['contenttype']="activity";
	$temp[]=$listdatatemp ;	
	
	
	
   $indexList[1]['blockclass']="newlist";
   $indexList[1]['blockname']="最新资源";
   $indexList[1]['listdata']=$temp;
	
   //最热资源
    $temp="";
//	$ziyuan=array('hotcourse','hotdoc','hotcase');
//	foreach ($ziyuan as $ziyuankey=>$ziyuanvalue ){	
//		$listdatatemp['id']=$hotlist[$ziyuanvalue]['id'];		
//	    $listdatatemp['url']=$hotlist[$ziyuanvalue]['titlelink'];	
//	    $listdatatemp['name']='['.$blocknames['resourcelist'].']'.$hotlist[$ziyuanvalue]['title'];
//	    $listdatatemp['title']=$hotlist[$ziyuanvalue]['title'];
//	    $listdatatemp['contenttype']="resourcelist";
//	    $temp[]=$listdatatemp ;	
//   }	
   
    $ziyuan=array('hotcourse','hotdoc','hotcase');
	
	foreach ($ziyuan as $ziyuankey=>$ziyuanvalue ){	
		
		for($i=1;$i<=3;$i++){
			//&newListTemp[$ziyuanvalue]['id']
			$listdatatemp['id']=$hotlist[$ziyuanvalue.$i]['id'];		
	   		$listdatatemp['url']=$hotlist[$ziyuanvalue.$i]['titlelink'];	
	   		$listdatatemp['name']='['.$blocknames['resourcelist'].']'.$hotlist[$ziyuanvalue.$i]['title'];
	        $listdatatemp['title']=$hotlist[$ziyuanvalue.$i]['title'];
	    	$listdatatemp['contenttype']="resourcelist";
	   		$temp[]=$listdatatemp ;	
	   		
		}	    
	}
	
	
    //最热资源--社区文档
    $listdatatemp['id']=$hotlist['hotsnsdoc']['id'];		
	$listdatatemp['url']=$hotlist['hotsnsdoc']['titlelink'];	
	$listdatatemp['name']='['.$blocknames['groupdoc'].']'.$hotlist['hotsnsdoc']['title'];
	$listdatatemp['title']=$hotlist['hotsnsdoc']['title'];
	$listdatatemp['contenttype']="groupdoc";
	$temp[]=$listdatatemp ;	
	
	//最热资源--活动1	
    $listdatatemp['id']=$hotlist['hotactivity1']['fid'];		
	$listdatatemp['url']='forum.php?mod=group&fid='.$hotlist['hotactivity1']['fid'];	
	$listdatatemp['name']='['.$blocknames['activity'].']'.$hotlist['hotactivity1']['name'];
	$listdatatemp['title']=$hotlist['hotactivity1']['name'];
	$listdatatemp['contenttype']="activity";
	$temp[]=$listdatatemp ;	
	
	//最热资源--活动2	 
    $listdatatemp['id']=$hotlist['hotactivity2']['fid'];		
	$listdatatemp['url']='forum.php?mod=group&fid='.$hotlist['hotactivity2']['fid'];	
	$listdatatemp['name']='['.$blocknames['activity'].']'.$hotlist['hotactivity2']['name'];
	$listdatatemp['title']=$hotlist['hotactivity2']['name'];
	$listdatatemp['contenttype']="activity";
	$temp[]=$listdatatemp ;	
	
	//最热资源--话题	
	$listdatatemp['id']=$hotlist['hottopic']['tid'];	
	$listdatatemp['url']='forum.php?mod=viewthread&special=0&plugin_name=topic&plugin_op=groupmenu&tid='.$hotlist['hottopic']['tid'];	
	$listdatatemp['name']='['.$blocknames['topic'].']'.$hotlist['hottopic']['subject'];
	$listdatatemp['title']=$hotlist['hottopic']['subject'];
	$listdatatemp['contenttype']="topic";
	$temp[]=$listdatatemp ;	
	
   $indexList[2]['blockclass']="hotlist";
   $indexList[2]['blockname']="最热资源";
   $indexList[2]['listdata']=$temp;
   
   //print_r($indexList);
   
   
//	foreach ($hotlist as $contenttype=>$rows ){
//			$temp['blockclass']="hotlist";
//			$temp['blockname']="最热资源";
//			$temp['listdata']=$rows ;
//			$indexList['hotlist']=$temp;
//	}			
	
		
	
   //$indexList["listadata"]=$official_blog_info;
  // $indexList["listadata"]=$newlist;
  // $indexList["listadata"]=$getHotResourceList;
  
 
   
  //echo  "显示首页内容";
  include_once template('portal/indexManager');
  //include_once template('portal/newindex');
} else if($operation == 'delete') {
	//现在不能删除
	$memkey=array('portal_index_top3newCourses','portal_index_top3newSNSDocs','portal_index_top3newCases','portal_index_newlive'
	,'portal_index_newnwkt','portal_index_newtopic1','portal_index_newtopic2','portal_index_newactivity'
	,'portal_index_top3hotCourses','portal_index_top3hotDocs','portal_index_top3hotCases'
	);
	
	if($_POST['delsubmit'] == '1'){
		$isdeletes = $_POST['is_delete']; //需要删除数据 [blockclass][contenttype][id]
		
		if($isdeletes){
			foreach ($isdeletes as $bc=> $content) {
				
					foreach ($content as $contenttype=>$rows ){
						foreach ($rows as $cid=>$row ){
//							print_r("---key----".$cid);
//							print_r("---value----".$row);
//							print_r("---contenttype----".$contenttype);							
						    deletedata($cid,$contenttype);							
						}
						
					}
			}
		}			
//
//		//return ;
//		
		/** 清除memcached
		 * 
		 */
		foreach ($memkey as $cache_key=> $value) {
			memory("rm", $value);
		}
		
		$msg = "删除成功";
		//$url='admin.php?action=portal&operation=indexmanager';
		//cpmsg($msg, $url);
		cpmsg($msg, 'action=portal&operation=indexmanager', 'succeed');		
	}

}else if($operation == 'ignore'){
	$memkey=array('portal_index_top3newCourses','portal_index_top3newSNSDocs','portal_index_top3newCases','portal_index_newlive'
	,'portal_index_newnwkt','portal_index_newtopic1','portal_index_newtopic2','portal_index_newactivity'
	,'portal_index_top3hotCourses','portal_index_top3hotDocs','portal_index_top3hotCases'
	,'portal_gblog' //  add by songsp  2011-3-14 17:20:08   官方博客cache 。删除官方博客时不需要，是因为delete时更新了。
	);
	if($_POST['delsubmit'] == '1'){
		$isdeletes = $_POST['is_delete']; //需要删除数据 [blockclass][contenttype][id]
	if($isdeletes){
			foreach ($isdeletes as $bc=> $content) {
				
					foreach ($content as $contenttype=>$rows ){
						foreach ($rows as $cid=>$row ){
							//print_r("---key----".$cid);
							//print_r("---value----".$row);
							//print_r("---contenttype----".$contenttype);							
						    //deletedata($cid,$contenttype);	
						    //增加到屏蔽表中	
						    $setarr = array(
								'contentid' => $cid,
								'contenttype' => $contenttype,
								'createuserid' => $_G['uid'],
								'createUsername' => $_G['username'],
								'createDate' => $_G['timestamp']
							);
							
							if(portalIsignore($cid,$contenttype)==  1){
							 	DB::insert('protal_ignore', $setarr);	
							}else{
								//$portalIsignoreList[$cid] = '1';
							}
						    				
						}
						
					}
			}
		}
		
		/** 
		 * 清除memcached		 * 
		 */
		foreach ($memkey as $cache_key=> $value) {
			memory("rm", $value);
		}
		
		$msg = "屏蔽成功";
//		//$url='admin.php?action=portal&operation=indexmanager';
//		//cpmsg($msg, $url);
		cpmsg($msg, 'action=portal&operation=indexmanager', 'succeed');	
	}
}


?>