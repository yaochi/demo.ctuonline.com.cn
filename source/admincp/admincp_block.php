<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_block.php 11340 2010-05-31 02:20:33Z xupeng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
$operation = in_array($operation, array('jscall', 'list', 'perm')) ? $operation : 'list';

shownav('portal', 'block');
loadcache('blockclass');

if($operation=='perm') {
	$bid = intval($_G['gp_bid']);
	if(!submitcheck('permsubmit')) {
		$block = DB::fetch_first('SELECT * FROM '.DB::table('common_block')." WHERE bid='$bid'");
		shownav('portal', 'block', 'block_perm');
		showsubmenu(cplang('block_perm_edit').' - '.($block['name'] ? $block['name'] : cplang('block_name_null')));
		showformheader("block&operation=perm&bid=$bid");

		showtableheader('', 'fixpadding');
		showsubtitle(array('', 'username',
		'<input class="checkbox" type="checkbox" name="chkalldata" onclick="checkAll(\'prefix\', this.form, \'allowdata\', \'chkalldata\')" id="chkalldata" /><label for="chkalldata"><br />'.cplang('block_perm_data').'</label>',
		'<input class="checkbox" type="checkbox" name="chkallsetting" onclick="checkAll(\'prefix\', this.form, \'allowsetting\', \'chkallsetting\')" id="chkallsetting" /><label for="chkallsetting"><br />'.cplang('block_perm_setting').'</label>'
		));

		$query = DB::query("SELECT * FROM ".DB::table('common_member')." m ,".DB::table('common_block_permission')." cp WHERE cp.bid='$bid' AND cp.uid=m.uid");
		while($value = DB::fetch($query)) {
			showtablerow('', array('class="td25"'), array(
				"<input type=\"checkbox\" class=\"checkbox\" name=\"delete[$value[uid]]\" value=\"$value[uid]\" /><input type=\"hidden\" name=\"perm[$value[uid]]\" value=\"$value[bid]\" />",
				"$value[username]",
				"<input type=\"checkbox\" class=\"checkbox\" name=\"allowdata[$value[uid]]\" value=\"1\" ".($value['allowdata'] ? 'checked' : '').' />',
				"<input type=\"checkbox\" class=\"checkbox\" name=\"allowsetting[$value[uid]]\" value=\"1\" ".($value['allowsetting'] ? 'checked' : '').' />',
			));
		}
		showtablerow('', array('class="td25"'), array(
			cplang('add_new'),
			'<input type="text" class="txt" name="newuser" value="" size="20" />',
			'<input type="checkbox" class="checkbox" name="newallowdata" value="1" />',
			'<input type="checkbox" class="checkbox" name="newallowsetting" value="1" />',
		));

		showsubmit('permsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();
	} else {

		if(!empty($_G['gp_delete'])) {
			DB::query("DELETE FROM ".DB::table('common_block_permission')." WHERE bid='$bid' AND uid IN(".dimplode($_G['gp_delete']).")");
		}

		$perms = array();
		if(is_array($_G['gp_perm'])) {
			foreach($_G['gp_perm'] as $uid => $value) {
				if(empty($_G['gp_delete']) || !in_array($uid, $_G['gp_delete'])) {
					$uid = intval($uid);
					$allowdata = $_G['gp_allowdata'][$uid] ? 1 : 0;
					$allowsetting = $_G['gp_allowsetting'][$uid] ? 1 : 0;
					$perms[] = "('$bid', '$uid', '$allowdata', '$allowsetting')";
				}
			}
		}
		if(!empty($_G['gp_newuser'])) {
			$uid = DB::result_first("SELECT uid FROM ".DB::table('common_member')." WHERE username='$_G[gp_newuser]'");
			if($uid) {
				$allowdata = $_G['gp_newallowdata'] ? 1 : 0;
				$allowsetting = $_G['gp_newallowsetting'] ? 1 : 0;
				$perms[] = "('$bid', '$uid', '$allowdata', '$allowsetting')";
			}
		}
		if(!empty($perms)) {
			DB::query("REPLACE INTO ".DB::table('common_block_permission')." (`bid`, `uid`, `allowdata`, `allowsetting`) VALUES ".implode(',', $perms));
		}

		cpmsg('block_perm_update_succeed', "action=block&operation=perm&bid=$bid", 'succeed');
	}

} else {

	if(submitcheck('deletesubmit')) {

		if($_POST['ids']) {
			DB::query('DELETE FROM '.DB::table('common_block_item')." WHERE bid IN (".dimplode($_POST['ids']).")");
			DB::query('DELETE FROM '.DB::table('common_block')." WHERE bid IN (".dimplode($_POST['ids']).")");
			cpmsg('block_delete_succeed', 'action=block&operation=jscall', 'succeed');
		} else {
			cpmsg('block_choose_at_least_one_block', 'action=block&operation=jscall', 'error');
		}

	} elseif(submitcheck('clearsubmit')) {

		include_once libfile('function/block');
		block_clear();
		cpmsg('block_clear_unused_succeed', 'action=block', 'succeed');

	} else {

		showsubmenu('block',  array(
				array('block_list', 'block', $operation=='list'),
				array('block_jscall', 'block&operation=jscall', $operation=='jscall')
			));

		$mpurl = ADMINSCRIPT.'?action=block&operation='.$operation;

		$intkeys = array('bid');
		$strkeys = array('blockclass');
		$randkeys = array();
		$likekeys = array('name');
		$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys);
		$wherearr = $results['wherearr'];
		$mpurl .= '&'.implode('&', $results['urls']);

		$wherearr[] = $operation=='jscall' ? "blocktype='1'" : "blocktype='0'";

		$wheresql = empty($wherearr)?'1':implode(' AND ', $wherearr);
		$wheresql = str_replace(array('bid', 'blockclass', 'name', 'blocktype'), array('b.bid', 'b.blockclass', 'b.name', 'b.blocktype'), $wheresql);

		$orders = getorders(array('bid', 'dateline'), 'bid');
		$ordersql = $orders['sql'];
		if($orders['urls']) $mpurl .= '&'.implode('&', $orders['urls']);
		$orderby = array($_GET['orderby']=>' selected');
		$ordersc = array($_GET['ordersc']=>' selected');

		$perpage = empty($_GET['perpage'])?0:intval($_GET['perpage']);
		if(!in_array($perpage, array(10,20,50,100))) $perpage = 20;
		$perpages = array($perpage=>' selected');
		$mpurl .= '&perpage='.$perpage;

		$searchlang = array();
		$keys = array('search', 'likesupport', 'resultsort', 'defaultsort', 'orderdesc', 'orderasc', 'perpage_10', 'perpage_20', 'perpage_50', 'perpage_100',
		'block_dateline', 'block_id', 'block_name', 'block_blockclass', 'block_add_jscall', 'block_choose_blockclass_to_add_jscall');
		foreach ($keys as $key) {
			$searchlang[$key] = cplang($key);
		}
		$blockclass_sel = '<select name="blockclass" id="blockclass">';
		$blockclass_sel .= '<option value="">'.cplang('blockstyle_blockclass_sel').'</option>';
		foreach($_G['cache']['blockclass'] as $key=>$value) {
			foreach($value['subs'] as $subkey=>$subvalue) {
				$selected = ($subkey == $_GET['blockclass'] ? ' selected' : '');
				$blockclass_sel .= "<option value=\"$subkey\"$selected>$subvalue[name]</option>";
			}
		}
		$blockclass_sel .= '</select>';
		$addjscall = $operation == 'jscall' ? '<input type="button" class="btn" onclick="addjscall()" value="'.$searchlang['block_add_jscall'].'" />' : '';
		$adminscript = ADMINSCRIPT;
		echo <<<SEARCH
			<script>disallowfloat = '{$_G[setting][disallowfloat]}';</script>
			<script type="text/javascript" src="{$_G[setting][jspath]}portal.js?{VERHASH}"></script>
			<div id="ajaxwaitid"></div>
			<form method="get" autocomplete="off" action="$adminscript">
				<div style="margin-top:8px;">
					<table cellspacing="3" cellpadding="3">
						<tr>
							<th>$searchlang[block_blockclass]</th><td colspan="3">$blockclass_sel $addjscall</td>
						</tr>
						<tr>
							<th>$searchlang[block_id]</th><td><input type="text" name="bid" value="$_GET[bid]"></td>
							<th>$searchlang[block_name]*</th><td><input type="text" name="name" value="$_GET[name]">*$searchlang[likesupport]</td>
						</tr>
						<tr>
							<th>$searchlang[resultsort]</th>
							<td colspan="3">
								<select name="orderby">
								<option value="">$searchlang[defaultsort]</option>
								<option value="dateline"$orderby[dateline]>$searchlang[block_dateline]</option>
								</select>
								<select name="ordersc">
								<option value="desc"$ordersc[desc]>$searchlang[orderdesc]</option>
								<option value="asc"$ordersc[asc]>$searchlang[orderasc]</option>
								</select>
								<select name="perpage">
								<option value="10"$perpages[10]>$searchlang[perpage_10]</option>
								<option value="20"$perpages[20]>$searchlang[perpage_20]</option>
								<option value="50"$perpages[50]>$searchlang[perpage_50]</option>
								<option value="100"$perpages[100]>$searchlang[perpage_100]</option>
								</select>
								<input type="hidden" name="action" value="block">
								<input type="hidden" name="operation" value="$operation">
								<input type="submit" name="searchsubmit" value="$searchlang[search]" class="btn">
							</td>
						</tr>
					</table>
				</div>
			</form>
			<script type="text/javascript">
			function addjscall() {
				var blockclass = $('blockclass').value;
				if(blockclass) {
					showWindow('blockclass', 'portal.php?mod=portalcp&ac=block&op=add&blocktype=1&classname=' + blockclass);
				} else {
					alert('$searchlang[block_choose_blockclass_to_add_jscall]');
				}
			}
			</script>
SEARCH;

		$start = ($page-1)*$perpage;

		showformheader('block&operation='.$operation);
		showtableheader('block_list');

		include_once libfile('function/block');
		if($operation=='jscall') {
			showsubtitle(array('', 'block_name', 'block_script', 'block_style', 'block_dateline'));
			$multipage = '';
			$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_block')." b WHERE $wheresql"), 0);
			if($count) {
				$query = DB::query("SELECT * FROM ".DB::table('common_block')." b WHERE $wheresql $ordersql LIMIT $start,$perpage");
				while($value = DB::fetch($query)) {
					$theclass = block_getclass($value['blockclass']);
					showtablerow('', array('class="td25"'), array(
						"<input type=\"checkbox\" class=\"checkbox\" name=\"ids[]\" value=\"$value[bid]\">",
						!empty($value['name']) ? $value['name'] : cplang('block_name_null'),
						$theclass['script'][$value['script']],
						$theclass['style'][$value['styleid']]['name'],
						!empty($value['dateline']) ? dgmdate($value['dateline']) : cplang('block_dateline_null'),
						"<a href=\"portal.php?mod=portalcp&ac=block&op=edit&tab=setting&bid=$value[bid]&blocktype=1\" target=\"_blank\" onclick=\"showWindow('showblock',this.href);return false;\">".cplang('edit')."</a> &nbsp;&nbsp".
						"<a href=\"javascript:;\" onclick=\"prompt('".cplang('block_copycode_message')."', '<!--{block/$value[bid]}-->')\">".cplang('block_copycode_inner')."</a> &nbsp;&nbsp".
						"<a href=\"javascript:;\" onclick=\"prompt('".cplang('block_copycode_jsmessage')."', '&lt;script type=&quot;text/javascript&quot; src=&quot;$_G[siteurl]api.php?mod=js&bid=$value[bid]&quot;&gt;&lt;/script&gt;')\">".cplang('block_copycode_outer')."</a>&nbsp;&nbsp;<a href=\"".ADMINSCRIPT."?action=block&operation=perm&bid=$value[bid]\">".cplang('portalcategory_perm').'</a>'
					));
				}
				$multipage = multi($count, $perpage, $page, $mpurl);
			}

			showsubmit('', '', '', '<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form, \'ids\')" /><label for="chkall">'.cplang('select_all').'</label>&nbsp;&nbsp;<input type="submit" class="btn" name="deletesubmit" value="'.cplang('block_delete').'" />', $multipage);
			showtablefooter();
			showformfooter();

		} else {

			showsubtitle(array('block_name', 'block_script', 'block_style', 'block_dateline', 'block_page', 'operation'));
			$multipage = '';
			$count = DB::result(DB::query("SELECT COUNT(*) FROM ".DB::table('common_block')." b WHERE $wheresql"), 0);
			if($count) {
				$query = DB::query("SELECT b.*, tb.targettplname FROM ".DB::table('common_block')." b LEFT JOIN ".DB::table('common_template_block')." tb ON b.bid=tb.bid WHERE $wheresql $ordersql LIMIT $start,$perpage");
				while($value = DB::fetch($query)) {
					$theclass = block_getclass($value['blockclass']);
					$diyurl = block_getdiyurl($value['targettplname']);
					$diypage = '';
					if ($diyurl['flag'] == 0) {
						$diypage = "<a href=\"$diyurl[url]\" target=\"_blank\">".cplang('block_page_single')."</a>";
					} elseif ($diyurl['flag'] == 1) {
						$diypage = cplang('block_page_pub');
					} elseif ($diyurl['flag'] == 2) {
						$diypage = cplang('block_page_unused');
					}
					showtablerow('', '', array(
						$value['name'] ? $value['name'] : cplang('block_name_null'),
						$theclass['script'][$value['script']],
						$theclass['style'][$value['styleid']]['name'],
						dgmdate($value['dateline']),
						$diypage,
						 "<a href=\"portal.php?mod=portalcp&ac=block&op=edit&tab=setting&bid=$value[bid]&blocktype=1\" target=\"_blank\" onclick=\"showWindow('showblock',this.href);return false;\">".cplang('edit')."</a> &nbsp;&nbsp".$diyop."&nbsp;&nbsp;<a href=\"".ADMINSCRIPT."?action=block&operation=perm&bid=$value[bid]\">".cplang('portalcategory_perm').'</a>'
					));
				}
				$multipage = multi($count, $perpage, $page, $mpurl);
			}

			showsubmit('', '', '', '<input type="submit" class="btn" name="clearsubmit" value="'.cplang('block_clear_unused').'" />', $multipage);
			showtablefooter();
			showformfooter();
		}
	}
}
?>