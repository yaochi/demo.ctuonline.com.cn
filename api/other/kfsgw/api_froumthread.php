<?php
/* Function: 社区中学员帖子的接口
 * Com.:
 * Author: yangyang
 * Date: 2010-10-11
 */
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/class/class_core.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/function/function_home.php';
require dirname(dirname(dirname(dirname(__FILE__)))).'/source/function/function_doc.php';
$discuz = & discuz_core::instance();
$discuz->init();
$method=strtolower($_SERVER["REQUEST_METHOD"]);

$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
if ($page < 1)
	$page = 1;
$perpage = empty ($_GET['pagesize']) ? 20 : intval($_GET['pagesize']);
if ($perpage < 0)
	$perpage = 20;
$perpage = mob_perpage($perpage);
$start = ($page -1) * $perpage;
ckstart_max($start, $perpage);

$flag=$_GET['flag'];
if(!empty($_GET['uid'])){
	 $username = $_GET['uid'];
	 $usersql=" AND author='$username' ";
}
if(!empty($_GET['fid'])){
	 $fid = intval($_GET['fid']);
	 $fidsql=" AND fid='$fid' ";
}
if(!empty($_GET['starttime'])){
	 $starttime = $_GET['starttime'];
	 $startsql=" AND dateline>'$starttime' ";
}
if(!empty($_GET['endtime'])){
	 $endtime = $_GET['endtime'];
	 $endsql=" AND dateline<'$endtime' ";
}

if($flag=='kfsgw'){
	$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('forum_thread')." WHERE special='0' $usersql $fidsql $startsql $endsql "),0);
	if($count){
		$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE special='0' $usersql $fidsql $startsql $endsql ORDER BY dateline LIMIT $start,$perpage");
		while ($value = DB::fetch($query)) {
			$value[realname]=user_get_user_name_by_username($value[author]);
			$listvalue[username]=$value[realname];
			$listvalue[threadtitile]=$value[subject];
			$listvalue[createtime]=$value[dateline];
			$listvalue[viewnum]=$value[views];
			$listvalue[url]="http://home.myctu.cn/forum.php?mod=viewthread&fid=$value[fid]&special=0&plugin_name=topic&plugin_op=groupmenu&tid=$value[tid]&extra=page%3D1";
			$livearray[] = $listvalue;
		}
		//print_r(json_encode($livearray));
		echo json_encode($livearray);
	}else{
		echo json_encode(array());
	}
}else{
	echo("您未被授权！");
}

?>