<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_block.php 11486 2010-06-04 02:59:31Z xupeng $
 */

function block_script($script) {
	global $_G;
	$var = "block_class_$script";

	if(!isset($_G[$var])) {
		if(@include libfile("block/$script", 'class')) {
                    $blockclass = "block_$script";


                    $_G[$var] = new $blockclass();

		} else {
                    $blockclass=join_load_plugin_block($script);

                    if($blockclass){
                        $_G[$var] = new $blockclass();
                    }else{
                        $_G[$var] = null;
                    }
		}
	}
	return $_G[$var];
}

function block_get_batch($parameter) {
	global $_G;

	$bids = array();
	$in_bids = $parameter?explode(',', $parameter):array();
	foreach ($in_bids as $bid) {
		$bid = intval($bid);
		if($bid) $bids[$bid] = $bid;
	}

	if($bids) {
		$items = $prelist = array();

		// delete by songsp 2011-3-21 16:23:48  common_block_item未使用
		/*$query = DB::query("SELECT * FROM ".DB::table('common_block_item')." WHERE bid IN (".dimplode($bids).")");
		while ($item = DB::fetch($query)) {
			if($item['enddate'] && $item['enddate'] < TIMESTAMP) {
				continue;
			} elseif(!$item['startdate'] || $item['startdate'] <= TIMESTAMP) {
				if (!empty($items[$item['bid']][$item['displayorder']])) {
					$prelist[$item['bid']][$item['displayorder']] = $item;
				}
				$items[$item['bid']][$item['displayorder']] = $item;
			}
		}*/

		$query = DB::query("SELECT * FROM ".DB::table('common_block')." WHERE bid IN (".dimplode($bids).")");


		//当前专区首页所有的block id  add by songsp  2011-3-23 16:15:53   性能优化
		$_G['myself']['group_index']['block_ids'] = dimplode($bids);




		while ($block = DB::fetch($query)) {
			if($items[$block['bid']]) {
				ksort($items[$block['bid']]);
				$newitem = array();
				if (!empty($prelist[$block['bid']])) {
					$countpre = 0;
					foreach ($items[$block['bid']] as $position => $item) {
						if (empty($prelist[$position])) {
							$newitem[$position + $countpre] = $item;
						} else {
							if ($item['itemtype']=='1') {
								if ($prelist[$position]['startdate'] > $item['startdate']) {
									$newitem[$position] = $prelist[$position];
								} else {
									$newitem[$position] = $item;
								}
							} else {
								$newitem[$position] = $prelist[$position];
								$newitem[$position+$countpre] = $item;
								$countpre++;
							}
						}
					}
					ksort($newitem);
				}
				$block['itemlist'] = empty($newitem) ? $items[$block['bid']] : $newitem;
			}
			$_G['block'][$block['bid']] = $block;

		}
	}

}

function block_display_batch($bid) {
        if(join_plugin_block_is_display($bid)){
            echo block_fetch_content($bid);
			//echo block_fetch_content($bid,false, true);
        }
}

function block_fetch_content($bid, $isjscall=false, $forceupdate=false) {
	global $_G;
	static $allowmem = null;

	if($allowmem === null) {
		$allowmem = getglobal('setting/memory/diyblockoutput/enable') && memory('check');
	}

	$str = '';
	$block = empty($_G['block'][$bid])?array():$_G['block'][$bid];
	if(!$block) {
		return;
	}
    $cache_key = "block_".$bid;
    $cache = memory("get", $cache_key);

    //对专区组件菜单，专区描述，专区信息, diy工具特殊,授课记录处理，本身不需要设置cache时间
    $special_plugins = array('pluginmenu','foruminfo','forumremark','lecturerecord','grouptop');//,'html_html',




    if(!empty($cache) ){

    	if(in_array($block['blockclass'],$special_plugins)){

    		//处理特殊的专区diy模块内容
			if($block['blockclass']!='pluginmenu'){	
    			return parse_special_block($block['blockclass'],unserialize($cache));
			}
    		//return unserialize($cache);
    	}else if($block[dateline]+$block[cachetime]>=TIMESTAMP && !$forceupdate){
    		return unserialize($cache);
    	}

    }


    //add by songsp 2011-3-30 19:48:01
    if(in_array($block['blockclass'],$plugins)){

    	$_G['myself']['nocache_blockclass'] = true;
    }



   //print_r("=====2222".$bid);
    $forceupdate = true;
	if($forceupdate) {
		block_updatecache($bid, true);
		$block = $_G['block'][$bid];
	} elseif($block['cachetime'] > 0 && $_G['timestamp'] - $block['dateline'] > $block['cachetime']) {
		if($isjscall) {
            block_updatecache($bid, true);
			$block = $_G['block'][$bid];
		} elseif(empty($_G['blockupdate']) || $block['dateline'] < $_G['blockupdate']['dateline']) {
			$_G['blockupdate'] = array('bid'=>$bid, 'dateline'=>$block['dateline']);
		}
	} elseif($block['cachetime']==0){


        block_updatecache($bid, true);
    }

	/*if($allowmem) {
		$str = memory('get', 'blockcache_'.$bid.'_'.($isjscall ? 'js' : 'htm'));
		if($str !== null) {
			return $str;
		}
	}*/

	if($isjscall) {
		if($block['summary']) $str .= $block['summary'];
		$str .= block_template($bid);
	} else {
		$classname = !empty($block['classname']) ? $block['classname'].' ' : '';
		$str .= "<div id=\"portal_block_$bid\" class=\"{$classname}block move-span\">";
		if($block['title']) $str .= $block['title'];
		$str .= '<div id="portal_block_'.$bid.'_content" class="content">';
		/*if($block['summary']) {
			$block['summary'] = stripslashes($block['summary']);
			$str .= "<div class=\"portal_block_summary\">$block[summary]</div>";
		}*/

		$str .= block_template($bid);
		$str .= '</div>';
		$str .= "</div>";
	}

	/*if($allowmem && empty($block['nocache'])) {
		memory('set', 'blockcache_'.$bid.'_'.($isjscall ? 'js' : 'htm'), $str, getglobal('setting/memory/diyblockoutput/ttl'));
	}*/
	//print_r("*****".$str);
	memory("set", $cache_key, serialize($str));

	$str = parse_special_block($block['blockclass'],$str);

	return $str;
}

