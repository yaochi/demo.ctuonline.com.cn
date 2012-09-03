<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_profile.php 11005 2010-05-19 08:13:38Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function profile_setting($fieldid, $space=array(), $showstatus=false) {
	global $_G;

	if(empty($_G['cache']['profilesetting'])) {
		loadcache('profilesetting');
	}
	$field = $_G['cache']['profilesetting'][$fieldid];
	if(empty($field) || !$field['available'] || in_array($fieldid, array('uid', 'constellation', 'zodiac', 'birthmonth', 'birthyear', 'birthprovince', 'resideprovince', 'residedist', 'residecommunity'))) {
		return '';
	}

	if($showstatus) {
		$uid = intval($space['uid']);
		if($uid && !isset($_G['profile_verifys'][$uid])) {
			$_G['profile_verifys'][$uid] = array();
			$query = DB::query('SELECT fieldid, newvalue FROM '.DB::table('common_member_security')." WHERE uid = '$uid'");
			while($value = DB::fetch($query)) {
				$key = $value['fieldid'];
				if($_G['cache']['profilesetting'][$key]['needverify']) {
					$_G['profile_verifys'][$uid][$key] = $value['newvalue'];
				}
			}
		}
		$verifyvalue = isset($_G['profile_verifys'][$uid][$fieldid]) ? $_G['profile_verifys'][$uid][$fieldid] : null;
	}

	$html = '';
	if($fieldid == 'birthday') {
		if($field['unchangeable'] && !empty($space[$fieldid])) {
			return '<span>'.$space['birthyear'].'-'.$space['birthmonth'].'-'.$space['birthday'].'</span>';
		}
		$birthyeayhtml = '';
		$nowy = dgmdate($_G['timestamp'], 'Y');
		for ($i=0; $i<100; $i++) {
			$they = $nowy - $i;
			$selectstr = $they == $space['birthyear']?' selected':'';
			$birthyeayhtml .= "<option value=\"$they\"$selectstr>$they</option>";
		}
		$birthmonthhtml = '';
		for ($i=1; $i<13; $i++) {
			$selectstr = $i == $space['birthmonth']?' selected':'';
			$birthmonthhtml .= "<option value=\"$i\"$selectstr>$i</option>";
		}
		$birthdayhtml = '';
		if(empty($space['birthmonth']) || in_array($space['birthmonth'], array(1, 3, 5, 7, 8, 10, 12))) {
			$days = 31;
		} elseif(in_array($space['birthmonth'], array(4, 6, 9, 11))) {
			$days = 30;
		} elseif($space['birthyear'] && (($space['birthyear'] % 400 == 0) || ($space['birthyear'] % 4 == 0 && $space['birthyear'] % 400 != 0))) {
			$days = 29;
		} else {
			$days = 28;
		}
		for ($i=1; $i<=$days; $i++) {
			$selectstr = $i == $space['birthday']?' selected':'';
			$birthdayhtml .= "<option value=\"$i\"$selectstr>$i</option>";
		}
		$html = '<select id="birthyear" name="birthyear" onchange="showbirthday();">'
				.'<option value="">'.lang('space', 'year').'</option>'
				.$birthyeayhtml
				.'</select>'
				.'&nbsp;&nbsp;'
				.'<select id="birthmonth" name="birthmonth" onchange="showbirthday();">'
				.'<option value="">'.lang('space', 'month').'</option>'
				.$birthmonthhtml
				.'</select>'
				.'&nbsp;&nbsp;'
				.'<select id="birthday" name="birthday">'
				.'<option value="">'.lang('space', 'day').'</option>'
				.$birthdayhtml
				.'</select>';

	} elseif($fieldid=='gender') {
		if($field['unchangeable'] && strlen($space[$fieldid]) > 0) {
			return '<span>'.lang('space', 'gender_'.intval($space[$fieldid])).'</span>';
		}
		$selected = array($space[$fieldid]=>' selected="selected"');
		$html = '<select name="gender" id="gender">'
				.'<option value="0"'.($space[$fieldid]=='0' ? ' selected="selected"' : '').'>'.lang('space', 'gender_0').'</option>'
				.'<option value="1"'.($space[$fieldid]=='1' ? ' selected="selected"' : '').'>'.lang('space', 'gender_1').'</option>'
				.'<option value="2"'.($space[$fieldid]=='2' ? ' selected="selected"' : '').'>'.lang('space', 'gender_2').'</option>'
				.'</select>';

	} elseif($fieldid=='realname') {
        return '<span>'.$space['realname'].'</span>';
	} elseif($fieldid=='birthcity') {
		if($field['unchangeable'] && !empty($space[$fieldid])) {
			return '<span>'.$space['birthprovince'].'-'.$space['birthcity'].'</span>';
		}
		$values = array(0, 0);
		$elems = array('birthprovince', 'birthcity');
		if(!empty($space['birthprovince'])) {
			$html = profile_show('birthcity', $space);
			$html .= '&nbsp;&nbsp;<a href="javascript:;" onclick="showdistrict(\'birthdistrictbox\', [\'birthprovince\', \'birthcity\'], 2); return false;">'.lang('spacecp', 'profile_edit').'</a>';
			$html .= '<p id="birthdistrictbox"></p>';
		} else {
			$html = '<p id="birthdistrictbox">'.showdistrict($values, $elems, 'birthdistrictbox').'</p>';
		}
	} elseif($fieldid=='residecity') {
		if($field['unchangeable'] && !empty($space[$fieldid])) {
			return '<span>'.$space['resideprovince'].'-'.$space['residecity'].'</span>';
		}
		$values = array(0,0,0,0);
		$elems = array('resideprovince', 'residecity', 'residedist', 'residecommunity');
		if(!empty($space['resideprovince'])) {
			$html = profile_show('residecity', $space);
			$html .= '&nbsp;&nbsp;<a href="javascript:;" onclick="showdistrict(\'residedistrictbox\', [\'resideprovince\', \'residecity\', \'residedist\', \'residecommunity\'], 4); return false;">'.lang('spacecp', 'profile_edit').'</a>';
			$html .= '<p id="residedistrictbox"></p>';
		} else {
			$html = '<p id="residedistrictbox">'.showdistrict($values, $elems, 'residedistrictbox').'</p>';
		}
	} else {
		if($field['unchangeable'] && $space[$fieldid]!='') {
			return '<span>'.nl2br($space[$fieldid]).'</span>';
		}
		if($field['formtype']=='textarea') {
			$html = "<textarea name=\"$fieldid\" id=\"$fieldid\" rows=\"3\" cols=\"40\" class=\"pt\">$space[$fieldid]</textarea>";
		} elseif($field['formtype']=='select') {
			$field['choices'] = explode("\n", $field['choices']);
			$html = "<select name=\"$fieldid\">";
			foreach($field['choices'] as $op) {
				$html .= "<option value=\"$op\"".($op==$space[$fieldid] ? 'selected="selected"' : '').">$op</option>";
			}
			$html .= '</select>';
		} elseif($field['formtype']=='list') {
			$field['choices'] = explode("\n", $field['choices']);
			$html = "<select name=\"$fieldid\" multiple=\"multiplue\">";
			$space[$fieldid] = explode("\n", $space[$fieldid]);
			foreach($field['choices'] as $op) {
				$html .= "<option value=\"$op\"".(in_array($op, $space[$fieldid]) ? 'selected="selected"' : '').">$op</option>";
			}
			$html .= '</select>';
		} elseif($field['formtype']=='checkbox') {
			$field['choices'] = explode("\n", $field['choices']);
			$space[$fieldid] = explode("\n", $space[$fieldid]);
			foreach($field['choices'] as $op) {
				$html .= ''
					."<input type=\"checkbox\" name=\"{$fieldid}[]\" value=\"$op\"".(in_array($op, $space[$fieldid]) ? ' checked="checked"' : '')." class=\"pc\" />"
					."<label>$op</label>&nbsp;&nbsp;";
			}
		} elseif($field['formtype']=='radio') {
			$field['choices'] = explode("\n", $field['choices']);
			foreach($field['choices'] as $op) {
				$html .= ''
						."<input type=\"radio\" name=\"{$fieldid}\" value=\"$op\"".($op == $space[$fieldid] ? ' checked="checked"' : '')." class=\"pc\" />"
						."<label>$op</label>&nbsp;&nbsp;";
			}
		} else {
			$html = "<input type=\"text\" value=\"$space[$fieldid]\" name=\"$fieldid\" class=\"px\" />";
		}
	}
	if($showstatus) {
		$html .= "<p class=\"d\">$value[description]";
		if($space[$fieldid]=='' && $value['unchangeable']) {
			$html .= '<em>'.lang('spacecp', 'profile_unchangeable').'</em>';
		}
		if($verifyvalue !== null) {
			$html .= "<strong>".lang('spacecp', 'profile_is_verifying')." (<a href=\"#\" onclick=\"$('newvalue_$fieldid').style.display='block';return false;\">".lang('spacecp', 'profile_mypost')."</a>)</strong>"
				."<p id=\"newvalue_$fieldid\" style=\"display:none\">".$verifyvalue."</p>";
		} elseif($field['needverify']) {
			$html .= '<em>'.lang('spacecp', 'profile_need_verifying').'</em>';
		}
		$html .= '</p>';
	}
	return $html;
}

