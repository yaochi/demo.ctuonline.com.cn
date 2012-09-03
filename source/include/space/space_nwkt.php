<?php


/* Function: 个人中心 你我课堂查询
 * Com.:
 * Author: wuhan
 * Date: 2010-7-20
 */
if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}

$minhot = $_G['setting']['feedhotmin'] < 1 ? 3 : $_G['setting']['feedhotmin'];
$page = empty ($_GET['page']) ? 1 : intval($_GET['page']);
if ($page < 1)
	$page = 1;
$id = empty ($_GET['id']) ? 0 : intval($_GET['id']);

require_once libfile('function/nwkt');

//查看某个你我课堂
if ($id) {
	$query = DB :: query("SELECT at.*, n.* FROM " . DB :: table('home_nwkt') . " n LEFT JOIN " . DB :: table('home_attachment') . " at ON at.aid=n.aid WHERE n.nwktid='$id' AND n.uid='$space[uid]'");
	$nwkt = DB :: fetch($query);
	if (empty ($nwkt)) {
		showmessage('view_to_info_did_not_exist');
	}

	if(!ckType($nwkt['uid'], $nwkt['type'], $nwkt['firstman_ids'], $nwkt['secondman_ids'], $nwkt['guest_ids'])){
		include template('home/space_privacy');
		exit ();
	}
	$feedarray=DB::fetch_first("select * from ".DB::TABLE("home_feed")." where icon='nwkt' and id=".$nwkt[nwktid]);
	$feedid=$feedarray[feedid];
//	if (!ckfriend($nwkt['uid'], $nwkt['friend'], $nwkt['target_ids'])) {
//		require_once libfile('function/friend');
//		$isfriend = friend_check($nwkt['uid']);
//		space_merge($space, 'count');
//		space_merge($space, 'profile');
//		include template('home/space_privacy');
//		exit ();
//	}
//	elseif (!$space['self'] && $nwkt['friend'] == 4) {
//		$cookiename = "view_pwd_nwkt_$nwkt[nwktid]";
//		$cookievalue = empty ($_G['cookie'][$cookiename]) ? '' : $_G['cookie'][$cookiename];
//		if ($cookievalue != md5(md5($nwkt['password']))) {
//			$invalue = $nwkt;
//			include template('home/misc_inputpwd');
//			exit ();
//		}
//	}
	
	$nwkt['going'] = ($nwkt['starttime'] <= $_G['timestamp'] && $nwkt['endtime'] >= $_G['timestamp']);
	
	$nwkt['starttime'] = dgmdate($nwkt['starttime']);
	$nwkt['endtime'] = dgmdate($nwkt['endtime']);
	
	if($nwkt['aid']) {
		if($nwkt['isimage']) {
			$nwkt['attachurl'] = ($nwkt['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'home/'.$nwkt['attachment'];
			$nwkt['thumb'] = $nwkt['attachurl'].($nwkt['thumb'] ? '.thumb.jpg' : '');
			$nwkt['width'] = $nwkt['thumb'] && $_G['setting']['thumbwidth'] < $nwkt['width'] ? $_G['setting']['thumbwidth'] : $nwkt['width'];
		}
	}
	
	$nwkt['subject'] = getstr($nwkt['subject'], 30, 0, 0, 0, 0, -1);
	
	$nwkt['firstman_names'] = getRealNameById($nwkt['firstman_ids'], ',&nbsp;');
	//$nwkt['firstman_names'] = getstr($nwkt['firstman_names'], 14, 0, 0, 0, 0, -1);//annotated by fumz,2010-9-13 15:39:37
	$nwkt['secondman_names'] = getRealNameById($nwkt['secondman_ids'], ',&nbsp;');
	if(strlen($nwkt['secondman_names'])>25){
		$nwkt['secondman_names_short']= getstr($nwkt['secondman_names'], 20, 0, 0, 0, 0, -1);//added by fumz,2010-9-13 15:39:44
	}else{
		$nwkt['secondman_names_short']=$nwkt['secondman_names'];
	}	
	$nwkt['guest_names'] = getRealNameById($nwkt['guest_ids'], ',');
	
//	$nwkt['firstman_ids'] = explode(',',$nwkt['firstman_ids']);
//	$nwkt['secondman_ids'] = explode(',',$nwkt['secondman_ids']);
//	$nwkt['guest_ids'] = explode(',',$nwkt['guest_ids']);
//	$nwkt['invite'] = (in_array($_G['uid'], $nwkt['firstman_ids']) || in_array($_G['uid'], $nwkt['secondman_ids']) || in_array($_G['uid'], $nwkt['guest_ids']));

	//你我课堂附件
	require_once libfile('function/home_attachment');
	$attachments = getattachs($nwkt['nwktid'], 'nwktid', 0, $nwkt['aid']);
	$attachlist = $attachments['attachs'];
	$imagelist = $attachments['imgattachs'];
	
	$query = DB :: query("SELECT classid, classname FROM " . DB :: table('home_nwkt_class') . " WHERE classid='$nwkt[classid]'");
	$classarr = DB :: fetch($query);

	$nwkt['message'] = nwkt_bbcode($nwkt['message']);

	$otherlist = $newlist = array ();

	$otherlist = array ();                
	$query = DB :: query("SELECT * FROM " . DB :: table('home_nwkt') . " WHERE uid='$space[uid]' ORDER BY dateline DESC LIMIT 0,6");
	while ($value = DB :: fetch($query)) {
		if ($value['nwktid'] != $nwkt['nwktid'] && empty ($value['friend'])) {
			$otherlist[] = $value;
		}
	}

	$newlist = array ();
	$query = DB :: query("SELECT * FROM " . DB :: table('home_nwkt') . " WHERE hot>='$minhot' ORDER BY dateline DESC LIMIT 0,6");
	while ($value = DB :: fetch($query)) {
		if ($value['nwktid'] != $nwkt['nwktid'] && empty ($value['friend'])) {
			$newlist[] = $value;
		}
	}

	$perpage = 20;
	$perpage = mob_perpage($perpage);

	$start = ($page -1) * $perpage;

	ckstart($start, $perpage);

	$count = $feedarray['commenttimes'];

	$list = array ();
	if ($count) {

		if ($_GET['goto']) {
			$page = ceil($count / $perpage);
			$start = ($page -1) * $perpage;
		} else {
			$cid = empty ($_GET['cid']) ? 0 : intval($_GET['cid']);
			$csql = $cid ? "cid='$cid' AND" : '';
		}
		$query = DB :: query("SELECT * FROM " . DB :: table('home_comment') . " WHERE $csql id='$feedid' AND idtype='feed' ORDER BY dateline LIMIT $start,$perpage");
		while ($value = DB :: fetch($query)) {
			$list[] = $value;
		}

		if (empty ($list) && empty ($cid)) {
			$count = getcount('home_comment', array (
				'id' => $feedid,
				'idtype' => 'feed'
			));
			db :: update('home_nwkt', array (
				'replynum' => $count
			), array (
				'nwktid' => $nwkt['nwktid']
			));
			db::update('home_feed', array('commenttimes'=>$count), array('feedid'=>$feedid));
		}
	}

	$multi = multi($count, $perpage, $page, "home.php?mod=space&uid=$nwkt[uid]&do=$do&id=$id#comment");

	if (!$space['self'] && $_G['cookie']['view_nwktid'] != $nwkt['nwktid']) {
		DB :: query("UPDATE " . DB :: table('home_nwkt') . " SET viewnum=viewnum+1 WHERE nwktid='$nwkt[nwktid]'");
		dsetcookie('view_nwktid', $nwkt['nwktid']);
	}

	$hash = md5($nwkt['uid'] . "\t" . $nwkt['dateline']);
	$id = $nwkt['nwktid'];
	$idtype = 'nwktid';

	$maxclicknum = 0;
	loadcache('click');
	$clicks = empty($_G['cache']['click']['blogid'])?array():$_G['cache']['click']['blogid'];
	$_G['cache']['click']['nwktid'] = $clicks;
	
	foreach ($clicks as $key => $value) {
		$value['clicknum'] = $nwkt["click{$key}"];
		$value['classid'] = mt_rand(1, 4);
		if($value['clicknum'] > $maxclicknum) $maxclicknum = $value['clicknum'];
		$clicks[$key] = $value;
	}

	$clickuserlist = array();
	$query = DB::query("SELECT * FROM ".DB::table('home_clickuser')."
		WHERE id='$id' AND idtype='$idtype'
		ORDER BY dateline DESC
		LIMIT 0,24");
	while ($value = DB::fetch($query)) {
		$value['clickname'] = $clicks[$value['clickid']]['name'];
		if($value['anonymity']>0){
			include_once libfile('function/repeats','plugin/repeats');
			$repeatsinfo=getforuminfo($value['anonymity']);
			$value[repeats]=$repeatsinfo;
		}
		$clickuserlist[] = $value;
	}
	
	$actives = array (
		'me' => ' class="a"'
	);

	$diymode = intval($_G['cookie']['home_diymode']);

	include_once template("diy:home/space_nwkt_view");

} else {

	//	loadcache('nwktclasses');
	//	$classes = $_G['cache']['nwktclasses'];
        // 你我课堂列表
	if (empty ($_GET['view']))
          /*
           *  你我课堂默认显示列表
           *  me - 我的课堂
           *  we - 好友的课堂
           *  all - 随便看看
           */
		$_GET['view'] = 'me';

	$perpage = 10;
	$perpage = mob_perpage($perpage);
	$start = ($page -1) * $perpage;
	ckstart($start, $perpage);

	$summarylen = 300;

	$classarr = array ();
	$list = array ();
	$userlist = array ();
	$count = $pricount = 0;
	
	$gets = array (
		'mod' => 'space',
		'uid' => $space['uid'],
		'do' => 'nwkt',
		'view' => $_GET['view'],
		'order' => $_GET['order'],
		'classid' => $_GET['classid'],
		'fuid' => $_GET['fuid'],
		'searchkey' => $_GET['searchkey'],
		'from' => $_GET['from'],
		'friend' => $_GET['friend'],
		'going' => $_GET['going'],
		'orderby' => $_GET['orderby'],
		'orderseq' => $_GET['orderseq'],
		'from'=>$_G['space'],
	);
	$theurl = 'home.php?' . url_implode($gets);
	$multi = '';

	$wheresql = '1';
	$f_index = '';
	
	$orderby = empty($_GET['orderby'])|| !in_array($_GET['orderby'], array('dateline', 'viewnum'))? 'dateline':$_GET['orderby'];
        // 按照dateline排序
	$orderseq = $_GET['orderseq']? 'ASC' : 'DESC';
	//modified by fumz,2010-11-24 17:59:01
	//begin
	$currenttimestamp=getglobal('timestamp');
	//$ordersql="case when starttime<$currenttimestamp AND endtime>$currenttimestamp then 1 else 0 end ";	
	if($orderby=='dateline'){
		$ordersql="(case when n.starttime<$currenttimestamp and n.endtime>$currenttimestamp then endtime end) desc";//正在进行的按结束时间降序排序
		$ordersql.=",(case when n.starttime>$currenttimestamp then starttime end) desc";
		$ordersql.=",(case when n.endtime<$currenttimestamp then endtime end) desc";
	}elseif($orderby=='viewnum'){
		$ordersql = "n.$orderby $orderseq";
	}	
	//end
	$need_count = true;
        // 你我课堂 随便看看
	if ($_GET['view'] == 'all') {

		if ($_GET['order'] == 'hot') {
			$wheresql .= " AND n.hot>='$minhot'";

			$orderactives = array (
				'hot' => ' class="a"'
			);
		} else {
			$orderactives = array (
				'dateline' => ' class="a"'
			);
		}

	}
        // 我的课堂
	elseif ($_GET['view'] == 'me') {

		$wheresql = "n.uid='$space[uid]'";
		//$wheresql = " OR FIND_IN_SET('$space[uid]', n.firstman_ids) OR FIND_IN_SET('$space[uid]', n.secondman_ids) OR FIND_IN_SET('$space[uid]', n.guest_ids)";

		$privacyfriend = empty ($_G['gp_friend']) ? 0 : intval($_G['gp_friend']);
		if ($privacyfriend) {
			$wheresql .= " AND n.friend='$privacyfriend'";
		}

		if ($_GET['from'] == 'space')
			$diymode = 1;

	} else {

		space_merge($space, 'field_home');

		if ($space['feedfriend']) {

			$fuid_actives = array ();

			require_once libfile('function/friend');
			$fuid = intval($_GET['fuid']);
			if ($fuid && friend_check($fuid, $space['uid'])) {
				$wheresql = "n.uid='$fuid'";
				$fuid_actives = array (
					$fuid => ' selected'
				);
			} else {
				$wheresql = "n.uid IN ($space[feedfriend])";
				$theurl = "home.php?mod=space&uid=$space[uid]&do=$do&view=we&from=".$_GET['space'];
				$f_index = '';
			}

			$query = DB :: query("SELECT * FROM " . DB :: table('home_friend') . " WHERE uid='$space[uid]' ORDER BY num DESC LIMIT 0,100");
			while ($value = DB :: fetch($query)) {
				$userlist[] = $value;
			}
		} else {
			$need_count = false;
		}
	}

	$actives = array (
		$_GET['view'] => ' class="a"'
	);

	//状态
	$going = empty ($_GET['going']) ? 0 : intval($_GET['going']);
	switch($going){
        //0 => '全部', 1=> '正在进行', 2=>'未开始', 3=> '已结束'
		case 0:
		break;
		case 1:
		$wheresql .= " AND n.starttime <= '$_G[timestamp]' AND n.endtime >= '$_G[timestamp]'";
		break;
		case 2:
		$wheresql .= " AND n.starttime > '$_G[timestamp]'";
		break;
		case 3:
		$wheresql .= " AND n.endtime < '$_G[timestamp]'";
		break;
		default:
	}

	$classid = empty ($_GET['classid']) ? 0 : intval($_GET['classid']);
	if ($classid) {
		$wheresql .= " AND n.classid='$classid'";
	}

	// 获取你我课堂的分类，全站通用
        // home_nwkt_class 你我课堂分类表
	$query = DB :: query("SELECT classid, classname FROM " . DB :: table('home_nwkt_class'));
	while ($value = DB :: fetch($query)) {
		$classarr[$value['classid']] = $value['classname'];
	}

	if ($need_count) {
		if ($searchkey = stripsearchkey($_GET['searchkey'])) {
			$wheresql .= " AND subject LIKE '%$searchkey%'";
		}

		$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('home_nwkt') . " n WHERE $wheresql"), 0);
		if ($count) {
			$query = DB :: query("SELECT at.*, n.* FROM " . DB :: table('home_nwkt') . " n $f_index LEFT JOIN " . DB :: table('home_attachment') . " at ON at.aid=n.aid WHERE $wheresql ORDER BY $ordersql LIMIT $start,$perpage");
		}
	}

	if ($count) {
		$realnames = array();
		
		while ($value = DB :: fetch($query)) {
			if(ckType($value['uid'], $value['type'], $value['firstman_ids'], $value['secondman_ids'], $value['guest_ids'])){
				$value['message'] = getstr($value['message'], $summarylen, 0, 0, 0, 0, -1);
				$value['message'] = preg_replace("/&[a-z]+\;/i", '', $value['message']);
				if ($value['attachment']){
					$value['attachurl'] = ($value['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'home/'.$value['attachment'];
					$value['thumb'] = $value['attachurl'].($value['thumb'] ? '.thumb.jpg' : '');
				}
				else{
					$value['thumb'] = "static/image/home/nwkt_empty.png";
				}
				
				$value['going'] = ($value['starttime'] <= $_G['timestamp'] && $value['endtime'] >= $_G['timestamp']);
				
				$value['dateline'] = dgmdate($value['dateline']);
				$value['starttime'] = dgmdate($value['starttime']);
				$value['endtime'] = dgmdate($value['endtime']);
				
				$value['firstman_ids'] = explode(',',$value['firstman_ids']);
				if($value['secondman_ids']){
					$value['secondman_ids'] = explode(',',$value['secondman_ids']);
				}
				$value['guest_ids'] = explode(',',$value['guest_ids']);
				$value['invite'] = (in_array($_G['uid'], $value['firstman_ids']) || in_array($_G['uid'], $value['secondman_ids']) || in_array($_G['uid'], $value['guest_ids']));
				
				$value['firstman_names'] = array();
				if(!empty($value['firstman_ids'])){
					foreach($value['firstman_ids'] as $id){
						if(!array_key_exists($id, $realnames)){
							$realname = DB::result_first("SELECT realname FROM ".DB::table('common_member_profile')." WHERE uid = '$id'");
							$realnames[$id] = $realname;
						}
						$value['firstman_names'][] = $realnames[$id];						
					}
				}
				$value['firstman_names'] = implode(',&nbsp;', $value['firstman_names']);
				$value['firstman_names'] = getstr($value['firstman_names'], 14, 0, 0, 0, 0, -1);
				
				$value['secondman_names']=array();
				if(!empty($value['secondman_ids'])){
					foreach($value['secondman_ids'] as $id){
						if(!array_key_exists($id, $realnames)){
							$realname = DB::result_first("SELECT realname FROM ".DB::table('common_member_profile')." WHERE uid = '$id'");
							$realnames[$id] = $realname;
						}
						$value['secondman_names'][] = $realnames[$id];						
					}
				}
				$value['secondman_names'] = implode(',&nbsp;', $value['secondman_names']);
				if(strlen($value['secondman_names'])>25){
					$value['secondman_names_short']= getstr($value['secondman_names'], 20, 0, 0, 0, 0, -1);
				}else{
					$value['secondman_names_short']=$value['secondman_names'];
				}	
				
				$list[] = $value;
			} else {
				$pricount++;
			}
		}

		$multi = multi($count, $perpage, $page, $theurl);
	}

	dsetcookie('home_diymode', $diymode);

	space_merge($space, 'field_home');
	if ($_GET['from'] == 'space'){
		include_once template("home/space_zone_nwkt_list");
	}else{
		include_once template("diy:home/space_nwkt_list");
	}
	

}
?>