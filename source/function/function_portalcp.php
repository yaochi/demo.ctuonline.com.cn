<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_portalcp.php 11542 2010-06-08 00:31:43Z monkey $
 */

function get_uploadcontent($attach) {

	$return = '';
	if($attach['isimage']) {
		$pic = pic_get($attach['attachment'], 'portal', $attach['thumb'], $attach['remote'], 0);
		$small_pic = $attach['thumb']?($pic.'.thumb.jpg'):$pic;

		$return .= '<table id="attach_list_'.$attach['attachid'].'" width="100%">';
		$return .= '<td width="90"><a href="'.$pic.'" target="_blank"><img src="'.$small_pic.'" width="80" height="80"></a></td>';
		$return .= '<td>';
		$return .= '<a href="###" onclick="insertImage(\''.$small_pic.'\', \''.$pic.'\')">'.lang('portalcp', 'insert_small_image').'</a><br>';
		$return .= '<a href="###" onclick="insertImage(\''.$pic.'\')">'.lang('portalcp', 'insert_large_image').'</a><br>';
		$return .= '<a href="###" onclick="deleteAttach(\''.$attach['attachid'].'\', \'portal.php?mod=attachment&id='.$attach['attachid'].'&op=delete\')">'.lang('portalcp', 'delete').'</a>';
		$return .= '</td>';
		$return .= '</table>';

	} else {
		$return .= '<table id="attach_list_'.$attach['attachid'].'" width="100%">';
		$return .= '<td><a href="portal.php?mod=attachment&id='.$attach['attachid'].'" target="_blank">'.$attach['filename'].'</a></td>';
		$return .= '<td>';
		$return .= '<a href="###" onclick="insertFile(\''.$attach['filename'].'\', \'portal.php?mod=attachment&id='.$attach['attachid'].'\')">'.lang('portalcp', 'insert_file').'</a><br>';
		$return .= '<a href="###" onclick="deleteAttach(\''.$attach['attachid'].'\', \'portal.php?mod=attachment&id='.$attach['attachid'].'&op=delete\')">'.lang('portalcp', 'delete').'</a>';
		$return .= '</td>';
		$return .= '</table>';
	}
	return $return;

}

function showcategoryrow($key, $level = 0, $last = '') {
	global $category, $permissioncategory, $permission;

	$value = $category[$key];
	$return = '';

	$op = '';
	if (checkperm('allowmanagearticle') || checkperm('allpublish') || $permission[$key]['allowpublish']) {
		$op .= '<a href="portal.php?mod=portalcp&ac=article&catid='.$value['catid'].'" target="_blank">'.lang('portalcp', 'article_publish').'</a>&nbsp;&nbsp;';
	}
	if (checkperm('allowmanagearticle') || checkperm('allowmanage') || $permission[$key]['allowmanage']) {
		$op .= '<a href="portal.php?mod=portalcp&ac=category&catid='.$key.'">'.lang('portalcp', 'article_manage').'</a>';
	}

	if($level == 2) {
		$class = $last ? 'lastchildcat' : 'childcat';
		$return = '<tr class="hover"><td><div class="'.$class.'">'.htmlspecialchars($value['catname']).
		'</div></td><td>'.$value[articles].'</td><td>'.$op.'</td></tr>';
	} elseif($level == 1) {
		$return = '<tr class="hover"><td><div class="cat">'.htmlspecialchars($value['catname']).
		'</td><td>'.$value[articles].'</td><td>'.$op.'</td></tr>';
		$children = checkperm('allowmanagearticle') ? $category[$key]['children'] : $permissioncategory[$key]['permissionchildren'];
		for($i=0,$L=count($children); $i<$L; $i++) {
			$return .= showcategoryrow($children[$i], 2, $i==$L-1);
		}
	} else {
		$return = '<tr class="hover"><td><div class="parentcat">'.htmlspecialchars($value['catname']).
		'</div></td><td>'.$value[articles].'</td><td>'.$op.'</td></tr>';
		$children = checkperm('allowmanagearticle') ? $category[$key]['children'] : $permissioncategory[$key]['permissionchildren'];
		for($i=0,$L=count($children); $i<$L; $i++) {
			$return .= showcategoryrow($children[$i], 1, '');
		}
	}
	return $return;
}