function block_memory_clear($bid) {
	if(memory('check')) {
		memory('rm', 'blockcache_'.$bid.'');
		memory('rm', 'blockcache_'.$bid.'_htm');
		memory('rm', 'blockcache_'.$bid.'_js');
		memory('rm', 'block_'.$bid);  //添加 by songsp 性能优化 2011-3-29 17:42:09
	}
}

function block_updatecache($bid, $forceupdate=false) {
	global $_G;


	if(!$forceupdate && discuz_process::islocked('block_update_cache', 5)) {
		return false;
	}

	block_memory_clear($bid);
	$block = empty($_G['block'][$bid])?array():$_G['block'][$bid];
	if(!$block) {
		return ;
	}

	$obj = block_script($block['script']);

	if(is_object($obj)) {


	/*
	去掉优化
	if($block['cachetime']>0){  //add by songsp 2011-3-21   如果使用缓存，更新缓存时间


			$block_ids = $_G['myself']['group_index']['block_ids'];
			if($block_ids ){//批量更新

				if(!$_G['myself']['group_index']['cache_flush']){ //未更新过
					DB::update('common_block', array('dateline'=>TIMESTAMP), " bid IN  (".$block_ids.")");
					$_G['myself']['group_index']['cache_flush']= 1; //已经刷新过专区首页block cache
				}

			}else{

				DB::update('common_block', array('dateline'=>TIMESTAMP), array('bid'=>$bid));
			}
		}

		*/
		if($block['cachetime']>0){
			DB::update('common_block', array('dateline'=>TIMESTAMP), array('bid'=>$bid));
		}
		$block['param'] = empty($block['param'])?array():unserialize($block['param']);
		$theclass = block_getclass($block['blockclass']);
		if($block['blockclass']=='portal_article') {
			$parameter = array('aids'=>array());
			$query = DB::query('SELECT aid FROM '.DB::table('portal_article_title')." WHERE bid='$bid'");
			while($value=DB::fetch($query)) {
				$parameter['aids'][] = intval($value['aid']);
			}
			$datalist = array();
			if(!empty($parameter['aids'])) {
				$bannedids = !empty($block['param']['bannedids']) ? explode(',', $block['param']['bannedids']) : array();
				if(!empty($bannedids)) {
					$parameter['aids'] = array_diff($parameter['aids'], $bannedids);
				}
				$bannedids = array_merge($bannedids, $parameter['aids']);
				$block['param']['bannedids'] = implode(',',$bannedids);

				$parameter['aids'] = implode(',', $parameter['aids']);
				$return = $obj->getdata($theclass["script"][$block["script"]]["style"][$block['styleid']], $parameter);
				$datalist = $return['data'];
			}
			if(count($datalist) < $block['shownum']) {
				$return = $obj->getdata($theclass["script"][$block["script"]]["style"][$block['styleid']], $block['param']);
				$return['data'] = array_merge($datalist, $return['data']);
			} else {
				$return['data'] = $datalist;
			}
		} else {
			$return = $obj->getdata($theclass["script"][$block["script"]]["style"][$block['styleid']], $block['param']);
		}

		if($return['data'] === null) {
			$_G['block'][$block['bid']]['summary'] = $return['html'];
			DB::update('common_block', array('summary'=>daddslashes($return['html'])), array('bid'=>$bid));
		} else {
            $_G['block'][$block['bid']]["data"] = $return['data'];
            //注释  by songsp 2011-3-21 16:20:13
			//$_G['block'][$block['bid']]['itemlist'] = block_updateitem($bid, $return['data']);
		}
	} else {
		DB::update('common_block', array('dateline'=>TIMESTAMP+999999, 'cachetime'=>0), array('bid'=>$bid));
	}
	discuz_process::unlock('block_update_cache');

	/*去掉优化
	//xiugai by songsp 2011-3-24 9:58:37   性能优化
	if(!$_G['myself']['block_update_cache']['cache_flush']){ //未更新过
		discuz_process::unlock('block_update_cache');
		$_G['myself']['block_update_cache']['cache_flush'] = 1;
	}
	*/

}

