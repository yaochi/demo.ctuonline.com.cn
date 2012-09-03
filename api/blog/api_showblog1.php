<?php
/* Function: 根据检索参数，获取日志的详情和日志的评论
 * Com.:
 * Author: yangyang
 * Date: 2011-10-26 
 */
require dirname(dirname(dirname(__FILE__))).'/source/class/class_core.php';
require dirname(dirname(dirname(__FILE__))).'/source/function/function_feed.php';
$discuz = & discuz_core::instance();

$discuz->init();

$uid=$_G['gp_uid'];
$feedid=$_G['gp_feedid'];
$siteurl=substr($_G['siteurl'],0,strripos($_G['siteurl'],'api/blog/'));
if($feedid){
	$feed=DB::fetch_first("SELECT *  FROM ".DB::table('home_feed')." WHERE feedid =".$feedid);
	$feed=mkfeed($feed);
	if($feed[fid]){
		$feed[gviewperm]=DB::result_first("select gviewperm from ".DB::TABLE("forum_forumfield")." where fid=".$feed[fid]);
	}else{
		$feed[gviewperm]=1;
	}
	$feed[username]=user_get_user_name($feed[uid]);
	if($feed[body_data]){
	}else{
		$feed[body_data]=array("message"=>null);
	}      
	if($feed[idtype]=='feed'){
		$oldfeed=DB::fetch_first("SELECT *  FROM ".DB::table('home_feed')." WHERE feedid =".$feed[id]);
		$oldfeed=mkfeed($oldfeed);
		$oldfeed[username]=user_get_user_name($oldfeed[uid]);
		if($oldfeed[icon]=='blog'){
			$blog=DB::fetch_first("SELECT blog.* ,bf.message FROM ".DB::table('home_blog')." blog left join ".DB::table('home_blogfield')." bf on bf.blogid = blog.blogid WHERE blog.blogid =".$oldfeed[id]);
			$message=dhtmlspecialchars($blog[message]);
			if(strlen($message)!=strlen($blog[message])){
				$res['isreach']='1';
			}else{
				$res['isreach']='0';
			}
		}elseif($oldfeed[icon]=='album'){
			$res['isreach']='1';
			$oldfeed['body_general']="<div>".$oldfeed['body_general']."</div>";
			$pic="<style>img { width: 92px; height: 92px; float: left; margin: 0 5px 5px 0; }</style>";
			$query=DB::query("select * from ".DB::TABLE("home_pic")." where picid in (".$oldfeed[target_ids].")");
			while($value=DB::fetch($query)){
				$pic=$pic."<img src='".$_G['config']['image']['url'].'/'.$value[filepath]."'>";//端口需要修改
			}
		}elseif($oldfeed[icon]=='thread'){
			$query=DB::query("SELECT * FROM ".DB::table('forum_post')." WHERE tid =".$oldfeed[id]);
			while($value=DB::fetch($query)){
				if($value[first]=='1'){
					$message=dhtmlspecialchars($value[message]);
					if(strlen($message)!=strlen($value[message])){
						$res['isreach']='1';
					}else{
						$res['isreach']='0';
					}
					$res['thread']=$value;
				}else{
					$newcommentvalue[cid]=$value[pid];
					$newcommentvalue[uid]=$value[authorid];
					$newcommentvalue[id]=$value[tid];
					$newcommentvalue[dateline]=$value[dateline];
					$newcommentvalue[ip]=$value[useip];
					//$newcommentvalue[message]=$commentvalue[message];
					if(strripos($value[message],'</blockquote>')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'</blockquote>')+29);
					}elseif(strripos($value[message],'[/i]')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'[/i]')+16);
					}else{
						$newcommentvalue[message]=$value[message];
					}
					if(strripos($newcommentvalue[message],'<br />')){
						$newcommentvalue[message]=substr($newcommentvalue[message],0,strripos($newcommentvalue[message],'<br />'));
					}
					if($value[anonymity]){
						if($value[anonymity]==-1){
							$newcommentvalue[authorid]=-1;
							$newcommentvalue[realname]='匿名';
						}else{
							include_once libfile('function/repeats','plugin/repeats');
							$repeatsinfo=getforuminfo($value[anonymity]);
							$newcommentvalue[authorid]=$repeatsinfo[fid];
							$newcommentvalue[realname]=$repeatsinfo[name];
							if($repeatsinfo['icon']){
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
							}else{
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
							}
						}
					}else{
						$newcommentvalue[authorid]=$value[authorid];
						$newcommentvalue[realname]=user_get_user_name($value[authorid]);
					}
					$res['comment'][]=$newcommentvalue;
				}
			}
		}elseif($oldfeed[icon]=='reward'){
			$query=DB::query("SELECT * FROM ".DB::table('forum_post')." WHERE tid =".$oldfeed[id]);
			while($value=DB::fetch($query)){
				if($value[first]=='1'){
					$message=dhtmlspecialchars($value[message]);
					if(strlen($message)!=strlen($value[message])){
						$res['isreach']='1';
					}else{
						$res['isreach']='0';
					}
					$res['reward']=$value;
				}else{
					$newcommentvalue[cid]=$value[pid];
					$newcommentvalue[uid]=$value[authorid];
					$newcommentvalue[id]=$value[tid];
					$newcommentvalue[dateline]=$value[dateline];
					$newcommentvalue[ip]=$value[useip];
					//$newcommentvalue[message]=$commentvalue[message];
					if(strripos($value[message],'</blockquote>')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'</blockquote>')+29);
					}elseif(strripos($value[message],'[/i]')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'[/i]')+16);
					}else{
						$newcommentvalue[message]=$value[message];
					}
					if(strripos($newcommentvalue[message],'<br />')){
						$newcommentvalue[message]=substr($newcommentvalue[message],0,strripos($newcommentvalue[message],'<br />'));
					}
					if($value[anonymity]){
						if($value[anonymity]==-1){
							$newcommentvalue[authorid]=-1;
							$newcommentvalue[realname]='匿名';
						}else{
							include_once libfile('function/repeats','plugin/repeats');
							$repeatsinfo=getforuminfo($value[anonymity]);
							$newcommentvalue[authorid]=$repeatsinfo[fid];
							$newcommentvalue[realname]=$repeatsinfo[name];
							if($repeatsinfo['icon']){
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
							}else{
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
							}
						}
					}else{
						$newcommentvalue[authorid]=$value[authorid];
						$newcommentvalue[realname]=user_get_user_name($value[authorid]);
					}
					$res['comment'][]=$newcommentvalue;
				}
			}
		}elseif($oldfeed[icon]=='notice'){
			$notice=DB::fetch_first("SELECT * FROM ".DB::table('notice')." WHERE id =".$oldfeed[id]);
			$message=dhtmlspecialchars($notice[content]);
			if(strlen($message)!=strlen($notice[content])){
				$res['isreach']='1';
			}else{
				$res['isreach']='0';
			}
			$res['notice']=$notice;
		}else{
			$res['isreach']='0';
		}
		$feed[oldfeed]=$oldfeed;
		$query=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='feed' and id=".$oldfeed[feedid]);
		while($value=DB::fetch($query)){
			$res['comment'][]=$value;
		}
	}else{
		$feed[oldfeed]='null';
		if($feed[icon]=='blog'){
			$blog=DB::fetch_first("SELECT blog.* ,bf.message FROM ".DB::table('home_blog')." blog left join ".DB::table('home_blogfield')." bf on bf.blogid = blog.blogid WHERE blog.blogid =".$feed[id]);
			$message=dhtmlspecialchars($blog[message]);
			if(strlen($message)!=strlen($blog[message])){
				$res['isreach']='1';
			}else{
				$res['isreach']='0';
			}
		}elseif($feed[icon]=='album'){
			$res['isreach']='1';
			$feed['body_general']="<div>".$feed['body_general']."</div>";
			$pic="<style>img { width: 92px; height: 92px; float: left; margin: 0 5px 5px 0; }</style>";
			$query=DB::query("select * from ".DB::TABLE("home_pic")." where picid in (".$feed[target_ids].")");
			while($value=DB::fetch($query)){
				$pic=$pic."<img src='".$_G['config']['image']['url'].'/'.$value[filepath]."'>";
			}
		}elseif($feed[icon]=='thread'){
			$query=DB::query("SELECT * FROM ".DB::table('forum_post')." WHERE tid =".$feed[id]);
			while($value=DB::fetch($query)){
				if($value[first]=='1'){
					$message=dhtmlspecialchars($value[message]);
					if(strlen($message)!=strlen($value[message])){
						$res['isreach']='1';
					}else{
						$res['isreach']='0';
					}
					$res['thread']=$value;
				}else{
					$newcommentvalue[cid]=$value[pid];
					$newcommentvalue[uid]=$value[authorid];
					$newcommentvalue[id]=$value[tid];
					$newcommentvalue[dateline]=$value[dateline];
					$newcommentvalue[ip]=$value[useip];
					//$newcommentvalue[message]=$commentvalue[message];
					if(strripos($value[message],'</blockquote>')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'</blockquote>')+29);
					}elseif(strripos($value[message],'[/i]')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'[/i]')+16);
					}else{
						$newcommentvalue[message]=$value[message];
					}
					if(strripos($newcommentvalue[message],'<br />')){
						$newcommentvalue[message]=substr($newcommentvalue[message],0,strripos($newcommentvalue[message],'<br />'));
					}
					if($value[anonymity]){
						if($value[anonymity]==-1){
							$newcommentvalue[authorid]=-1;
							$newcommentvalue[realname]='匿名';
						}else{
							include_once libfile('function/repeats','plugin/repeats');
							$repeatsinfo=getforuminfo($value[anonymity]);
							$newcommentvalue[authorid]=$repeatsinfo[fid];
							$newcommentvalue[realname]=$repeatsinfo[name];
							if($repeatsinfo['icon']){
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
							}else{
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
							}
						}
					}else{
						$newcommentvalue[authorid]=$value[authorid];
						$newcommentvalue[realname]=user_get_user_name($value[authorid]);
					}
					$res['comment'][]=$newcommentvalue;
				}
			}
		}elseif($feed[icon]=='reward'){
			$query=DB::query("SELECT * FROM ".DB::table('forum_post')." WHERE tid =".$feed[id]);
			while($value=DB::fetch($query)){
				if($value[first]=='1'){
					$message=dhtmlspecialchars($value[message]);
					if(strlen($message)!=strlen($value[message])){
						$res['isreach']='1';
					}else{
						$res['isreach']='0';
					}
					$res['reward']=$value;
				}else{
					$newcommentvalue[cid]=$value[pid];
					$newcommentvalue[uid]=$value[authorid];
					$newcommentvalue[id]=$value[tid];
					$newcommentvalue[dateline]=$value[dateline];
					$newcommentvalue[ip]=$value[useip];
					//$newcommentvalue[message]=$commentvalue[message];
					if(strripos($value[message],'</blockquote>')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'</blockquote>')+29);
					}elseif(strripos($value[message],'[/i]')){
						$newcommentvalue[message]=substr($value[message],strripos($value[message],'[/i]')+16);
					}else{
						$newcommentvalue[message]=$value[message];
					}
					if(strripos($newcommentvalue[message],'<br />')){
						$newcommentvalue[message]=substr($newcommentvalue[message],0,strripos($newcommentvalue[message],'<br />'));
					}
					if($value[anonymity]){
						if($value[anonymity]==-1){
							$newcommentvalue[authorid]=-1;
							$newcommentvalue[realname]='匿名';
						}else{
							include_once libfile('function/repeats','plugin/repeats');
							$repeatsinfo=getforuminfo($value[anonymity]);
							$newcommentvalue[authorid]=$repeatsinfo[fid];
							$newcommentvalue[realname]=$repeatsinfo[name];
							if($repeatsinfo['icon']){
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
							}else{
								$newcommentvalue['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
							}
						}
					}else{
						$newcommentvalue[authorid]=$value[authorid];
						$newcommentvalue[realname]=user_get_user_name($value[authorid]);
					}
					$res['comment'][]=$newcommentvalue;
				}
			}
		}elseif($feed[icon]=='notice'){
			$notice=DB::fetch_first("SELECT * FROM ".DB::table('notice')." WHERE id =".$feed[id]);
			$message=dhtmlspecialchars($notice[content]);
			if(strlen($message)!=strlen($notice[content])){
				$res['isreach']='1';
			}else{
				$res['isreach']='0';
			}
			$res['notice']=$notice;
		}else{
			$res['isreach']='0';
		}
		if($feed[icon]=='blog'){
		}else{
			$query=DB::query("select * from ".DB::TABLE("home_comment")." where idtype ='feed' and id=".$feedid);
			while($value=DB::fetch($query)){
				if($value[anonymity]){
					if($value[anonymity]==-1){
						$newcommentvalue[authorid]=-1;
						$newcommentvalue[realname]='匿名';
					}else{
						include_once libfile('function/repeats','plugin/repeats');
						$repeatsinfo=getforuminfo($value[anonymity]);
						$newcommentvalue[authorid]=$repeatsinfo[fid];
						$newcommentvalue[realname]=$repeatsinfo[name];
						if($repeatsinfo['icon']){
							$newcommentvalue['ficon']=$_G[config]['image']['url'].'/data/attachment/group/'.$repeatsinfo['icon'];
						}else{
							$newcommentvalue['ficon']=$_G[config]['image']['url'].'/static/image/images/def_group.png';
						}
					}
				}
				$res['comment'][]=$value;
			}
		}
	}
	
}
$res['blog']=$blog;
$res['pic']=$pic;
$res['feed']=$feed;

echo json_encode($res);

?>