function showcategoryrowpush($key, $level = 0, $last = '') {
	global $category, $permissioncategory, $permission;

	$value = $category[$key];
	$return = '';



	$op = '';
	if (checkperm('allowmanagearticle') || checkperm('allpublish') || $permission[$key]['allowpublish'] || checkperm('allowmanage') || $permission[$key]['allowmanage']) {
		$value['pushurl'] = '<a href="portal.php?mod=portalcp&ac=article&catid='.$key.'&from_idtype='.$_GET['idtype'].'&from_id='.$_GET['id'].'" target="_blank" onclick="hideWindow(\''.$_G[gp_handlekey].'\)">'.htmlspecialchars($value['catname']).'</a>';
	}

	if($level == 2) {
		$class = $last ? 'lastchildcat' : 'childcat';
		$return = '<tr class="hover"><td><div class="'.$class.'">'.$value['pushurl'].'</div></td></tr>';
	} elseif($level == 1) {
		$return = '<tr class="hover"><td><div class="cat">'.$value['pushurl'].'</div></td></tr>';
		$children = checkperm('allowmanagearticle') ? $category[$key]['children'] : $permissioncategory[$key]['permissionchildren'];
		for($i=0,$L=count($children); $i<$L; $i++) {
			$return .= showcategoryrowpush($children[$i], 2, $i==$L-1);
		}
	} else {
		$return = '<tr class="hover"><td><div class="parentcat">'.$value['pushurl'].'</div></td></tr>';
		$children = checkperm('allowmanagearticle') ? $category[$key]['children'] : $permissioncategory[$key]['permissionchildren'];
		for($i=0,$L=count($children); $i<$L; $i++) {
			$return .= showcategoryrowpush($children[$i], 1, '');
		}
	}
	return $return;
}

function getallowcategory($uid){
	if (empty($uid)) return false;
	$uid = max(0,intval($uid));
	$query = DB::query('SELECT * FROM '.DB::table('portal_category_permission')." WHERE uid='$uid'");

	$permission = array();
	while($value = DB::fetch($query)) {
		if ($value['allowpublish'] || $value['allowmanage'] || $value['allowpush']) {
			$permission[$value['catid']] = $value;
		}
	}
	return $permission;
}

function getcategory ($catid = 0){

	$query = DB::query('SELECT * FROM '.DB::table('portal_category'));

	$category = array();
	while($value = DB::fetch($query)) {
		$category[$value['catid']] = $value;
	}

	foreach ($category as $key=>$value) {
		$upid = $value['upid'];
		if($upid) {
			$category[$upid]['children'][] = $key;
			$category[$key]['level'] = 1;
			while($category[$upid]) {
				$category[$key]['level'] += 1;
				$upid = $category[$upid]['upid'];
			}
		} else {
			$category[$key]['level'] = 0;
		}
	}
	if ($catid) {
		return $category[$catid];
	} else {
		return $category;
	}
}

function getpermissioncategory($category, $permission = array()) {

	$cats = array();
	foreach ($permission as $k=>$v) {
		$cur = $category[$v];

		if ($cur['level'] != 0) {
			while ($cur['level']) {
				$cats[$cur['upid']]['permissionchildren'][] = $cur['catid'];
				$cur = $category[$cur['upid']];
			}
		} else {
			$cats[$v] = array();
		}
	}

	return $cats;
}