function block_template($bid){
    global $_G;

	$block = empty($_G['block'][$bid])?array():$_G['block'][$bid];
	$theclass = block_getclass($block['blockclass']);// for shor
	$thestyle = $theclass["script"][$block["script"]]["style"][$block['styleid']];// for short
	if(empty($block) || empty($theclass) || empty($thestyle)) {
		return ;
	}

    require_once libfile("function/diytemplate");

    $root = DISCUZ_ROOT."/source/plugin/commontemplate";
    $file = $thestyle["key"];

    if(!file_exists($root."/".$file.".htm")){
        $root = DISCUZ_ROOT."/source/plugin/".$theclass["id"]."/block/template";
    }

    $compfile = $theclass["id"]."_".$block['blockclass']."_".$thestyle["key"];
    $html = diy_template_render($root, $file, $compfile, $_G['block'][$block['bid']]["data"]);
    return $html;
}

function block_template_old($bid) {
	global $_G;

	$block = empty($_G['block'][$bid])?array():$_G['block'][$bid];
	$theclass = block_getclass($block['blockclass']);// for short
	$thestyle = $theclass["script"][$block["script"]]["style"][$block['styleid']];// for short
	if(empty($block) || empty($theclass) || empty($thestyle)) {
		return ;
	}
	$template = '';
	if($block['itemlist']) {
		$template = $thestyle['template']['header'];
		$order = 0;
		foreach($block['itemlist'] as $blockitem) {
			$order++;

			if(is_array($thestyle['template']['order']) && ($thestyle['template']['order'][$order])) {
				$theteamplte = $thestyle['template']['order'][$order];
			} else {
				$theteamplte = $thestyle['template']['loop'];
			}

			$blockitem['showstyle'] = !empty($blockitem['showstyle']) ? unserialize($blockitem['showstyle']) : array();
			$blockitem['fields'] = !empty($blockitem['fields']) ? $blockitem['fields'] : array();
			$blockitem['fields'] = is_array($blockitem['fields']) ? $blockitem['fields'] : unserialize($blockitem['fields']);
			$blockitem['picwidth'] = !empty($block['picwidth']) ? intval($block['picwidth']) : 'auto';
			$blockitem['picheight'] = !empty($block['picheight']) ? intval($block['picheight']) : 'auto';
			$blockitem['target'] = !empty($block['target']) ? ' target="_'.$block['target'].'"' : '';

			$fields = array('picwidth'=>array(), 'picheight'=>array(), 'target'=>array());
			$fields = array_merge($fields, $theclass['fields']);
			$searcharr = $replacearr = array();
			foreach($fields as $key=>$field) {
				$replacevalue = isset($blockitem[$key]) ? $blockitem[$key] : (isset($blockitem['fields'][$key]) ? $blockitem['fields'][$key] : '');
				$field['datatype'] = !empty($field['datatype']) ? $field['datatype'] : '';
				if($field['datatype'] == 'int') {// int
					$replacevalue = intval($replacevalue);
				} elseif($field['datatype'] == 'string') {
					$replacevalue = dhtmlspecialchars($replacevalue);
				} elseif($field['datatype'] == 'date') {
					$replacevalue = dgmdate($replacevalue, 'u', '9999', 'n-j');
				} elseif($field['datatype'] == 'title') {//title
					$replacevalue = stripslashes($replacevalue);
					$style = block_showstyle($blockitem['showstyle'], 'title');
					if($style) {
						$theteamplte = preg_replace('/title=[\'"]{title}[\'"]/', 'title="{title-title}"', $theteamplte);
						$theteamplte = preg_replace('/alt=[\'"]{title}[\'"]/', 'alt="{alt-title}"', $theteamplte);
						$searcharr[] = '{title-title}';
						$replacearr[] = $replacevalue;
						$searcharr[] = '{alt-title}';
						$replacearr[] = $replacevalue;
						$replacevalue = '<font style="'.$style.'">'.$replacevalue.'</font>';
					}
				} elseif($field['datatype'] == 'summary') {//summary
					$replacevalue = stripslashes($replacevalue);
					$style = block_showstyle($blockitem['showstyle'], 'summary');
					if($style) {
						$replacevalue = htmlspecialchars($replacevalue);
						$replacevalue = '<font style="'.$style.'">'.$replacevalue.'</font>';
					}
				} elseif($field['datatype'] == 'pic') {
					if($blockitem['picflag'] == '1') {
						$replacevalue = $_G['setting']['attachurl'].$replacevalue;
					} elseif ($blockitem['picflag'] == '2') {
						$replacevalue = $_G['setting']['ftp']['attachurl'].$replacevalue;
					}
					if($block['picwidth'] && $block['picheight'] && $block['picwidth'] != 'auto' && $block['picheight'] != 'auto') {
						if($blockitem['makethumb'] == 1) {
							$replacevalue = $_G['setting']['attachurl'].block_thumbpath($block, $blockitem);
						} elseif(!$_G['block_makethumb'] && !$blockitem['makethumb']) {
							DB::update('common_block_item', array('makethumb'=>2), array('itemid'=>$blockitem['itemid']));
							require_once libfile('class/image');
							$image = new image();
							$thumbpath = block_thumbpath($block, $blockitem);
							$return = $image->Thumb($replacevalue, $thumbpath, $block['picwidth'], $block['picheight'], 2);
							if($return) {
								DB::update('common_block_item', array('makethumb'=>1), array('itemid'=>$blockitem['itemid']));
								$replacevalue = $_G['setting']['attachurl'].$thumbpath;
							}
							$_G['block_makethumb'] = true;
						}
					}
				}
				$searcharr[] = '{'.$key.'}';
				$replacearr[] = $replacevalue;
			}

			$template .= str_replace($searcharr, $replacearr, $theteamplte);
		}
		$template .= $thestyle['template']['footer'];
	}
	return $template;
}

