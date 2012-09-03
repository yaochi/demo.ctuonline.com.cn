<?php
/* Function: 专区名片
 * Com.:
 * Author: yangyang
 */
require dirname(dirname(dirname(__FILE__))).'/config/config_global.php';

$fid=intval($_GET['fid']);
$uid=intval($_GET['uid']);
if(!$fid){
	$fid=intval($_POST['fid']);
}
if(!$uid){
	$uid=intval($_POST['uid']);
}
if($fid && $uid){
	$conn=mysql_connect($_config['db']['1']['dbhost'],$_config['db']['1']['dbuser'],$_config['db']['1']['dbpw']);
	mysql_query('set names "'.$_config['db']['1']['dbcharset'].'"');
	if($conn){
		mysql_select_db($_config['db']['1']['dbname']) or die("database failed");

		$query="select * from pre_forum_forum ff left join pre_forum_forumfield fff on ff.fid=fff.fid where (ff.type='sub' or ff.type='activity') and ff.fid=".$fid;

		$result=mysql_query($query) or die('query failed');
		while($value=mysql_fetch_array($result)){
			$forum['fid']=$value['fid'];
			$forum['name']=$value['name'];
			$forum['description']=$value['description'];
			if($value['icon']){
				$forum['icon']=$_config['image']['url'].'/data/attachment/group/'.$value['icon'];
			}else{
				$forum['icon']=$_config['image']['url'].'/static/image/images/def_group.png';
			}
			$forum['membernum']=$value['membernum'];
			$forum['blogs']=$value['threads'];
			$forum['viewnum']=$value['viewnum'];
			$forum['jointype']=$value['jointype'];
			$founderuid=$value['founderuid'];
		}
		mysql_free_result($result);
		/*$query="select count(*) from pre_home_feed where ( fid =".$fid." or sharetofids like '%,".$fid.",%' )";
		$result=mysql_query($query) or die('query failed');
		while($value=mysql_fetch_array($result)){
			$forum['blogs']=$value["count(*)"];
		}
		mysql_free_result($result);*/
		$query="select * from pre_forum_groupuser where uid=".$uid." and fid=".$fid;
		$result=mysql_query($query) or die('query failed');
		$forum['ismember']='';
		while($value=mysql_fetch_array($result)){
			if($value['level']=='4'||$value['level']=='2'||$value['level']=='1'){
				$forum['ismember']=1;
			}elseif($value['level']=='0'){
				$forum['ismember']=2;
			}
		}
		if($founderuid==$uid){
			$forum['ismember']=3;
		}

		if(!$forum['ismember']){
			$forum['ismember']=0;
		}
		mysql_free_result($result);
	}
}

$res['forum']=$forum;
echo json_encode($res);
?>