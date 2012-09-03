<?php
define('APPTYPEID', 3);
define('CURSCRIPT', 'group');

require './source/class/class_core.php';

$discuz = & discuz_core :: instance();

$discuz->init();

// 增加外部专家访问权限限制
// Add by lujianqing 2010-10-08
if(isset($discuz->var['cookie']['expert_Is'])){
    $url = $discuz->var['cookie']['expert_url'];
    $message = "非常抱歉，由于您为外部专家，暂无权访问该内容";
    showmessage($message,$url);
}

include_once DISCUZ_ROOT."/source/include/misc/misc_login.php";

if (!defined('IN_DISCUZ')) {
	exit ('Access Denied');
}

include_once libfile("function/core");
include_once libfile("function/home");

$page = 1;
$perpage = 10;
$perpage = mob_perpage($perpage);
$start = ($page -1) * $perpage;
ckstart($start, $perpage);

$wheresql = '1';
$ordersql = 'l.starttime DESC';

$list = $templist = array();
$hotlist=array();
$count = DB :: result(DB :: query("SELECT COUNT(*) FROM " . DB :: table('group_live') . " WHERE $wheresql"), 0);
if ($count) {
	$query = DB :: query("SELECT l.*, f.name FROM " . DB :: table('group_live') . " l, " . DB :: table('forum_forum') . " f , " . DB :: table('forum_forumfield') . " ff WHERE $wheresql AND f.type='sub' AND l.fid = f.fid AND ff.fid=f.fid AND ff.gviewperm='1' ORDER BY $ordersql LIMIT $start,$perpage");
	$hotquery = DB :: query("SELECT l.*, f.name FROM " . DB :: table('group_live') . " l, " . DB :: table('forum_forum') . " f , " . DB :: table('forum_forumfield') . " ff WHERE $wheresql AND f.type='sub' AND l.fid = f.fid AND ff.fid=f.fid AND ff.gviewperm='1' AND l.starttime>".strtotime('-6 month')." ORDER BY l.playnum DESC LIMIT $start,3");
	while($value=DB::fetch($hotquery)){
		 if(empty($value['url'])){
                    $value['url'] = "forum.php?mod=group&action=plugin&fid=$value[fid]&plugin_name=grouplive&plugin_op=groupmenu&grouplive_action=livecp&op=join&liveid=$value[liveid]";
		 			if($value['type'] == 3){
		 				$value['url'] = $_G[config][expert][liveurl]."/live/beforelive.do?action=start&liveid=".$value['newliveid']."&fid=".$value['fid'];
		 			}
                }
		$hotlist[]=$value;
	}

	$_G['home_today'] = $_G['timestamp'] - $_G['timestamp'] % 86400;

	while ($value = DB :: fetch($query)) {

		if($value['starttime'] < ($_G['home_today'] + 86400) && $value['starttime'] >= $_G['home_today']){
			$value['day'] = '今天';
		} else {
			$value['day'] = dgmdate($value['starttime'], 'm月d日');
		}

		if($_G['timestamp'] < $value['starttime']){
			$value['activty'] = 'c';
		}elseif($_G['timestamp'] > $value['endtime']){
			$value['activty'] = 'a';
		}else{
			$value['activty'] = 'b';
		}

		$value['time'] = dgmdate($value['starttime'], 'H:i') . " - " . dgmdate($value['endtime'], 'H:i');
                if(empty($value['url'])){
                    $value['url'] = "forum.php?mod=group&action=plugin&fid=$value[fid]&plugin_name=grouplive&plugin_op=groupmenu&grouplive_action=livecp&op=join&liveid=$value[liveid]";
                    if($value['type'] == 3){
		 				$value['url'] = $_G[config][expert][liveurl]."/live/beforelive.do?action=start&liveid=".$value['newliveid']."&fid=".$value['fid'];
		 			}
                }
		$templist[] = $value;
	}

	if(!empty($templist)){
		for($i = count($templist)-1; $i >= 0; $i--){
			$list[] = $templist[$i];
		}
	}
}

// 直播广告
$query = DB::query("SELECT * FROM ".DB::table("common_advertisement")." ad WHERE ad.available=1 AND ad.title LIKE 'NL%'");
while($row=DB::fetch($query)){
    $row[parameters] = unserialize($row[parameters]);
    $return[] = $row;
}
$adtitles = array(
    "unionad"=>"NP3",
    "partymember"=>"NP4",
    "wirelessad"=>"NP5",
    "challengead"=>"NP6",
    "cdmaad"=>"NP7",
    "helpad"=>"NP1",
    "oldversion"=>"NP2",
    "NL1"=>"对话发展",
    "NL2"=>"添翼振翅",
    "NL3"=>"光点星课堂",
    "NL4"=>"天翼大讲堂",
    "indexleft11"=>"NB1",
    "indexleft2"=>"NB2",
    "indexleft3"=>"NB3",
    "indexleft4"=>"NB4",
    "indexleft5"=>"NB5",
);
include template('live/index');
?>