function block_showstyle($showstyle, $key) {
	$style = '';
	if(!empty($showstyle["{$key}_b"])) {
		$style .= 'font-weight: 900;';
	}
	if(!empty($showstyle["{$key}_i"])) {
		$style .= 'font-style: italic;';
	}
	if(!empty($showstyle["{$key}_u"])) {
		$style .= 'text-decoration: underline;';
	}
	if(!empty($showstyle["{$key}_c"])) {
		$style .= 'color: '.$showstyle["{$key}_c"].';';
	}
	return $style;
}

function block_setting($script, $values = array()) {
	global $_G;

	$return = array();
	$obj = block_script($script);
	if(!is_object($obj)) return $return;

	$_G['tmp_block_param_value']= $values; //放置选择的一些参数值，为解决模块选择的指定数据无法回显
	return block_makeform($obj->getsetting(), $values);
}

function block_style_setting($script, $style, $values){
    global $_G;

	$return = array();
	$obj = block_script($script);
	if(!is_object($obj)) return $return;
	return block_makeform($obj->getstylesetting($style), $values);
}

function block_makeform($blocksetting, $values){
	$return = array();
	foreach($blocksetting as $settingvar => $setting) {
		$varname = in_array($setting['type'], array('mradio', 'mcheckbox', 'select', 'mselect')) ?
			($setting['type'] == 'mselect' ? array('parameter['.$settingvar.'][]', $setting['value']) : array('parameter['.$settingvar.']', $setting['value']))
			: 'parameter['.$settingvar.']';
		$value = $values[$settingvar] != '' ? dstripslashes($values[$settingvar]) : $setting['default'];
		$setname = $setting['title'];
		$type = $setting['type'];
		$s = '';
		$langscript = substr($setname, 0, strpos($setname, '_'));
		$check = array();
        $value = empty($value)?$setting[value]:$value;

		if($type == 'radio') {
			$value ? $check['true'] = "checked" : $check['false'] = "checked";
			$value ? $check['false'] = '' : $check['true'] = '';
			$s .= '<ul><li'.($check['true'] ? ' class="checked"' : '').'><input type="radio" class="pr" name="'.$varname.'" value="1" '.$check['true'].'>&nbsp;'.lang('core', 'yes').'</li>'.
				'<li'.($check['false'] ? ' class="checked"' : '').'><input type="radio" class="pr" name="'.$varname.'" value="0" '.$check['false'].'>&nbsp;'.lang('core', 'no').'</li></ul>';
		} elseif($type == 'text' || $type == 'password' || $type == 'number') {
			$s .= '<input name="'.$varname.'" value="'.dhtmlspecialchars($value).'" type="'.$type.'" class="px" />';
		} elseif($type == 'textarea') {
			$s .= '<textarea rows="4" name="'.$varname.'" cols="50" class="pt">'.dhtmlspecialchars($value).'</textarea>';
		} elseif($type == 'select') {
			$s .= '<select name="'.$varname[0].'" class="ps">';
			foreach($varname[1] as $option) {
				$selected = $option[0] == $value ? ' selected="selected"' : '';
				$s .= '<option value="'.$option[0].'"'.$selected.'>'.lang('block/'.$langscript, $option[1]).'</option>';
			}
			$s .= '</select>';
		} elseif($type == 'mradio') {
			if(is_array($varname)) {
				$radiocheck = array($value => ' checked');
				$s .= '<ul'.(empty($varname[2]) ?  ' class="pr"' : '').'>';
				foreach($varname[1] as $varary) {
					if(is_array($varary) && !empty($varary)) {
						$s .= '<li'.($radiocheck[$varary[0]] ? ' class="checked"' : '').'><input class="pr" type="radio" name="'.$varname[0].'" value="'.$varary[0].'"'.$radiocheck[$varary[0]].'>&nbsp;'.lang('block/'.$langscript, $varary[1]).'</li>';
					}
				}
				$s .= '</ul>';
			}
		} elseif($type == 'mcheckbox') {
			$s .= '<ul class="nofloat">';
			foreach($varname[1] as $varary) {
				if(is_array($varary) && !empty($varary)) {
					$checked = is_array($value) && in_array($varary[0], $value) ? ' checked' : '';
					$s .= '<li'.($checked ? ' class="checked"' : '').'><input class="pc" type="checkbox" name="'.$varname[0].'[]" value="'.$varary[0].'"'.$checked.'>&nbsp;'.lang('block/'.$langscript, $varary[1]).'</li>';
				}
			}
			$s .= '</ul>';
		} elseif($type == 'mselect') {
			$s .= '<select name="'.$varname[0].'" multiple="multiple" size="10" class="ps">';
			foreach($varname[1] as $option) {
				$selected = is_array($value) && in_array($option[0], $value) ? ' selected="selected"' : '';
				$s .= '<option value="'.$option[0].'"'.$selected.'>'.lang('block/'.$langscript, $option[1]).'</option>';
			}
			$s .= '</select>';
		} elseif($type=="html" || $setting["html"]){
			$s .= $setting["html"];
		} else {
			$s .= $type;
		}
		$return[] = array('title' => lang('block/'.$langscript, $setname), 'html' => $s);
	}
	return $return;
}

