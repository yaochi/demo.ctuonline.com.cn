<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_block.php 11486 2010-06-04 02:59:31Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('function/block');
$op = in_array($_GET['op'], array('setting', 'remove', 'item', 'blockclass', 'getblock', 'thumbsetting', 'push', 'saveblockclassname', 'saveblocktitle')) ? $_GET['op'] : 'block';
$allowmanage = $allowadd = 0;
if(!empty($_G['group']['allowaddtopic']) || !empty($_G['group']['allowdiy']) || !empty($_G['group']['allowmanagetopic'])) {
	$allowadd = 1;
}

$block = $settings = array();
$bid = intval($_GET['bid']);
if($bid) {
	$block = DB::fetch_first("SELECT b.*, tb.targettplname FROM ".DB::table('common_block')." b LEFT JOIN ".DB::table('common_template_block')." tb ON b.bid = tb.bid  WHERE b.bid='$bid'");
	if(empty($block)) {
		showmessage('block_not_exist');
	}
	$_G['block'][$bid] = $block;
	if(strexists($block['targettplname'], 'portal/portal_topic_content')) {
		if(!empty($_G['group']['allowmanagetopic'])) {
			$allowmanage = 1;
		} elseif($_G['group']['allowaddtopic']) {
			$id = str_replace('portal/portal_topic_content_', '', $block['targettplname']);
			$topic = DB::fetch_first('SELECT uid FROM '.DB::table('portal_topic')." WHERE topicid='".intval($id)."'");
			if($topic['uid'] == $_G['uid']) {
				$allowmanage = 1;
			}
		}
	} else {
		if(!empty($_G['group']['allowdiy'])) {
			$allowmanage = 1;
		}
	}
}

require './source/function/function_forum.php';
loadforum();
if($_G['forum']['ismoderator']){
    $allowmanage = 1;
}

if($bid && !$allowmanage) {
	$blockpermission = DB::fetch_first('SELECT * FROM '.DB::table('common_block_permission')." WHERE bid='$bid' AND uid='$_G[uid]'");
	$blockdataperm = $blockpermission && $blockpermission['allowdata'] ? true : false;
	$blocksettingperm = $blockpermission && $blockpermission['allowsetting'] ? true : false;
	if ($blockdataperm || $blocksettingperm) $allowmanage = 1;
} else {
	$blockdataperm = $blocksettingperm = true;
}

$block['param'] = empty($block['param'])?array():unserialize($block['param']);
if(empty($block['bid'])) {
	$bid = 0;
}

$_GET['classname'] = !empty($_GET['classname']) ? $_GET['classname'] : ($block ? $block['blockclass'] : 'html_html');
$theclass = block_getclass($_GET['classname']);
$theclass['script'] = isset($theclass['script']) ? $theclass['script'] : array();

$_GET['script'] = !empty($_GET['script']) && isset($theclass['script'][$_GET['script']])
		? $_GET['script']
		: (!empty($block['script']) ? $block['script'] : key($theclass['script']));

$theclass['style'] = isset($theclass['script'][$_GET['script']]['style']) ? $theclass['script'][$_GET['script']]['style'] : array();
$_GET['styleid'] = (isset($_GET['styleid']) && isset($theclass['style'][$_GET['styleid']]))
		? $_GET['styleid']
		: (!empty($block['styleid']) ? $block['styleid'] : key($theclass['style']));
$thestyle = $theclass['style'][$_GET['styleid']];
$is_htmlblock = ($_GET['classname'] == 'html_html') ? 1 : 0;
$is_pluginmenublock = ($_GET['classname'] == 'pluginmenu') ? 1 : 0;   //组件菜单
$is_foruminfoblock = ($_GET['classname'] == 'foruminfo') ? 1 : 0;   //专区信息
if($is_foruminfoblock == 0){	
	$is_foruminfoblock = ($_GET['classname'] == 'forumremark') ? 1 : 0;   //专区描述
}
if($_GET['classname'] == 'forumimg') $is_forumimg = 1;


//不使用cahce的模块 添加 lecturerecord  授课记录   添加 by songsp 2011-3-30 16:07:55
$nocachetime = in_array($_GET['script'], array('lecturerecord_lecturerecord','blank', 'line', 'banner', 'vedio', 'google')) ? true : false;