function save_diy_data($primaltplname, $targettplname, $data, $database = false, $optype = '') {
	global $_G;
	if (empty($data) || !is_array($data)) return false;
	$file = ($_G['cache']['style_default']['tpldir'] ? $_G['cache']['style_default']['tpldir'] : './template/default').'/'.$primaltplname.'.htm';
	if (!file_exists($file)) {
		$file = './template/default/'.$primaltplname.'.htm';
	}
	$content = file_get_contents($file);
	foreach ($data['layoutdata'] as $key => $value) {
		$html = '';
		$html .= '<div id="'.$key.'" class="area">';
		$html .= getframehtml($value);
		$html .= '</div>';
		$content = preg_replace("/(\<\!\-\-\[diy\=$key\]\-\-\>).+?(\<\!\-\-\[\/diy\]\-\-\>)/is", "\\1".$html."\\2", $content);
	}
	$content = preg_replace("/(\<style id\=\"diy_style\" type\=\"text\/css\"\>).*?(\<\/style\>)/is", "\\1".$data['spacecss']."\\2", $content);
	if (!empty($data['style'])) {
		$content = preg_replace("/(\<link id\=\"style_css\" rel\=\"stylesheet\" type\=\"text\/css\" href\=\").+?(\"\>)/is", "\\1".$data['style']."\\2", $content);
	}

	$flag = $optype == 'savecache' ? true : false;

	$targettplname = $flag ? $targettplname.'_diy_preview' : $targettplname;

	if(!$flag) @unlink('./data/diy/'.$targettplname.'_diy_preview.htm');

	$tplfile ='./data/diy/'.$targettplname.'.htm';

	if (file_exists($tplfile) && !$flag) copy($tplfile, $tplfile.'.bak');

	$tplpath = dirname($tplfile);
	if (!is_dir($tplpath)) dmkdir($tplpath);
	$r = file_put_contents($tplfile, $content);

	if ($r && $database && !$flag) {
		DB::delete('common_template_block', array('targettplname'=>$targettplname));
		if (!empty($_G['curtplbid'])) {
			$values = array();
			foreach ($_G['curtplbid'] as $bid) {
				$values[] = "('$targettplname','$bid')";
			}
			if (!empty($values)) {
				DB::query("INSERT INTO ".DB::table('common_template_block')." (targettplname,bid) VALUES ".implode(',', $values));
			}
		}

		$tpldata = daddslashes(serialize($data));
		DB::query("REPLACE INTO ".DB::table('common_diy_data')." (targettplname, primaltplname, diycontent) VALUES ('$targettplname', '$primaltplname', '$tpldata')");
	}

	return $r;
}

function getframehtml($data = array()) {
	global $_G;
	$html = $style = '';
	foreach ((array)$data as $id => $content) {
		list($flag, $name) = explode('`', $id);
		if ($flag == 'frame') {
			$fattr = $content['attr'];
			$moveable = $fattr['moveable'] == 'true' ? ' move-span' : '';
			$html .= '<div id="'.$fattr['name'].'" class="'.$fattr['className'].'">';
			if (checkhastitle($fattr['titles'])) {
				$style = gettitlestyle($fattr['titles']);
				$html .= '<div class="'.implode(' ',$fattr['titles']['className']).'"'.$style.'>'.gettitlehtml($fattr['titles'], 'frame').'</div>';
			}
			foreach ((array)$content as $colid => $coldata) {
				list($colflag, $colname) = explode('`', $colid);
				if ($colflag == 'column') {
					$html .= '<div id="'.$colname.'" class="'.$coldata['attr']['className'].'">';
					$html .= '<div id="'.$colname.'_temp" class="move-span temp"></div>';
					$html .= getframehtml($coldata);
					$html .= '</div>';
				}
			}
			$html .= '</div>';
		} elseif ($flag == 'tab') {
			$fattr = $content['attr'];
			$moveable = $fattr['moveable'] == 'true' ? ' move-span' : '';
			$html .= '<div id="'.$fattr['name'].'" class="'.$fattr['className'].'">';
			$switchtype = 'click';
			foreach ((array)$content as $colid => $coldata) {
				list($colflag, $colname) = explode('`', $colid);
				if ($colflag == 'column') {
					if (checkhastitle($fattr['titles'])) {
						$style = gettitlestyle($fattr['titles']);
						$title = gettitlehtml($fattr['titles'], 'tab');
					}
					$switchtype = is_array($fattr['titles']['switchType']) && !empty($fattr['titles']['switchType'][0]) ? $fattr['titles']['switchType'][0] : 'click';
					$html .= '<div id="'.$colname.'" class="'.$coldata['attr']['className'].'"'.$style.' switchtype="'.$switchtype.'">'.$title;
					$html .= getframehtml($coldata);
					$html .= '</div>';
				}
			}
			$html .= '<div id="'.$fattr['name'].'_content" class="tb-c"></div>';
			$html .= '<script type="text/javascript">initTab("'.$fattr['name'].'","'.$switchtype.'");</script>';
			$html .= '</div>';
		} elseif ($flag == 'block') {
			$battr = $content['attr'];
			$bid = intval(str_replace('portal_block_', '', $battr['name']));
			if (!empty($bid)) {
				$html .= "<!--{block/{$bid}}-->";
				$_G['curtplbid'][$bid] = $bid;
			}
		}
	}
    
	return $html;
}
function gettitlestyle($title) {
	$style = '';
	if (is_array($title['style']) && count($title['style'])) {
		foreach ($title['style'] as $k=>$v){
			$style .= $k.':'.$v.';';
		}
	}
	$style = $style ? ' style=\''.$style.'\'' : '';
	return $style;
}
function checkhastitle($title) {
	if (!is_array($title)) return false;
	foreach ($title as $k => $v) {
		if (strval($k) == 'className') continue;
		if (!empty($v['text'])) return true;
	}
	return false;
}