function block_updateitem($bid, $items=array()) {
	global $_G;
	$block = $_G['block'][$bid];
	if(!$block) {
		$block = DB::fetch_first('SELECT * FROM '.DB::table('common_block')." WHERE bid='$bid'");
		$_G['block'][$bid] = $block;
	}
	if(!$block) {
		return false;
	}
	$block['shownum'] = max($block['shownum'], 1);
	$showlist = array_fill(1, $block['shownum'], array());
	$archivelist = array();
	$prelist = array();
	$manualkeys = array();
	$autokeys = array();
	$modlist = array();
	$query = DB::query('SELECT * FROM '.DB::table('common_block_item')." WHERE bid='$bid'");
	while($value=DB::fetch($query)) {
		$key = $value['idtype'].'_'.$value['id'];
		if($value['itemtype'] == '1') {
			if($value['startdate'] > TIMESTAMP) {
				$prelist[] = $value;
			} elseif((!$value['startdate'] || $value['startdate'] <= TIMESTAMP) &&
				(!$value['enddate'] || $value['enddate'] > TIMESTAMP)) {
				$showlist[$value['displayorder']] = $value;
				$key = $value['idtype'].'_'.$value['id'];
				$manualkeys[$key] = true;
			} else {
				$archivelist[$value['itemid']] = $value;
			}
		} elseif($value['itemtype'] == '2') {
			$modlist[$key] = $value;
			$archivelist[$value['itemid']] = $value;
		} else {
			$archivelist[$value['itemid']] = $value;
			$autokeys[$key] = $value['itemid'];
		}
	}
	$itemindex = 0;
	for($i=1; $i<=$block['shownum']; $i++) {
		if($showlist[$i]) {
			if($block['picwidth'] && $block['picheight']) {
				if(file_exists($_G['setting']['attachdir'].block_thumbpath($block, $showlist[$i]))) {
					$showlist[$i]['makethumb'] = 1;
				} else {
					$showlist[$i]['makethumb'] = 0;
				}
			}
		} else {
			$key = $items[$itemindex]['idtype'].'_'.$items[$itemindex]['id'];
			while(!empty($manualkeys[$key])) {
				$itemindex++;
				$key = $items[$itemindex]['idtype'].'_'.$items[$itemindex]['id'];
			}
			if(!isset($items[$itemindex])) {
				break;
			}
			if(isset($modlist[$key])) {
				$modlist[$key]['displayorder'] = $i;
				$showlist[$i] = $modlist[$key];
			} else {
				$items[$itemindex]['displayorder'] = $i;
				if($block['picwidth'] && $block['picheight']) {
					if(file_exists($_G['setting']['attachdir'].block_thumbpath($block, $items[$itemindex]))) {
						$items[$itemindex]['makethumb'] = 1;
					} else {
						$items[$itemindex]['makethumb'] = 0;
					}
				}
				$items[$itemindex]['fields'] = serialize($items[$itemindex]['fields']);

				$key = $items[$itemindex]['idtype'].'_'.$items[$itemindex]['id'];
				if($autokeys[$key]) {
					$items[$itemindex]['itemid'] = $autokeys[$key];
					unset($archivelist[$autokeys[$key]]);
				}
				$showlist[$i] = $items[$itemindex];
			}
			$itemindex++;
		}
	}
	if($archivelist) {
		$delids = array_keys($archivelist);
		DB::query('DELETE FROM '.DB::table('common_block_item')." WHERE bid='$bid' AND itemid IN (".dimplode($delids).")");
		$inserts = array();
		foreach($archivelist as $value) {
			$value = daddslashes($value);
			$inserts[] = "('$value[bid]', '$value[id]', '$value[idtype]', '$value[title]',
				 '$value[url]', '$value[pic]', '$value[summary]', '$value[showstyle]', '$value[related]',
				 '$value[fields]', '$value[displayorder]', '$value[startdate]', '$value[enddate]', '$value[picflag]', '$value[makethumb]')";
		}
		DB::query('REPLACE INTO '.DB::table('common_block_item_archive')."(bid, id, idtype, title, url, pic, summary, showstyle, related, `fields`, displayorder, startdate, enddate, picflag, makethumb) VALUES ".implode(',', $inserts));
	}
	$inserts = $itemlist = array();
	$itemlist = array_merge($showlist, $prelist);
	foreach($itemlist as $value) {
		if($value) {
			$value = daddslashes($value);
			$inserts[] = "('$value[itemid]', '$bid', '$value[itemtype]', '$value[id]', '$value[idtype]', '$value[title]',
				 '$value[url]', '$value[pic]', '$value[summary]', '$value[showstyle]', '$value[related]',
				 '$value[fields]', '$value[displayorder]', '$value[startdate]', '$value[enddate]', '$value[picflag]', '$value[makethumb]')";
		}
	}
	if($inserts) {
		DB::query('REPLACE INTO '.DB::table('common_block_item')."(itemid, bid, itemtype, id, idtype, title, url, pic, summary, showstyle, related, `fields`, displayorder, startdate, enddate, picflag, makethumb) VALUES ".implode(',', $inserts));
	}

	$showlist = array_filter($showlist);
	return $showlist;
}

