<?php
/* Function: 你我课堂分类管理
 * Com.:
 * Author: wuhan
 * Date: 2010-7-23
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
if(!isfounder()) cpmsg('noaccess_isfounder', '', 'error');

$operation = empty($operation) ? 'admin' : $operation;

if($operation == 'admin') {

	if(!submitcheck('nwktclasssubmit')) {

		$templates = '';
		$query = DB::query("SELECT * FROM ".DB::table('home_nwkt_class')."");
		while($value = DB::fetch($query)) {
			$templates .= showtablerow('', array('class="td25"', '', 'class="td29"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$value[classid]\">",
				"<input type=\"text\" class=\"txt\" size=\"8\" name=\"namenew[$value[classid]]\" value=\"$value[classname]\">",
			), TRUE);
		}

		shownav('style', 'nwktclass_admin');
		showsubmenu('nwktclass_admin');
		showformheader('nwktclass');
		showtableheader();
		showsubtitle(array('', 'nwktclass_admin_name',''));
		echo $templates;
		echo '<tr><td>'.$lang['add_new'].'</td><td><input type="text" class="txt" size="8" name="newname"></td><td>&nbsp;</td></tr>';
		showsubmit('nwktclasssubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($_G['gp_newname']) {
			DB::insert('home_nwkt_class', array('classname' => $_G['gp_newname'], 'uid' => $_G['uid'], 'dateline' => $_G['timestamp']));
		}

		foreach($_G['gp_namenew'] as $id => $classname) {
			if(!$_G['gp_delete'] || ($_G['gp_delete'] && !in_array($id, $_G['gp_delete']))) {
				DB::query("UPDATE ".DB::table('home_nwkt_class')." SET classname='{$_G['gp_namenew'][$id]}' WHERE classid='$id'", 'UNBUFFERED');
			}
		}

		if(is_array($_G['gp_delete'])) {
			if($ids = dimplode($_G['gp_delete'])) {
				DB::query("DELETE FROM ".DB::table('home_nwkt_class')." WHERE classid IN ($ids)", 'UNBUFFERED');
				DB::query("UPDATE ".DB::table('home_nwkt')." SET classid='0' WHERE classid IN ($ids)", 'UNBUFFERED');
			}
		}
		cpmsg('nwktclass_update_succeed', 'action=nwktclass', 'succeed');
	}

}
?>