function gettitlehtml($title, $type) {
	global $_G;
	if (!is_array($title)) return '';
	$html = $one = $style = $color =  '';
	foreach ($title as $k => $v) {
		if (in_array(strval($k),array('className','style'))) continue;
		if (empty($v['src']) && empty($v['text'])) continue;
		$one = "<span class=\"{$v['className']}\"";
		$style = $color = "";
		$style .= empty($v['font-size']) ? '' : "font-size:{$v['font-size']}px;";
		$style .= empty($v['float']) ? '' : "float:{$v['float']};";
		$margin_ = empty($v['float']) ? 'left' : $v['float'];
		$style .= empty($v['margin']) ? '' : "margin-{$margin_}:{$v['margin']}px;";
		$color = empty($v['color']) ? '' : "color:{$v['color']};";
		$img = !empty($v['src']) ? '<img src="'.$v['src'].'" class="vm" alt="'.$v['text'].'"/>' : '';
		if (empty($v['href'])) {
			$style = empty($style)&&empty($color) ? '' : ' style="'.$style.$color.'"';
			$one .= $style.">$img{$v['text']}";
		} else {
			$style = empty($style) ? '' : ' style="'.$style.'"';
			$colorstyle = empty($color) ? '' : ' style="'.$color.'"';
			$one .= $style.'><a href="'.$v['href'].'"'.$colorstyle.'>'.$img.$v['text'].'</a>';
		}
		$one .= '</span>';

		$siteurl = str_replace(array('/','.'),array('\/','\.'),$_G['siteurl']);
		$one = preg_replace('/\"'.$siteurl.'(.*?)\"/','"$1"',$one);

		$html = $k === 'first' ? $one.$html : $html.$one;
	}
	return $html;
}

function gettheme($type) {
	$themes = array();
	$themedirs = dreaddir(DISCUZ_ROOT."/static/$type");
	foreach ($themedirs as $key => $dirname) {
		$now_dir = DISCUZ_ROOT."/static/$type/$dirname";
		if(file_exists($now_dir.'/style.css') && file_exists($now_dir.'/preview.jpg')) {
			$themes[] = array(
				'dir' => $type.'/'.$dirname,
				'name' => getcssname($type.'/'.$dirname)
			);
		}
	}
	return $themes;
}

function getcssname($dirname) {
	$css = @file_get_contents(DISCUZ_ROOT.'./static/'.$dirname.'/style.css');
	if($css) {
		preg_match("/\[name\](.+?)\[\/name\]/i", trim($css), $mathes);
		if(!empty($mathes[1])) $name = dhtmlspecialchars($mathes[1]);
	} else {
		$name = 'No name';
	}
	return $name;
}