function block_thumbpath($block, $item) {
	global $_G;
	$hash = md5($item['pic'].'-'.$item['picflag'].':'.$block['picwidth'].'|'.$block['picheight']);
	return 'block/'.substr($hash, 0, 2).'/'.$hash.'.jpg';
}

function block_getclass($classname) {
	global $_G;
    $blockclass = block_load_plugin($_G["fid"]);
	/*if(!isset($_G['cache']['blockclass'])) {
		loadcache('blockclass');
	}*/
	list($c1, $c2) = explode('_', $classname);
    foreach($blockclass as $key=>$value){
        if(isset($blockclass[$key]['subs'][$classname])){
            $blockclass[$key]['subs'][$classname]["id"] = $c1;
            return $blockclass[$key]['subs'][$classname];
        }
    }
    return array();
}

function block_getdiyurl($tplname, $diymod=false) {
	$mod = $id = $script = $url = '';
	$flag = 0;
	if (empty ($tplname)) {
		$flag = 2;
	} else {
		list($script,$tpl) = explode('/',$tplname);
		if (!empty($tpl)) {
			$arr = array();
			preg_match_all('/(.*)\_(\d{1,9})/', $tpl,$arr);
			$mod = empty($arr[1][0]) ? $tpl : $arr[1][0];
			$id = max(intval($arr[2][0]),0);
			switch ($mod) {
				case 'index' :
				case 'discuz' :
					$mod = 'index';
					break;
				case 'space_home' :
					$mod = 'space';
					break;
				case 'forumdisplay' :
					$flag = $id ? 0 : 1;
					$mod .= '&fid='.$id;
					break;
				case 'list' :
					$flag = $id ? 0 : 1;
					$mod .= '&catid='.$id;
					break;
				case 'portal_topic_content' :
					$mod = 'topic';
					$flag = $id ? 0 : 1;
					$mod .= '&topicid='.$id;
					break;
				case 'view' :
					$flag = $id ? 0 : 1;
					$mod .= '&aid='.$id;
					break;
				default :
					break;
			}
		}
		$url = empty($mod) ? '' : $script.'.php?mod='.$mod.($diymod?'&diy=yes':'');
	}
	return array('url'=>$url,'flag'=>$flag);
}