function profile_check($fieldid, &$value, $space=array()) {
	global $_G;

	if(empty($_G['cache']['profilesetting'])) {
		loadcache('profilesetting');
	}
	if(empty($_G['profilevalidate'])) {
		include libfile('spacecp/profilevalidate', 'include');
		$_G['profilevalidate'] = $profilevalidate;
	}

	$field = $_G['cache']['profilesetting'][$fieldid];
	if(empty($field) || !$field['available']) {
		return false;
	}

	if($value=='') {
		if($field['required']) {
			return false;
		} else {
			return true;
		}
	}
	if($field['unchangeable'] && !empty($space[$fieldid])) {
		return false;
	}

	include_once libfile('function/home');
	if(in_array($fieldid, array('birthday', 'birthmonth', 'birthyear', 'gender'))) {
		$value = intval($value);
		return true;
	} elseif(in_array($fieldid, array('resideprovince', 'residecity', 'birthproince', 'birthcity', 'residedist', 'residecommunity'))) {
		$value = getstr($value, '', 1, 1);
		return true;
	}

	if($field['choices']) {
		$field['choices'] = explode("\n", $field['choices']);
	}
	if($field['formtype'] == 'text' || $field['formtype'] == 'textarea') {
		$value = getstr($value, '', 1, 1);
		if($field['size'] && strlen($value) > $field['size']) {
			return false;
		} else {
			$field['validate'] = !empty($field['validate']) ? $field['validate'] : ($_G['profilevalidate'][$fieldid] ? $_G['profilevalidate'][$fieldid] : '');
			if($field['validate'] && !preg_match($field['validate'], $value)) {
				return false;
			}
		}
	} elseif($field['formtype'] == 'checkbox' || $field['formtype'] == 'list') {
		$arr = array();
		foreach ($value as $op) {
			if(in_array($op, $field['choices'])) {
				$arr[] = $op;
			}
		}
		$value = implode("\n", $arr);
		if($field['size'] && count($arr) > $field['size']) {
			return false;
		}
	} elseif($field['formtype'] == 'radio' || $field['formtype'] == 'select') {
		if(!in_array($value, $field['choices'])){
			return false;
		}
	}
	return true;
}