function checksecurity($str) {

	$filter = array(
		'/\/\*[\n\r]*(.+?)[\n\r]*\*\//is',
		'/[^a-z0-9]+/i',
	);
	$str = preg_replace($filter, '', $str);
	if(preg_match("/(expression|implode|javascript)/i", $str)) {
		showmessage('css_contains_elements_of_insecurity');
	}
	return true;
}

function block_export($bids) {
	$return = array('block'=>array(), 'style'=>array());
	if(empty($bids)) {
		return;
	}
	$styleids = array();
	$query = DB::query('SELECT * FROM '.DB::table('common_block')." WHERE bid IN (".dimplode($bids).')');
	while($value=DB::fetch($query)) {
		$value['param'] = unserialize($value['param']);
		$return['block'][$value['bid']] = $value;
		$styleids[] = intval($value['styleid']);
	}
	if($styleids) {
		$styleids = array_unique($styleids);
		$query = DB::query('SELECT * FROM '.DB::table('common_block_style')." WHERE styleid IN (".dimplode($styleids).')');
		while($value=DB::fetch($query)) {
			$value['template'] = unserialize($value['template']);
			$return['style'][$value['styleid']] = $value;
		}
	}
	return $return ;
}

function block_import($data) {
	global $_G;
	if(!is_array($data['block'])) {
		return ;
	}
	$data = daddslashes($data);
	$stylemapping = array();
	//修改by songsp 2010-11-20
	if($data['style']&& is_array($data['style']) ) {
		$hashes = $styles = array();
		foreach($data['style'] as $value) {
			$hashes[] = $value['hash'];
			$styles[$value['hash']] = $value['styleid'];
		}
		$query = DB::query('SELECT styleid, hash FROM '.DB::table('common_block_style')." WHERE hash IN (".dimplode($hashes).')');
		while($value=DB::fetch($query)) {
			$id = $styles[$value['hash']];
			$stylemapping[$id] = intval($value['styleid']);
			unset($styles[$value['hash']]);
		}
		foreach($styles as $id) {
			$style = $data['style'][$id];
			$style['styleid'] = '';
			if(is_array($style['template'])) {
				$style['template'] = dstripslashes($style['template']);
				$style['template'] = addslashes(serialize($style['template']));
			}
			$newid = DB::insert('common_block_style', $style, true);
			$stylemapping[$id] = $newid;
		}
	}

	$blockmapping = array();
	foreach($data['block'] as $block) {
		$oid = $block['bid'];
		if(!empty($block['styleid'])) {
			$block['styleid'] = intval($stylemapping[$block['styleid']]);
		}
		$block['bid'] = '';
		$block['uid'] = $_G['uid'];
		$block['username'] = $_G['username'];
		$block['dateline'] = 0;
		if(is_array($block['param'])) {
			$block['param'] = dstripslashes($block['param']);
			$block['param'] = addslashes(serialize($block['param']));
		}
		$newid = DB::insert('common_block', $block, true);
		$blockmapping[$oid] = $newid;
	}
	include_once libfile('function/cache');
	updatecache('blockclass');
	return $blockmapping;
}

function getobjbyname($name, $data) {
	if (!$name || !$data) return false;

	foreach ((array)$data as $id => $content) {
		list($type, $curname) = explode('`', $id);
		if ($curname == $name) {
			return array('type'=>$type,'content'=>$content);
		} elseif ($type == 'frame' || $type == 'tab' || $type == 'column') {
			$r = getobjbyname($name, $content);
			if ($r) return $r;
		}
	}
	return false;
}