function block_clear() {
	$blocks = array();
	$query = DB::query("SELECT bid, blocktype FROM ".DB::table('common_block'));
	while($value = DB::fetch($query)) {
		if($value['blocktype'] == '0') {
			$blocks[$value['bid']] = 1;
		}
	}
	$query = DB::query("SELECT bid FROM ".DB::table('common_template_block'));
	while($value = DB::fetch($query)) {
		unset($blocks[$value['bid']]);
	}
	$delids = array_keys($blocks);
	if (!empty($delids)) {
		$delids = dimplode($delids);
		DB::query("DELETE FROM ".DB::table('common_block')." WHERE bid IN ($delids)");
		DB::query("DELETE FROM ".DB::table('common_block_item')." WHERE bid IN ($delids)");
		DB::query("OPTIMIZE TABLE ".DB::table('common_block'));
		DB::query("OPTIMIZE TABLE ".DB::table('common_block_item'));
	}
}

function block_load_plugin($fid){
    require_once './source/function/function_group.php';
    group_load_plugin($fid);
    include libfile('portal/blockclass', 'include');
    $data = $blockclass;/*
    while($value = DB::fetch($query)) {
        list($c1, $c2) = explode('_', $value['blockclass']);
        $blockclass = $c1.'_'.$c2;
        if(isset($data[$c1]['subs'][$blockclass])) {
            $value['template'] = unserialize($value['template']);
            $data[$c1]['subs'][$blockclass]['style'][$value['styleid']] = $value;
        }
    }*/
    return $data;
}