$blocktype = (!empty($_GET['blocktype']) || !empty($block['blocktype'])) ? 1 : 0;

$stylesetting = block_style_setting($_GET['script'], $thestyle[key], $block['param']);

if($op == 'block') {
	if($bid && !$allowmanage) {
		showmessage('block_edit_nopermission');
	}
	if(!$bid) {
		list($tpl, $id) = explode(':', $_GET['tpl']);
		if(trim($tpl)=='portal/portal_topic_content') {
			if(!$_G['group']['allowaddtopic'] && !$_G['group']['allowmanagetopic']) {
				showmessage('block_edit_nopermission');
			}
		} elseif(!$_G['group']['allowdiy'] && !$_G['forum']['ismoderator']) {
			showmessage('block_edit_nopermission');
		}
	}

	if(submitcheck('blocksubmit')) {

		$_POST['cachetime'] = intval($_POST['cachetime']);
		$_POST['shownum'] = intval($_POST['shownum']);
//		$_POST['blockheight'] = intval($_POST['blockheight']);
		$_POST['picwidth'] = $_POST['picwidth'] ? intval($_POST['picwidth']) : 0;
		$_POST['picheight'] = $_POST['picheight'] ? intval($_POST['picheight']) : 0;
		$_POST['script'] = isset($theclass['script'][$_POST['script']]) ?
							$_POST['script'] : key($theclass['script']);
		$_POST['a_target'] = in_array($_POST['a_target'], array('blank', 'top', 'self')) ? $_POST['a_target'] : 'blank';

		$_POST['shownum'] = $_POST['shownum'] > 0 ? $_POST['shownum'] : 10;
//		$_POST['blockheight'] = $_POST['blockheight'] > 0 ? $_POST['blockheight'] : null;
		$_POST['parameter']['items'] = $_POST['shownum'];
		$_POST['parameter']['blockheight'] = $_POST['blockheight'];
		include_once libfile('function/home');
		$setarr = array(
			'name' => getstr($_POST['name'], 255, 1, 1, 1, 0, 0),
			'summary' => getstr($_POST['summary'], '', 1, 1, 1, 0, 1),
			'styleid' => $_POST['styleid'],
			'script' => $_POST['script'],
			'param' => addslashes(serialize($_POST['parameter'])),
			'cachetime' => intval($_POST['cachetime']),
			'shownum' => $_POST['shownum'],
			'blockheight' => $_POST['blockheight'],
			'picwidth' => $_POST['picwidth'] && $_POST['picwidth'] > 8 && $_POST['picwidth'] < 1960 ? $_POST['picwidth'] : 0,
			'picheight' => $_POST['picheight'] && $_POST['picheight'] > 8 && $_POST['picheight'] < 1960 ? $_POST['picheight'] : 0,
			'target' => $_POST['a_target'],
			'dateline' => TIMESTAMP
		);
		if($bid) {
			DB::update('common_block', $setarr, array('bid'=>$bid));
		} else {
			$setarr['blockclass'] = $_GET['classname'];
			$setarr['uid'] = $_G['uid'];
			$setarr['username'] = $_G['username'];
			if($blocktype == 1) {
				$setarr['blocktype'] = '1';
			}
			$bid = DB::insert('common_block', $setarr, true);
		}
		$_G['block'][$bid] = DB::fetch_first("SELECT * FROM ".DB::table('common_block')." WHERE bid='$bid'");
		block_updatecache($bid, true);
		showmessage('do_success', 'portal.php', array('bid'=>$bid, 'eleid'=> $_GET['eleid']));

	} elseif(submitcheck('updatesubmit')) {
		if(isset($_POST['bannedids']) && $block['param']['bannedids'] != $_POST['bannedids']) {
			$block['param']['bannedids'] = $_POST['bannedids'];
			DB::update('common_block', array('param'=>addslashes(serialize($block['param']))), array('bid'=>$bid));
			$_G['block'][$bid] = $block;
		}
		block_updatecache($bid, true);
		showmessage('do_success', 'portal.php', array('bid'=>$bid, 'eleid'=> $_GET['eleid']));
	}

	$block['script'] = isset($block['script']) ? $block['script'] : $_GET['script'];
	$settings = block_setting($block['script'], $block['param']);
	$scriptarr = array($block['script'] => ' selected');
	$stylearr = array($_GET['styleid'] => ' selected');

	$block = block_checkdefault($block);
	$cachetimearr = array($block['cachetime'] =>' selected="selected"');
	$targetarr[$block['target']] = ' selected';

	$itemlist = array();
	if($bid) {
		$query = DB::query('SELECT * FROM '.DB::table('common_block_item')." WHERE bid = '$bid'");
		while($value = DB::fetch($query)) {
			$itemlist[$value['displayorder']] = $value;
		}
		ksort($itemlist);
	}

	$showhtmltip = ($bid && $_GET['tab']=='data' && $is_htmlblock) ? true : false;
	$tab = ($bid && $_GET['tab'] != 'setting' && !$is_htmlblock) ? 'data' : 'setting';

	$block['summary'] = htmlspecialchars($block['summary']);

	$block['param']['bannedids'] = !empty($block['param']['bannedids']) ? stripslashes($block['param']['bannedids']) : '';

} elseif($op == 'setting') {

	/*if(($bid && !$allowmanage) || (!$bid && !$allowadd)) {
		showmessage('block_edit_nopermission');
	}*/

	if($theclass['script'][$_GET['script']]) {
		$settings = block_setting($_GET['script'], $block['param']);
	}

	$block['script'] = isset($block['script']) ? $block['script'] : $_GET['script'];
	$settings = block_setting($block['script'], $block['param']);
	$scriptarr = array($block['script'] => ' selected');
	$stylearr = array($_GET['styleid'] => ' selected');

	$block = block_checkdefault($block);
	$cachetimearr = array($block['cachetime'] =>' selected="selected"');
	$targetarr[$block['target']] = ' selected';

} elseif($op == 'thumbsetting') {

	/*if(($bid && !$allowmanage) || (!$bid && !$allowadd)) {
		showmessage('block_edit_nopermission');
    }*/
    
	$block = block_checkdefault($block);
	$cachetimearr = array($block['cachetime'] =>' selected="selected"');
	$targetarr[$block['target']] = ' selected';
    
} elseif($op == 'remove') {

	if(!$bid || !$allowmanage) {
		showmessage('block_edit_nopermission');
	}

	if($block && $_GET['itemid']) {
		$_GET['itemid'] = intval($_GET['itemid']);
		$archive = DB::fetch_first('SELECT * FROM '.DB::table('common_block_item')." WHERE itemid='$_GET[itemid]' AND bid='$bid'");
		if($archive) {
			$archive = daddslashes($archive);
			DB::query('REPLACE INTO '.DB::table('common_block_item_archive')
				."(bid, id, idtype, title, url, pic, summary, showstyle, related, `fields`, displayorder, startdate, enddate) VALUES "
				."('$archive[bid]', '$archive[id]', '$archive[idtype]', '$archive[title]',"
				."'$archive[url]', '$archive[pic]', '$archive[summary]', '$archive[showstyle]', '$archive[related]',"
				."'$archive[fields]', '$archive[displayorder]', '$archive[startdate]', '$archive[enddate]')");

			DB::query('DELETE FROM '.DB::table('common_block_item')." WHERE itemid='$_GET[itemid]'");
			if($archive['itemtype'] != '1') {
				$parameters = !empty($block['param']) ? $block['param'] : array();
				$bannedids = !empty($parameters['bannedids']) ? explode(',', $parameters['bannedids']) : array();
				$bannedids[] = intval($archive['id']);
				$bannedids = array_unique($bannedids);
				$parameters['bannedids'] = implode(',', $bannedids);
				$parameters = addslashes(serialize($parameters));
				DB::update('common_block', array('param'=>$parameters), array('bid'=>$bid));
				$_G['block'][$bid] = DB::fetch_first('SELECT * FROM '.DB::table('common_block')." WHERE bid='$bid'");
			}
			block_updatecache($bid, true);
		}
		showmessage('do_success', "portal.php?mod=portalcp&ac=block&op=block&bid=$bid&tab=data");
	}
} elseif($op == 'item' || $op=='push') {

	if(!$bid || !$allowmanage) {
		showmessage('block_edit_nopermission');
	}

	$itemid = $_GET['itemid'] ? intval($_GET['itemid']) : 0;
	$item = array();
	if($op == 'item') {
		if($itemid) {
			$item = DB::fetch_first('SELECT * FROM '.DB::table('common_block_item')." WHERE itemid='$itemid'");
			$itemid = intval($item['itemid']);
			$item['fields'] = unserialize($item['fields']);
		}
	} else {
		$_GET['id'] = intval($_GET['id']);
		$script = '';
		if($_GET['idtype'] == 'tids') {
			$script = 'thread';
		} elseif($_GET['idtype'] == 'aids') {
			$script = 'article';
		} elseif($_GET['idtype'] == 'picids') {
			$script = 'pic';
		} elseif($_GET['idtype'] == 'blogids') {
			$script = 'blog';
		}
		if($_GET['id'] && $script) {
			$obj = block_script($script);
			if(is_object($obj)) {
				$paramter = array($_GET['idtype'] => $_GET['id']);
				$return = $obj->getData($thestyle, $paramter);
				if($return['data']) {
					$item = $return['data'][0];
				}
			}
		}
	}

	if($item['picflag'] == '1') {
		$item['pic'] = $item['pic'] ? $_G['setting']['attachurl'].$item['pic'] : '';
	} elseif($item['picflag'] == '2') {
		$item['pic'] = $item['pic'] ? $_G['setting']['ftp']['attachurl'].$item['pic'] : '';
	}
	$item['picflag'] = '0';

	$item['startdate'] = $item['startdate'] ? dgmdate($item['startdate']) : dgmdate(TIMESTAMP);
	$item['enddate'] = $item['enddate'] ? dgmdate($item['enddate']) : '';
	$orders = range(1, $block['shownum']);
	$orderarr[$item['displayorder']] = ' selected="selected"';
	$item['showstyle'] = $item['showstyle'] ? unserialize($item['showstyle']) : array();
	$showstylearr = array();
	foreach(array('title_b', 'title_i', 'title_u', 'title_c', 'summary_b', 'summary_i', 'summary_u', 'title_c') as $value) {
		if(!empty($item['showstyle'][$value])) {
			$showstylearr[$value] = 'class="a"';
		}
	}

	$itemfields = $item;
	unset($itemfields['fields']);
	$item['fields'] = (array)$item['fields'];
	foreach($item['fields'] as $key=>$value) {
		if($theclass['fields'][$key]) {
			switch($theclass['fields'][$key]['datatype']) {
				case 'date':
					$itemfields[$key] = dgmdate($value);
					break;
				case 'int':
					$itemfields[$key] = intval($value);
				default:
					$itemfields[$key] = $value;
			}
		}
	}

	if(submitcheck('itemsubmit')) {
		$item['bid'] = $block['bid'];
		$item['displayorder'] = intval($_POST['displayorder']);
		$item['startdate'] = $_POST['startdate'] ? dstrtotime($_POST['startdate']) : TIMESTAMP;
		$item['enddate'] = $_POST['enddate'] ? dstrtotime($_POST['enddate']) : 0;
		$item['itemtype'] = empty($itemid) || !empty($_POST['locked']) ? '1' : '2';
		$item['title'] = htmlspecialchars($_POST['title']);
		$item['url'] = $_POST['url'];
		$item['summary'] = $_POST['summary'];
		if($_FILES['pic']['tmp_name']) {
			$result = pic_upload($_FILES['pic'], 'portal');
			$item['pic'] = 'portal/'.$result['pic'];
			$item['picflag'] = $result['remote'] ? '2' : '1';
			$item['makethumb'] = 0;
		} elseif($_POST['pic']) {
			$item['pic'] = $_POST['pic'];
			$item['picflag'] = intval($_POST['picflag']);
			$item['makethumb'] = 0;
		}
		$item['showstyle'] = $_POST['showstyle'] ? dstripslashes($_POST['showstyle']) : array();
		$item['showstyle'] = daddslashes(serialize($item['showstyle']));

		foreach($theclass['fields'] as $key=>$value) {
			if(!isset($item[$key]) && isset($_POST[$key])) {
				if($value['datatype'] == 'int') {
					$_POST[$key] = intval($_POST[$key]);
				} elseif($value['datatype'] == 'date') {
					$_POST[$key] = dstrtotime($_POST[$key]);
				} else {
					$_POST[$key] = dstripslashes($_POST[$key]);
				}
				$item['fields'][$key] = $_POST[$key];
			}
		}
		$item['fields']	= addslashes(serialize($item['fields']));

		$archive = array();
		if($item['startdate'] > $_G['timestamp']) {
			DB::insert('common_block_item', $item, false, true);
		} elseif(!$item['enddate'] || $item['enddate'] > $_G['timestamp']) {
			$archive = DB::fetch_first('SELECT * FROM '.DB::table('common_block_item')." WHERE bid='$block[bid]' AND displayorder='$item[displayorder]'");
			DB::insert('common_block_item', $item, false, true);
		} else {
			$archive = DB::fetch_first('SELECT * FROM '.DB::table('common_block_item')." WHERE bid='$block[bid]' AND displayorder='$item[displayorder]'");
		}
		if($archive && $archive['itemid'] != $item['itemid']) {
			DB::query('REPLACE INTO '.DB::table('common_block_item_archive')
				."(bid, id, idtype, title, url, pic, summary, showstyle, related, `fields`, displayorder, startdate, enddate) VALUES "
				."('$archive[bid]', '$archive[id]', '$archive[idtype]', '$archive[title]',"
				."'$archive[url]', '$archive[pic]', '$archive[summary]', '$archive[showstyle]', '$archive[related]',"
				."'$archive[fields]', '$archive[displayorder]', '$archive[startdate]', '$archive[enddate]')");
			DB::query('DELETE FROM '.DB::table('common_block_item')." WHERE itemid='$archive[itemid]'");
		}
		block_updatecache($bid, true);
		showmessage('do_success', 'portal.php?mod=portalcp&ac=block&op=block&bid='.$block['bid'].'&tab=data', array('bid'=>$bid));
	}

} elseif ($op == 'getblock') {

	if(!$bid || !$allowmanage) {
		showmessage('block_edit_nopermission');
	}
	
	block_get_batch($bid);
	block_updatecache($bid, !empty($_GET['forceupdate']));
	if(strexists($block['summary'], '<script')) {
		$block['summary'] = lang('portalcp', 'block_diy_nopreview');
		$_G['block'][$bid] = $block;
		$_G['block'][$bid]['cachetime'] = 0;
		$_G['block'][$bid]['nocache'] = true;
	}
	
	$html = block_fetch_content($bid, $block['blocktype'], true);

} elseif ($op == 'saveblockclassname') {

	if(!$bid || !$allowmanage) {
		showmessage('block_edit_nopermission');
	}

	if (submitcheck('saveclassnamesubmit')) {
		$setarr = array('classname'=>$_POST['classname']);
		DB::update('common_block',$setarr,array('bid'=>$bid));
	}
	block_memory_clear($bid);
	$cache_key = "block_".$bid;
    memory("rm", $cache_key);
	showmessage('do_success');
} elseif ($op == 'saveblocktitle') {
    
	if (submitcheck('savetitlesubmit')) {
		$title = dstripslashes($_POST['title']);
		$title = preg_replace('/url\([\'"](.*?)[\'"]\)/','url($1)',$title);

		$_G['siteurl'] = str_replace(array('/','.'),array('\/','\.'),$_G['siteurl']);
		$title = preg_replace('/\"'.$_G['siteurl'].'(.*?)\"/','"$1"',$title);

		$setarr = array('title'=>daddslashes($title));
		DB::update('common_block',$setarr,array('bid'=>$bid));
	}

	block_memory_clear($bid);
    $cache_key = "block_".$bid;
    memory("rm", $cache_key);
	showmessage('do_success');
}

include_once template("portal/portalcp_block");

function block_checkdefault($block) {
	if(empty($block['shownum'])) {
		$block['shownum'] = 10;
	}
	if(!isset($block['cachetime'])) {
		$block['cachetime'] = '7200';  //默认2小时 原来1个小时（3600）
	}
	if(empty($block['picwidth'])) {
		$block['picwidth'] = "200";
	}
	if(empty($block['picheight'])) {
		$block['picheight'] = "200";
	}
	if(empty($block['target'])) {
		$block['target'] = "blank";
	}
	if($block['blockclass']=='groupfeed'){//当是动态是默认高度300
		if(empty($block['blockheight'])){
			$block['blockheight'] = "300";
		}
	}
	/*if(empty($block['blockheight'])) {  //模块高度默认值
		$block['blockheight'] = "100";
	}*/
	return $block;
}

?>