function profile_show($fieldid, $space=array()) {
	global $_G;

	if(empty($_G['cache']['profilesetting'])) {
		loadcache('profilesetting');
	}
	$field = $_G['cache']['profilesetting'][$fieldid];
	if(empty($field) || !$field['available'] || in_array($fieldid, array('uid', 'birthmonth', 'birthyear', 'birthprovince', 'resideprovince'))) {
		return false;
	}

	if($fieldid=='gender') {
		return lang('space', 'gender_'.intval($space['gender']));
	} elseif($fieldid=='birthday') {
		$return = $space['birthyear'] ? $space['birthyear'].' '.lang('space', 'year').' ' : '';
		if($space['birthmonth'] && $space['birthday']) {
			$return .= $space['birthmonth'].' '.lang('space', 'month').' '.$space['birthday'].' '.lang('space', 'day');
		}
		return $return;
	} elseif($fieldid=='birthcity') {
		return $space['birthprovince'].'&nbsp;'.$space['birthcity'];
	} elseif($fieldid=='residecity') {
		return $space['resideprovince']
				.(!empty($space['residecity']) ? '&nbsp;'.$space['residecity'] : '')
				.(!empty($space['residedist']) ? '&nbsp;'.$space['residedist'] : '')
				.(!empty($space['residecommunity']) ? '&nbsp;'.$space['residecommunity'] : '');
	} elseif($fieldid == 'site') {
		$url = str_replace('"', '\\"', $space[$fieldid]);
		return "<a href=\"$url\" target=\"_blank\">$url</a>";
	} else {
		return nl2br($space[$fieldid]);
	}
}


function showdistrict($values, $elems=array(), $container='districtbox', $showlevel=null) {
	$showlevel = !empty($showlevel) ? intval($showlevel) : count($values);
	$showlevel = $showlevel <= 4 ? $showlevel : 4;
	$upids = array(0);
	for($i=0;$i<$showlevel;$i++) {
		if(!empty($values[$i])) {
			$upids[] = intval($values[$i]);
		} else {
			for($j=$i; $j<$showlevel; $j++) {
				$values[$j] = '';
			}
			break;
		}
	}
	$options = array(1=>array(), 2=>array(), 3=>array(), 4=>array());
	$query = DB::query('SELECT * FROM '.DB::table('common_district')." WHERE upid IN (".dimplode($upids).')');
	while($value = DB::fetch($query)) {
		$options[$value['level']][] = array($value['id'], $value['name']);
	}
	$names = array('province', 'city', 'district', 'community');
	for($i=0; $i<4;$i++) {
		$elems[$i] = !empty($elems[$i]) ? $elems[$i] : $names[$i];
	}
	$html = '';
	for($i=0;$i<$showlevel;$i++) {
		$level = $i+1;
		$jscall = "showdistrict('$container', ['$elems[0]', '$elems[1]', '$elems[2]', '$elems[3]'], $showlevel, $level)";
		$html .= '<select name="'.$elems[$i].'" id="'.$elems[$i].'" onchange="'.$jscall.'">';
		$html .= '<option value="">'.lang('spacecp', 'district_level_'.$level).'</option>';
		foreach($options[$level] as $option) {
			$selected = $option[0] == $values[$i] ? ' selected="selected"' : '';
			$html .= '<option did="'.$option[0].'" value="'.$option[1].'"'.$selected.'>'.$option[1].'</option>';
		}
		$html .= '</select>';
		$html .= '&nbsp;&nbsp;';
	}
	return $html;
}

?>