//add by songsp  处理特殊专区diy模块
function parse_special_block($blockclass,$cachedate){

	global $_G;
	//对专区组件菜单，专区描述，专区信息  特殊处理
    $special_plugins = array('pluginmenu','foruminfo','forumremark','grouptop');

	if(!in_array($blockclass,$special_plugins)){
		return $cachedate;
	}


	if('forumremark' == $blockclass){// 专区描述

		//专区描述参数使用[myself_value_forum_description] 占位，替换
		$cachedate = str_replace( '[myself_value_forum_description]', $_G['forum']['description'], $cachedate );

		return $cachedate;

	}
	else if('foruminfo' == $blockclass){//专区信息
		//$_G['forum']['type_icn_id']= 1;

		//$_G['forum']['displayorder'] = 1;

		//处理[myself_block_forum_type_icn_id]
		if(!$_G['forum']['type_icn_id']){
			//print_r("--");
			//$cachedate = preg_replace( '\[myself_block_forum_type_icn_id].*[/myself_block_forum_type_icn_id\]', '', $cachedate );
			$cachedate = preg_replace( '/\[myself_block_forum_type_icn_id\]([\s\S]*?)\[\/myself_block_forum_type_icn_id\]/i', '', $cachedate );
		}else{
			$cachedate = preg_replace( '/\[myself_block_forum_type_icn_id\]([\s\S]*?)\[\/myself_block_forum_type_icn_id\]/i', '\\1', $cachedate );
		}

		//替换[myself_value_forum_type_icn_id]
		$cachedate = preg_replace( '/(\[myself_value_forum_type_icn_id\])/i', $_G['forum']['type_icn_id'], $cachedate );



		//替换$_G['forum']['icon'] 图标[myself_block_forum_icon]  add by songsp 2011-4-22 16:37:05
		$cachedate = preg_replace( '/(\[myself_block_forum_icon\])/i', $_G['forum']['icon'], $cachedate );



		//[myself_value_forum_name]
		$cachedate = preg_replace( '/(\[myself_value_forum_name\])/i', $_G['forum']['name'], $cachedate );

		//[myself_block_forum_displayorder]
		if(!$_G['forum']['displayorder']){

			$cachedate = preg_replace( '/\[myself_block_forum_displayorder\]([\s\S]*?)\[\/myself_block_forum_displayorder\]/i', '', $cachedate );
		}else{
			$cachedate = preg_replace( '/\[myself_block_forum_displayorder\]([\s\S]*?)\[\/myself_block_forum_displayorder\]/i', '\\1', $cachedate );
		}

		//[myself_value_forum_membernum]
		$cachedate = preg_replace( '/(\[myself_value_forum_membernum\])/i', $_G['forum']['membernum'], $cachedate );

		//[myself_value_forum_viewnum]
		$cachedate = preg_replace( '/(\[myself_value_forum_viewnum\])/i', $_G['forum']['viewnum'], $cachedate );

		//myself_value_forum_my_empirical
		$cachedate = preg_replace( '/(\[myself_value_forum_my_empirical\])/i', $_G['forum']['my_empirical']?$_G['forum']['my_empirical']:0, $cachedate );

		//群管理员[myself_value_forum_groupmanagers]
		$groupmanagers = $_G['forum']['moderators'];

		foreach ($groupmanagers as $manage){
			$groupmanagers_html.='<a href="home.php?mod=space&amp;uid='.$manage[uid].'" target="_blank">';
			$groupmanagers_html.=user_get_user_name($manage[uid]);
			$groupmanagers_html.='</a> &nbsp;';
		}
		$cachedate = preg_replace( '/(\[myself_value_forum_groupmanagers\])/i', $groupmanagers_html, $cachedate );



		return $cachedate;

	}else if('pluginmenu' == $blockclass){//专区组件菜单

		//菜单 [myself_value_groupmenu]
		foreach ($_G["group_plugins"]["group_available"]["groupmenu"] as $id =>$item){
			if($id!='groupad'&& $id!='repeats'){
				$groupmenu_html.='<li onmouseout="this.className=\'\'" onmouseover="this.className=\'hvr\'">';
				$groupmenu_html.='<a href="forum.php?mod=group&action=plugin&fid='.$_G[fid].'&plugin_name='.$id.'&plugin_op=groupmenu">';
				$groupmenu_html.='<img alt="" src="static/image/plugins/'.$id.'.gif" width="16" height="16">'.$item["menu"].'</a>';
				$groupmenu_html.='</li>';
			}
		}
		$cachedate = preg_replace( '/(\[myself_value_groupmenu\])/i', $groupmenu_html, $cachedate );

		//加入专区按钮	[myself_value_join_button]	 [myself_value_join_button_style2]  两个样式
		$status = $_G['myself']['group']['status'] ;//从G中获取
		$join_button_info_1 = '';
		$join_button_info_2 = '';
		$join_button_info_3 = '';
		if($status != 2 && $status != 3){
			if($status != 'isgroupuser'){

				if($_G['uid'] && ($_G['forum']['jointype']==0 || $_G['forum']['jointype']==2) && $_G["forum"]["group_type"]!=1){
					$join_button_info_1 = '<div class="hm"><button type="button" class="pn" onclick="location.href=\'forum.php?mod=group&amp;action=join&amp;fid='.$_G[fid].'\'"><em>加入专区</em></button></div>';
					$join_button_info_2 = '<li><a href="forum.php?mod=group&amp;action=join&amp;fid='.$_G[fid].'">加入专区</a></li>';
					$join_button_info_3 = '<a href="forum.php?mod=group&amp;action=join&amp;fid='.$_G[fid].'">加入专区</a>';
				}

			}
		}
		$cachedate = preg_replace( '/(\[myself_value_join_button\])/i', $join_button_info_1, $cachedate );
		$cachedate = preg_replace( '/(\[myself_value_join_button_style2\])/i', $join_button_info_2, $cachedate );
		$cachedate = preg_replace( '/(\[join_button\])/i', $join_button_info_3, $cachedate );//新增退出caimm



		//管理专区按钮[myself_value_ismoderator_button]
		$action = $_G['myself']['group']['action'] ;//从G中获取
		$ismoderator_button_info = '';
		if( $_G['forum']['ismoderator']){

			$ismoderator_button_info.='<li ';

			if($action == 'manage'){
				$ismoderator_button_info.= 'class="a" ';
			}
			$ismoderator_button_info.='><a href="forum.php?mod=group&amp;action=manage&amp;fid='.$_G[fid].'#groupnav">管理专区</a></li>';
			$ismoderator_button ='<a href="forum.php?mod=group&amp;action=manage&amp;fid='.$_G[fid].'#groupnav">管理专区</a>';

		}
		$cachedate = preg_replace( '/(\[myself_value_ismoderator_button\])/i', $ismoderator_button_info, $cachedate );
		$cachedate = preg_replace( '/(\[ismoderator_button\])/i', $ismoderator_button, $cachedate );//新增退出caimm


		//退出按钮 	[myself_value_exit_button]
		$exit_button_info = '';
		if($status != 2 && $status != 3){
			if ($status == 'isgroupuser'){
				if ( !group_is_group_founder($_G[fid], $_G[uid]) && $_G['forum']['group_type']['group_type']!=1) {
					$exit_button_info = '<li><a href="javascript:;" onclick="showDialog(\'退出专区您在此专区中的经验值将不被保留。确定要退出该专区吗？\', \'confirm\', \'\', function(){location.href=\'forum.php?mod=group&amp;action=out&amp;fid='.$_G[fid].'\'})">退出专区</a></li>';
					$exit_button = '<a href="javascript:;" onclick="showDialog(\'退出专区您在此专区中的经验值将不被保留。确定要退出该专区吗？\', \'confirm\', \'\', function(){location.href=\'forum.php?mod=group&amp;action=out&amp;fid='.$_G[fid].'\'})">退出专区</a>';
				}
			}
		}

		$cachedate = preg_replace( '/(\[myself_value_exit_button\])/i', $exit_button_info, $cachedate );
		$cachedate = preg_replace( '/(\[exit_button\])/i', $exit_button, $cachedate );//新增退出caimm


		return $cachedate;
	}





	return $cachedate;

}

?>