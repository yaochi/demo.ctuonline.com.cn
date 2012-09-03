<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/home');
require_once libfile('function/space');
require_once libfile('function/spacecp');
require_once libfile('function/discuzcode');

$selectionid = empty($_GET['selectionid'])?0:intval($_GET['selectionid']);
global  $_G;
$mod=$_GET['mod'];
$anonymity=DB::result_first("select anonymity from ".DB::TABLE("home_feed")." where icon='selection' and id=".$selectionid);
if($anonymity>0){
	include_once libfile('function/repeats','plugin/repeats');
	$repeats=getforuminfo($anonymity);
}
$query=DB::query("SELECT * FROM ".DB::TABLE("selection")." WHERE selectionid='$selectionid' AND fid='$_G[fid]' AND moderated!='-1'");
 	$selection = DB::fetch($query);

	if($selection){

	
		if(submitcheck('answersubmit')){
			if(empty($_G['uid'])){
				showmessage('你还没有登录','member.php?mod=logging&action=login');
			}

			$query=DB::result_first("SELECT count(*) FROM ".DB::TABLE("selection_record")." WHERE selectionid='$selectionid' AND uid='$_G[uid]'");
			
			if($_G['timestamp']<$selection[selectionstartdate]){
				showmessage('评选还未开始！','forum.php?mod='.$mod.'&action=plugin&fid='.$_G[fid].'&plugin_name=selection&plugin_op=groupmenu&selectionid='.$selectionid.'&selection_action=answer');
			}
			if($selection[selectionenddate]<$_G['timestamp']){
				showmessage('评选已经结束！','forum.php?mod='.$mod.'&action=plugin&fid='.$_G[fid].'&plugin_name=selection&plugin_op=groupmenu&selectionid='.$selectionid.'&selection_action=answer');
			}
			if($query==0){				
				$selection['scored'] = $selection['scored'] + 1;
				DB::update('selection', $selection, 'selectionid='.$selectionid);	
			}
			if($selection[voteNum]==1){				
				$query = DB :: query("SELECT * FROM " . DB :: table('selection_user_vote_num') . " WHERE selectionid='$selectionid' AND uid='$_G[uid]' LIMIT 1");
				$uservotenum = DB :: fetch($query);
				$canusenum = $uservotenum['num'] - $uservotenum['usednum'];
				if($canusenum==0){
					showmessage('您的选票已用完！','forum.php?mod='.$mod.'&action=plugin&fid='.$_G[fid].'&plugin_name=selection&plugin_op=groupmenu&selectionid='.$selectionid.'&selection_action=answer');
				}
				//单选框
				$optionids=$_POST['optionids'];
				$record = array();
				$record['selectionid'] = $selectionid;
				$record['optionid'] = $optionids;
				$record['optiondescr'] = $_POST['optiondescr_'.$optionids];
				$record['uid'] = $_G['uid'];
				if(!$selection['showrecordflag']){
					//不使用无记名投票
					$record['username'] = $_G['username'];
				}
				
				$record['dateline']= $_G['timestamp'];
				DB::insert('selection_record', $record, 1);
			
				$query=DB::query("SELECT * FROM ".DB::TABLE("selection_option")." WHERE optionid='$optionids' AND fid='$_G[fid]' AND moderated!='-1'");
				$option = DB::fetch($query);
				$option['scored'] = $option['scored'] + 1;
				DB::update('selection_option', $option, 'optionid='.$optionids);
				
				$uservotenum['usednum'] = $uservotenum['usednum'] + 1;
				DB::query("update " . DB :: table('selection_user_vote_num') ."  set usednum = usednum + 1 where  id=".$uservotenum['id']);
				
			}else if($selection['voteNum']>1&&!$selection['voterepeatflag']){
				//复选框
				$optionids=$_POST['optionids'];
				$query = DB :: query("SELECT * FROM " . DB :: table('selection_user_vote_num') . " WHERE selectionid='$selectionid' AND uid='$_G[uid]' LIMIT 1");
				$uservotenum = DB :: fetch($query);
				$canusenum = $uservotenum['num'] - $uservotenum['usednum'];
				if($canusenum<count($optionids)){
					showmessage('对不起，您只能投'.$canusenum."票",'forum.php?mod='.$mod.'&action=plugin&fid='.$_G[fid].'&plugin_name=selection&plugin_op=groupmenu&selectionid='.$selectionid.'&selection_action=answer');
				}
				foreach($optionids as $optionid){
					$record = array();
					$record['selectionid'] = $selectionid;
					$record['optionid'] = $optionid;
					$record['optiondescr'] = $_POST['optiondescr_'.$optionid];
					$record['uid'] = $_G['uid'];
					if(!$selection['showrecordflag']){
					//不使用无记名投票
					$record['username'] = $_G['username'];
					}
					$record['dateline']= $_G['timestamp'];
					$res=DB::result_first("select count(*) from ".DB::table("selection_record")." where optionid=".$optionid." and uid=".$_G[uid]);
					DB::insert('selection_record', $record, 1);
			
					$query=DB::query("SELECT * FROM ".DB::TABLE("selection_option")." WHERE optionid='$optionid' AND fid='$_G[fid]' AND moderated!='-1'");
					$option = DB::fetch($query);
					if(!$res){
						$option['scored'] = $option['scored'] + 1;
					}
					DB::update('selection_option', $option, 'optionid='.$optionid);
					
					
					$uservotenum['usednum'] = "".($uservotenum['usednum'] + 1);
					DB::query("update " . DB :: table('selection_user_vote_num') ."  set usednum = usednum + 1 where  id=".$uservotenum['id']);
					 
				}
				
				if(!$selection['votebatchflag']){
					$uservotenum['usednum'] = "".($uservotenum['num']);
					DB::query("update " . DB :: table('selection_user_vote_num') ."  set usednum = num where  id=".$uservotenum['id']);
				}
			}else if($selection['voteNum']>1&&$selection['voterepeatflag']){
				$query = DB :: query("SELECT * FROM " . DB :: table('selection_user_vote_num') . " WHERE selectionid='$selectionid' AND uid='$_G[uid]' LIMIT 1");
				$uservotenum = DB :: fetch($query);
				$canusenum = $uservotenum['num'] - $uservotenum['usednum'];
				if($canusenum==0){
					showmessage('您的选票已用完！','forum.php?mod='.$mod.'&action=plugin&fid='.$_G[fid].'&plugin_name=selection&plugin_op=groupmenu&selectionid='.$selectionid.'&selection_action=answer');
				}
				$optionids=$_POST['optionids'];
				 
				$record = array();
				$record['selectionid'] = $selectionid;
				$record['optionid'] = $optionids;
				$record['optiondescr'] = $_POST['optiondescr_'.$optionids];
				$record['uid'] = $_G['uid'];
				if(!$selection['showrecordflag']){
					//不使用无记名投票
					$record['username'] = $_G['username'];
				}
				
				$record['dateline']= $_G['timestamp'];
				DB::insert('selection_record', $record, 1);
			
				$query=DB::query("SELECT * FROM ".DB::TABLE("selection_option")." WHERE optionid='$optionids' AND fid='$_G[fid]' AND moderated!='-1'");
				$option = DB::fetch($query);
				$option['scored'] = $option['scored'] + 1;
				DB::update('selection_option', $option, 'optionid='.$optionids);
				
				$uservotenum['usednum'] = $uservotenum['usednum'] + 1;
				DB::query("update " . DB :: table('selection_user_vote_num') ."  set usednum = usednum + 1 where  id=".$uservotenum['id']);
				//exit();
				 
			}

			showmessage('提交成功','forum.php?mod='.$mod.'&action=plugin&fid='.$_G[fid].'&plugin_name=selection&plugin_op=groupmenu&selectionid='.$selectionid.'&selection_action=answer');
			include template("selection:answer");
			 
			//积分
			/*require_once libfile("function/credit");
			credit_create_credit_log($_G['uid'],"joinselectionionnaire",$selectionid);
			
			//经验值
			require_once libfile("function/group");
			group_add_empirical_by_setting($_G['uid'],$_G[fid], 'selectionionnaire_join', $selectionionid);
			//feed
			require_once libfile('function/feed');
			$tite_data = array('username' => '<a href="home.php?mod=space&uid='.$uid.'">'.user_get_user_name_by_username($username).'</a>', 'selectionname' => '<a href="forum.php?mod='.$mod.'&action=plugin&fid='.$_G[fid].'&plugin_name=selection&plugin_op=groupmenu&diy=&selectionid='.$selectionid.'&selection_action=answer">'.$selectionname.'</a>');
			feed_add('selection', 'feed_selection_answer', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $_G['uid'], $username);
			
			DB::query("UPDATE ".db::table('selection')." SET joiner='$joiner' WHERE selectionid='$selectionid' AND fid='$_G[fid]'");
			if($visible==0){
				showmessage('回答成功',join_plugin_action('index'));
			}else{
				showmessage('回答成功',join_plugin_action('selectioncp'));
			}*/
		}else{
		
			$query = DB :: query("SELECT * FROM " . DB :: table('selection') . " WHERE selectionid='$selectionid' AND fid='$_G[fid]' LIMIT 1");
			$selection = DB :: fetch($query);
			$selection[selectiondescr]=discuzcode($selection[selectiondescr],-1,0,1,1,1,1,1);
			
			$query = DB :: query("SELECT * FROM " . DB :: table('selection_user_vote_num') . " WHERE selectionid='$selectionid' AND uid='$_G[uid]' LIMIT 1");
			$uservotenum = DB :: fetch($query);
			 
			
			 
			 
			 
			if(empty($uservotenum)){
				$uservotenum = array();
				$uservotenum['uid'] = $_G['uid'];
				$uservotenum['selectionid'] = $selectionid;
				$uservotenum['num'] = $selection['voteNum'];
				$uservotenum['dateline']= $_G['timestamp'];
				$uservotenum = DB :: insert("selection_user_vote_num",$uservotenum,1);
				$canusenum = $selection['voteNum'];
			}else{
				//判断用户距离上次投票是否过了一个周期以上，如果过了一个周期以上，则更新用户可以投的票数，并更新时间
				$cycle=0;
				 
				if($selection['votecreatetime']!=0){
					$lever = "".($_G['timestamp']-$uservotenum['dateline']);
					//$time = "".($_G['timestamp']-$selection['dateline']);
					if($selection[votecreatetype]==0){
						//分钟
						//$miniute = floor($time % (60));
						$lever = floor($lever/60);
						 
						if($lever>$selection[votecreatetime]){
							//如果距离上次生成投票的时间已经有一个周期以上了
							$cycle = 1;
						}
						 
					}else if($selection[votecreatetype]==1){
						//小时
						//$hour = floor(leave / (60*60));
						$lever = floor(leave / (60*60));
						if($lever>$selection[votecreatetime]){
							//如果距离上次生成投票的时间已经有一个周期以上了
							$cycle = 1;
						}
					}else if($selection[votecreatetype]==2){
						//天
						//$day = floor(leave / (60*60*24));
						$lever = floor(leave / (60*60*24));
						if($lever>$selection[votecreatetime]){
							//如果距离上次生成投票的时间已经有一个周期以上了
							$cycle = 1;
						}
					}
					
					if($cycle>0){
						$uservotenum['num'] = $selection['voteNum'];
						$uservotenum['usednum'] = 0;
						$uservotenum['dateline']= $_G['timestamp'];
						$uservotenum = DB :: update("selection_user_vote_num",$uservotenum,"id=".$uservotenum['id']);
						$canusenum = $selection['voteNum'];
					}else{
						
						if($uservotenum['usednum'] != 0){
							//表示已经投过票了，再次进来的话不让投票了
							$uservotenum['usednum'] = $selection['voteNum'];
							$uservotenum = DB :: update("selection_user_vote_num",$uservotenum,"id=".$uservotenum['id']);
							$canusenum = 0;
						}
					}
				
				} else{
					$canusenum = ($uservotenum['num']-$uservotenum['usednum']);
				}
				
			}

			
			//$query = DB :: query("SELECT * FROM " . DB :: table('selection_user_vote_num') . " WHERE selectionid='$selectionid' AND uid='$_G[uid]' LIMIT 1");
//			$uservotenum = DB :: fetch($query);
//			$canusenum = ($uservotenum['num']-$uservotenum['usednum']);
 			
			
			require_once libfile('function/org'); 		
			 //路径按照实际情况修改 
			 // 根据用户的id获取该用户组织信息 
			// $org_id = getUserGroupByuserId($selection['uid']); 
			 //取得省公司的名称
			// $provinceArray=getOrgById($org_id); 
			// $orgname = mb_convert_encoding($provinceArray[0]['name'], "UTF-8", "GBK");
			// $selection['userorg'] = $orgname;
 
			if($selection[ordertype]==1){
				//笔画排序
				$optionquery=DB :: query("SELECT * FROM " . DB :: table('selection_option') . " WHERE selectionid='$selectionid' order by convert( optionname USING big5)");
			}else if($selection[ordertype]==2){
				//拼音排序
				$optionquery=DB :: query("SELECT * FROM " . DB :: table('selection_option') . " WHERE selectionid='$selectionid' order by convert(optionname using GBK)");
			}else{
				$optionquery=DB :: query("SELECT * FROM " . DB :: table('selection_option') . " WHERE selectionid='$selectionid' order by ordernum ");
			}
			 
			$query = DB::query("SELECT * FROM ".DB::table("forum_forum")." WHERE fid=".$_G['fid']);
			$group = DB::fetch($query);
			
			
			if($group['type']=='activity'){
				$isgroupuser=group_is_group_member($group['fup'],$_G['uid']); 
			}else if($group['type']=='sub'){ 
				$isgroupuser=group_is_group_member($group['fid'],$_G['uid']); 
			}
			//if(!empty($_G['fup'])&&$_G['fup']!=0){
				//$isgroupuser=group_is_group_member($_G['fid'],$_G['uid']); 
//			}else{
//				$isgroupuser=group_is_group_member($_G['fup'],$_G['uid']); 
//			}
			 
			 
			 
			
			$cananswerflag = false;
			while($value = DB :: fetch($optionquery)){
				if(!empty($value['optionlimitid'])&&$value['optionlimitid']!=0){
					require_once dirname(dirname(dirname(__FILE__)))."/api/lt_org/group.php";
					$groupmgr = new Group();
					$value['cananswer'] = $groupmgr->checkUserInGroups($_G['uid'],$value['optionlimitid']);
					unset($groupmgr);
					if($value['cananswer']){
						$cananswerflag = true||$cananswerflag;
					}
				}else{
					$value['cananswer'] = 1;
					$cananswerflag = true||$cananswerflag;
				}
				if($canusenum<=0){
					$value['cananswer'] = 0;
					$cananswerflag = false;
				}
				if(!$isgroupuser){
					$value['cananswer'] = 0;
					$canusenum==0;
					$cananswerflag = false;
				}
				$value[optiondescrbase] = $value[optiondescr];
				$value[optiondescr] = discuzcode($value[optiondescr],-1,0,1,1,1,1,1);
				$selectionionlist[]=$value;
		
			}
			
			
						 	//获得专区信息
			$query = DB::query("SELECT t.name, tt.icon FROM ".DB::table("forum_forum")." t, ".DB::table("forum_forumfield")." tt 
					 WHERE t.fid=tt.fid AND t.fid=".$_G['fid']);
			$group = DB::fetch($query);
			$group['icon'] = get_groupimg($group['icon'], 'icon');
			$nowtime = $_G['timestamp'];
				
			include template("selection:answer");
		
		}
	}else{
		showmessage('您查看的评选已被删除','forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name=selection&plugin_op=groupmenu');
	}
	

dexit();
?>