function getframeblock($data) {
	global $_G;

	if (!isset($_G['curtplbid'])) $_G['curtplbid'] = array();
	if (!isset($_G['curtplframe'])) $_G['curtplframe'] = array();

	foreach ((array)$data as $id => $content) {
		list($flag, $name) = explode('`', $id);
		if ($flag == 'frame' || $flag == 'tab') {
			foreach ((array)$content as $colid => $coldata) {
				list($colflag, $colname) = explode('`', $colid);
				if ($colflag == 'column') {
					getframeblock($coldata,$framename);
				}
			}
			$_G['curtplframe'][$name] = array('type'=>$flag,'name'=>$name);
		} elseif ($flag == 'block') {
			$battr = $content['attr'];
			$bid = intval(str_replace('portal_block_', '', $battr['name']));
			if (!empty($bid)) {
				$_G['curtplbid'][$bid] = $bid;
			}
		}
	}
}

function getcssdata($css) {
	global $_G;
	if (empty($css)) return '';
	$reglist = array();
	foreach ((array)$_G['curtplframe'] as $value) {
		$reglist[] = '#'.$value['name'].'.*?\{.*?\}';
	}
	foreach ((array)$_G['curtplbid'] as $value) {
		$reglist[] = '#portal_block_'.$value.'.*?\{.*?\}';
	}
	$reg = implode('|',$reglist);
	preg_match_all('/'.$reg.'/',$css,$csslist);
	return implode('', $csslist[0]);
}

function import_diy($file) {
	global $_G;

	$css = '';
	$html = array();
	$arr = array();

	$content = file_get_contents($file);
	require_once libfile('class/xml');
	if (empty($content)) return $arr;
	$diycontent = xml2array($content);

	if ($diycontent) {

		foreach ($diycontent['layoutdata'] as $key => $value) {
			if (!empty($value)) getframeblock($value);
		}
		$newframe = array();
		foreach ($_G['curtplframe'] as $value) {
			$newframe[] = $value['type'].random(6);
		}

		$mapping = array();
		if (!empty($diycontent['blockdata'])) {
			$mapping = block_import($diycontent['blockdata']);
			unset($diycontent['bockdata']);
		}

		$oldbids = $newbids = array();
		if (!empty($mapping)) {
			foreach($mapping as $obid=>$nbid) {
				$oldbids[] = '#portal_block_'.$obid.' ';
				$newbids[] = '#portal_block_'.$nbid.' ';
				$oldbids[] = '[portal_block_'.$obid.']';
				$newbids[] = '[portal_block_'.$nbid.']';
				$oldbids[] = '~portal_block_'.$obid.'"';
				$newbids[] = '~portal_block_'.$nbid.'"';
			}
		}

		require_once libfile('class/xml');
		$xml = array2xml($diycontent['layoutdata'],true);
		$xml = str_replace($oldbids, $newbids, $xml);
		$xml = str_replace((array)array_keys($_G['curtplframe']), $newframe, $xml);
		$diycontent['layoutdata'] = xml2array($xml);

		$css = str_replace($oldbids, $newbids, $diycontent['spacecss']);
		$css = str_replace((array)array_keys($_G['curtplframe']), $newframe, $css);
		foreach ($diycontent['layoutdata'] as $key => $value) {
			$html[$key] = getframehtml($value);
		}
	}
	if (!empty($html)) {
		$xml = array2xml($html, true);
		require_once libfile('function/block');
		block_get_batch(implode(',', $mapping));
		foreach ($mapping as $bid) {
			$blocktag[] = '<!--{block/'.$bid.'}-->';
			$blockcontent[] = block_fetch_content($bid);
		}
		$xml = str_replace($blocktag,$blockcontent,$xml);
		$html = xml2array($xml);
		$arr = array('html'=>$html,'css'=>$css,'mapping'=>$mapping);
	}
	return $arr;
}

function checkprimaltpl($template) {
	global $_G;

	$primaltplname = DISCUZ_ROOT.$_G['cache']['style_default']['tpldir'].'/'.$template.'.htm';
	if (!file_exists($primaltplname)) {
		$primaltplname = DISCUZ_ROOT.'./template/default/'.$template.'.htm';
	}
	if (!is_file($primaltplname)) showmessage('diy_template_noexist');
	return $primaltplname;
}
?>