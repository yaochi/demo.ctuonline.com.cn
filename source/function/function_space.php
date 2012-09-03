<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_space.php 11181 2010-05-26 02:03:22Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function xml_to_array($xml)     //XML转换成数组                         
{                                                        
  $array = (array)(simplexml_load_string($xml));         
  foreach ($array as $key=>$item){                       
    $array[$key]  =  struct_to_array((array)$item);      
  }                                                      
  return $array;                                         
}                                                        
function struct_to_array($item) {   //XML转换成数组                     
  if(!is_string($item)) {                                
    $item = (array)$item;                                
    foreach ($item as $key=>$val){                       
      $item[$key]  =  struct_to_array($val);             
    }                                                    
  }                                                      
  return $item;                                          
}               

function parseXML($url) {//解析XML
	$s=join("",file($url));
	if(!$s) return;                    
	$arr = xml_to_array($s); 
	//print_r($arr);
	$result[groupid]=$arr[groupId][0];
	$result[groupname]=$arr[groupName][0];
	//print_r($result);
	return $result;
}

function getprogroup($regname,$inPost=true) {
	global $_G;
	$cache_key = "user_province_group_".$regname;//用户所在部门信息中的省信息
	$cache = memory("get", $cache_key);	 
	
	if(!empty($cache)){
		return unserialize($cache);
	}

	if(!$inPost) return;
	
	$FILE_SEARCH_PAGE = "http://".$_G[config][expert][activeurl]."/usermanage/getUserprovinceServlet.do?regName=".$regname;
	$str1 = parseXML($FILE_SEARCH_PAGE);
	if(!empty($str1)){
		memory("set", $cache_key, serialize($str1), 24*60*60);
	}
	return $str1;	
}


function getuserdefaultdiy() {
	$defaultdiy = array(
			'currentlayout' => '1:2:1',
			'block' => array(
					'frame`frame1' => array(
							'attr' => array('name'=>'frame1'),
							'column`frame1_left' => array(
									'block`profile' => array('attr' => array('name'=>'profile')),
									'block`album' => array('attr' => array('name'=>'album')),
									'block`doing' => array('attr' => array('name'=>'doing'))
							),
							'column`frame1_center' => array(
									'block`feed' => array('attr' => array('name'=>'feed')),
									'block`share' => array('attr' => array('name'=>'share')),
									'block`blog' => array('attr' => array('name'=>'blog')),
									'block`thread' => array('attr' => array('name'=>'thread')),
									'block`wall' => array('attr' => array('name'=>'wall'))
							),
							'column`frame1_right' => array(
									'block`friend' => array('attr' => array('name'=>'fans')),
									'block`visitor' => array('attr' => array('name'=>'visitor')),
									'block`group' => array('attr' => array('name'=>'group'))
							)
					)
			),
			'parameters' => array(
					'blog' => array('showmessage' => true, 'shownum' => 6),
					'doing' => array('shownum' => 15),
					'album' => array('shownum' => 8),
					'thread' => array('shownum' => 10),
					'share' => array('shownum' => 10),
					'fans' => array('shownum' => 18),
					'group' => array('shownum' => 12),
					'visitor' => array('shownum' => 18),
					'wall' => array('shownum' => 16),
					'feed' => array('shownum' => 16)
			)
	);
	return $defaultdiy;
}

function getuserdiydata($space) {
	$userdiy = getuserdefaultdiy();
	if (!empty($space['blockposition'])) {
		$blockdata = unserialize($space['blockposition']);
		foreach ((array)$blockdata as $key => $value) {
			if ($key == 'parameters') {
				foreach ((array)$value as $k=>$v) {
					if (!empty($v)) $userdiy[$key][$k] = $v;
				}
			} else {
				if (!empty($value)) $userdiy[$key] = $value;
			}
		}
	}
	return $userdiy;
}

function getblockhtml($blockname,$parameters = array()) {
	global $_G, $space;

	$parameters = empty($parameters) ? array() : $parameters;
	$list = array();
	$sql = $title = $html = $wheresql = $ordersql = '';
	$shownum = 6;

	$uid = intval($space['uid']);

	$shownum = empty($parameters['shownum']) ? $shownum : intval($parameters['shownum']);
	switch ($blockname) {
		case 'profile':
			
			//$FILE_SEARCH_PAGE = "http://".$_G[config][expert][activeurl]."/usermanage/getUserprovinceServlet.do?regName=".$regname;
			$regname=$space['regname'];
            $html .= '<input type="hidden" id="regname" name="regname" value="'.$regname.'"/>';
			$html .= '<div class="avt avtm">'.avatar($space['uid'],'middle');
			//显示所在的省公司
			require_once "function_org.php";
			//add by qiaoyz,2011-3-10,EKSN 197 在个人空间左上角用户头像下显示一对中括号（括号中的所在公司未显示出来）
			$html .= '<h2>'.user_get_user_name($space['uid']).'<span id="proauth"></span><span id="progroup">';
			/*if($usergroup){
			$html .= '['.$usergroup.']';
			}*/
			$progroup=getprogroup($space['regname'],false);
			if($progroup[groupname]){
				$html .= '['.$progroup[groupname].']';
			} else {
				$_G['myspace']['profile'] = 1;
			}
			
			
			$html .= '</span></h2>';
			
			
			$html .= '</div><p class="hm">';

			if ($space['self']) {
				$html .= '<ul class="xl xl2 cl">';
				$html .= '<li><a href="home.php?mod=space&diy=yes">'.lang('space', 'block_profile_diy').'</a></li>';
				$html .= '<li><a href="home.php?mod=space&do=wall">'.lang('space', 'block_profile_wall').'</a></li>';
				$html .= '<li><a href="home.php?mod=spacecp&ac=avatar">'.lang('space', 'block_profile_avatar').'</a></li>';
				$html .= '<li><a href="home.php?mod=spacecp&ac=profile">'.lang('space', 'block_profile_update').'</a></li>';
			} else {				
				$html .= "<a href=\"home.php?mod=space&uid=$space[uid]&do=wall\">".lang('space', 'block_profile_wall_to_me')."</a><span class=\"pipe\">|</span>";
				$html .= "<a href=\"home.php?mod=spacecp&ac=poke&op=send&uid=$space[uid]&handlekey=propokehk_{$space[uid]}\" id=\"a_poke_{$space[uid]}\" onclick=\"showWindow(this.id, this.href, 'get', 0);\">".lang('space', 'block_profile_poke')."</a><span class=\"pipe\">|</span>";
				$html .= "<a href=\"home.php?mod=spacecp&ac=pm&op=showmsg&handlekey=showmsg_$space[uid]&touid=$space[uid]&pmid=0&daterange=2\" id=\"a_sendpm_$space[uid]\" onclick=\"showWindow('showMsgBox', this.href, 'get', 0)\">".lang('space', 'block_profile_sendmessage')."</a>";
			}


			$html .= '</p>';
			
			if (!$space['self']) {
				require_once libfile('function/friend');
				$isfriend = friend_check($space['uid']);
				require_once libfile('function/follow');
				$isfollow = follow_check($space['uid']);
				
				if ($isfriend) {
					$html .= "<script>~function(){ document.write(getFollowBtn('friend', $_G[uid], $space[uid]))}();</script>";
				} else {
					if (!$isfollow) {
						$html .= "<script>~function(){ document.write(getFollowBtn('follow', $_G[uid], $space[uid]))}();</script>";				
					} else {
						$html .= "<script>~function(){ document.write(getFollowBtn('unfollow', $_G[uid], $space[uid]))}();</script>";
					}
				}
			}
			
			$html = '<div class="content"><div id="pcd">'.$html.'</div></div>';
			break;

		case 'doing':

			/*$dolist = array();
			$sql = "SELECT * FROM ".DB::table('home_doing')." WHERE uid='$uid' ORDER BY dateline DESC LIMIT 0,$shownum";
			$query = DB::query($sql);
			while ($value = DB::fetch($query)) {
				$dolist[] = $value;
			}

			if ($dolist) {
				foreach($dolist as $dv) {
					$doid = $dv['doid'];
					$_G[gp_key] = $key = random(8);
					$html .= "<li class=\"pbn bbda\">";
					$html .= $dv['message'];
					$html .= "&nbsp;<a href=\"home.php?mod=space&uid=$dv[uid]&do=doing&view=me&from=space&doid=$dv[doid]\" target=\"_blank\" class=\"xg1\">".lang('space', 'block_doing_reply')."</a>";
					$html .= "</li>";
				}
			} else {
				$html .= "<p class=\"emp\">".lang('space', 'block_doing_no_content')."</p>";
			}
			$html = '<div class="content"><ul class="xl">'.$html.'</ul></div>';*/
			break;

		case 'blog':
			$query = DB::query("SELECT bf.*, b.* FROM ".DB::table('home_blog')." b
				LEFT JOIN ".DB::table('home_blogfield')." bf ON bf.blogid=b.blogid
				WHERE b.uid='$uid'
				ORDER BY b.dateline DESC LIMIT 0,$shownum");
			while ($value = DB::fetch($query)) {
				if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
					if($value['pic']) $value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
					$value['message'] = $value['friend']==4?'':getstr($value['message'], 150, 0, 0, 0, 0, -1);
					$html .= lang('space', 'blog_li', array(
							'uid' => $value['uid'],
							'blogid' => $value['blogid'],
							'subject' => $value['subject'],
							'date' => dgmdate($value['dateline'],'Y-m-d')));
					if($parameters['showmessage']) {
						if ($value['pic']) {
							$html .= lang('space', 'blog_li_img', array(
									'uid' => $value['uid'],
									'blogid' => $value['blogid'],
									'src' => $value['pic']));
						}
						$html .= "<dd>$value[message]</dd>";
					}
					$html .= lang('space', 'blog_li_ext', array('uid'=>$value['uid'],'blogid'=>$value['blogid'],'viewnum'=>$value['viewnum'],'replynum'=>$value['replynum']));
					$html .= "</dl>";
				} else {
					$html .= '<p>'.lang('space','block_view_noperm').'</p>';
				}
			}
			$more = $html ? '<p class="ptm" style="text-align: right;"><a href="home.php?mod=space&uid='.$space['uid'].'&do=blog&view=me&from=space">'.lang('space', 'viewmore').'</a></p>' : '';
			$html = '<div class="content xld">'.$html.$more.'</div>';
			break;
		case 'album':


			$query = DB::query("SELECT * FROM ".DB::table('home_pic')." WHERE uid='$uid' ORDER BY dateline DESC LIMIT 0,$shownum");
			
			while ($value = DB::fetch($query)) {
				if(strpos($value['filepath'],'attachment/album')){
					$filepath=explode('.',$value['filepath']);
					$value['filepath']=$filepath[0].'.thumb.'.$value[type];
				}else{
					$value['filepath'] = pic_get($value['filepath'], 'album', $value['thumb'], $value['remote']);
				}
				$html .='<li style="width:94px;height:94px;"><div class="d"><a target="_blank" href="home.php?mod=space&uid='.$uid.'&do=album&picid='.$value[picid].'"><img alt="" src="'.$value[filepath].'"></a></div></li>';
			}
			$html = '<div class="content"><ul class="ml mla cl">'.$html.'</ul></div>';
			break;

		case 'feed':

			if(ckprivacy('feed', 'view')) {
				require_once libfile('function/feed');
				$query = DB::query("SELECT * FROM ".DB::table('home_feed')." WHERE uid='$uid' and anonymity='0' and idtype!='feed' ORDER BY dateline DESC LIMIT 0,$shownum");
				while ($value = DB::fetch($query)) {
					if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
						$html .=  mkfeedhtml(mkfeed($value));
					}
				}
			}
			$html = empty($html) ?  '' : '<ul class="content el">'.$html.'</ul>';
			break;
		case 'thread':

			if($_G['setting']['allowviewuserthread']) {
				$fidsql = " AND fid IN({$_G[setting][allowviewuserthread]}) ";
			} else {
				$fidsql = '';
			}
			$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE authorid='$uid' $fidsql ORDER BY tid DESC LIMIT 0,$shownum");
			while ($thread = DB::fetch($query)) {
				if($thread['author']) {
					$html .= "<li><a href=\"forum.php?mod=viewthread&tid={$thread['tid']}\" target=\"_blank\">{$thread['subject']}</a></li>";
				}
			}
			$html = empty($html) ?  '' : '<div class="content cl"><ul class="xl">'.$html.'</ul></div>';
			break;
		case 'fans':

			require_once libfile('function/follow');

			$friendlist = array();
			$friendlist = fans_list($uid,'', $shownum);

			$fuids = array_keys($friendlist);
			getonlinemember($fuids);

			foreach ($friendlist as $key => $value) {
				$classname = $_G['ols'][$value['fuid']]?'gol':'';
				$html .= '<li><a class="perPanel" href="home.php?mod=space&uid='.$value['fuid'].'" target="_blank"><em class="'.$classname.'"></em>'.avatar($value['fuid'],'small').'</a><p><a class="perPanel" href="home.php?mod=space&uid='.$value[fuid].'" target="_blank">'.user_get_user_name($value['fuid']).'</a></p></li>';
			}
			$html = '<div class="content"><ul class="ml mls cl">'.$html.'</ul></div>';
			break;
		case 'visitor':

			$query = DB::query("SELECT * FROM ".DB::table('home_visitor')." WHERE uid='$uid' ORDER BY dateline DESC LIMIT 0,$shownum");

			$list = $fuids = array();
			while ($value = DB::fetch($query)) {
				$list[] = $value;
				$fuids[] = $value['vuid'];
			}

			getonlinemember($fuids);

			foreach($list as $value) {
				$html .= "<li>";
				if ($value['vusername'] == '') {
					$html .= lang('space', 'visitor_anonymity');
				} else {
					$html .= lang('space', 'visitor_list', array(
							'uid' => $value['vuid'],
							'username' => user_get_user_name($value['vuid']),
							'class' => ($_G['ols'][$value['vuid']]?'gol':''),
							'avatar' => avatar($value['vuid'],'small')));
				}
				$html .= "<span class=\"xg2\">".dgmdate($value['dateline'],'u')."</span>";
				$html .= "</li>";
			}
			$html = '<div class="content"><ul class="ml mls cl">'.$html.'</ul></div>';
			break;
		case 'share':

			if(ckprivacy('share', 'view')) {
				require_once libfile('function/share');

				$query = DB::query("SELECT * FROM ".DB::table('home_share')." WHERE uid='$uid' ORDER BY dateline DESC LIMIT 0,$shownum");
				while ($value = DB::fetch($query)) {
					$value = mkshare($value);

					$html .= '<li><em><a href="home.php?mod=space&uid='.$value['uid'].'&do=share&id='.$value['sid'].'">'.$value['title_template'].'</a>('.dgmdate($value['dateline'], 'u').')</em><div class="ec cl">';
					if ($value['image']) {
						$html .= '<a href="'.$value['image_link'].'" target="_blank"><img src="'.$value['image'].'" class="tn" alt="" /></a>';
					}
					$html .= '<div class="d">'.$value['body_template'].'</div>';
					if ($value['type'] == 'video') {
						if(!empty($value['body_data']['imgurl'])) {
							$html .= '<table class="mtm" title="'.lang('space', 'click_play').'" onclick="javascript:showFlash(\''.$value['body_data']['host'].'\', \''.$value['body_data']['flashvar'].'\', this, \''.$value['sid'].'\');"><tr><td class="vdtn hm" style="background: url('.$value['body_data']['imgurl'].') no-repeat"><img src="'.IMGDIR.'/vds.png" alt="'.lang('space', 'click_play').'" /></td></tr></table>';
						} else {
							$html .= "<img src=\"".IMGDIR."/vd.gif\" alt=\"".lang('space', 'click_play')."\" onclick=\"javascript:showFlash('{$value['body_data']['host']}', '{$value['body_data']['flashvar']}', this, '{$value['sid']}');\" class=\"tn\" />";
						}
					}elseif ($value['type'] == 'music') {
						$html .= "<img src=\"".IMGDIR."/music.gif\" alt=\"".lang('space', 'click_play')."\" onclick=\"javascript:showFlash('music', '{$value['body_data']['musicvar']}', this, '{$value['sid']}');\" class=\"tn\" />";
					}elseif ($value['type'] == 'flash') {
						$html .= "<img src=\"".IMGDIR."/flash.gif\" alt=\"".lang('space', 'click_view')."\" onclick=\"javascript:showFlash('flash', '{$value['body_data']['flashaddr']}', this, '{$value['sid']}');\" class=\"tn\" />";
					}

					if ($value['body_general']) {
						$html .= '<div class="quote'.($value['image'] ? 'z' : '')."\"><blockquote>$value[body_general]</blockquote></div>";
					}
					$html .= '</div></li>';
				}
				$html = '<div class="content"><ul class="el">'.$html.'</ul></div>';
			}
			break;
		case 'wall':

			$walllist = array();
			if(ckprivacy('wall', 'view')) {
				$query = DB::query("SELECT * FROM ".DB::table('home_comment')." WHERE id='$uid' AND idtype='uid' ORDER BY dateline DESC LIMIT 0,$shownum");
				while ($value = DB::fetch($query)) {
					$value['message'] = strlen($value['message'])>500? getstr($value['message'], 500, 0, 0, 0, 0, -1).' ...':$value['message'];
					$walllist[] = $value;
				}
			}
			$html = '<div class="xld xlda" id="comment_ul">';
			foreach ($walllist as $key => $value) {
				$op = '';
				if ($value['author']) {
					$author_avatar = '<a href="home.php?mod=space&uid='.$value['authorid'].'" target="_blank">'.avatar($value['authorid'],'small').'</a>';
					$author = '<a href="home.php?mod=space&uid='.$value['authorid'].'" id="author_'.$value['cid'].'" target="_blank">'.user_get_user_name($value['authorid']).'</a>';
				}else {
					$author_avatar = '<img src="static/image/magic/hidden.gif" alt="hidden" />';
					$author = lang('space', 'hidden_username');
				}
				if ($value['authorid']==$_G['uid']) {
					$op .= lang('space', 'wall_edit', array('cid'=>$value['cid']));
				}
				if ($value['authorid']==$_G['uid'] || $space['self'] || checkperm('managecomment')){
					$op .= lang('space', 'wall_del', array('cid'=>$value['cid']));
				}
				if ($value['authorid']!=$_G['uid'] && ($value['idtype'] != 'uid' || $space['self'])) {
					$op .= lang('space', 'wall_reply', array('cid'=>$value['cid']));
				}
				$date = dgmdate($value['dateline'], 'u');
				$replacearr = array('author'=>$author, 'author_avatar' => $author_avatar, 'cid' => $value['cid'], 'message'=> $value['message'] , 'date' => $date, 'op'=> $op);
				$html .= lang('space', 'wall_li', $replacearr);
			}
			$html .= '</div>';
			$html = '<div class="content">'.lang('space','wall_form', array('uid' => $uid, 'FORMHASH'=>FORMHASH)).$html.'</div>';
			break;
		case 'group':
			require_once libfile('function/group');
			$grouplist = mygrouplist($uid, 'lastupdate', array('f.name', 'ff.icon'), $shownum);
			if(empty($grouplist)) $grouplist = array();
			foreach ($grouplist as $groupid => $group) {
				$group['groupid'] = $groupid;
				$html .= lang('space', 'group_li',$group);
			}
			$html = '<div class="content"><ul class="ml mls cl">'.$html.'</ul></div>';
			break;
		case 'music':
			if(!empty($parameters['mp3list'])) {
				$authcode = substr(md5($_G['authkey'].$uid), 6, 16);
				$querystring = urlencode("home.php?mod=space&do=index&op=getmusiclist&uid=$uid&hash=$authcode&t=".TIMESTAMP);
				$swfurl = STATICURL.'image/common/mp3player.swf?config='.$querystring;
				$html = "<script language=\"javascript\" type=\"text/javascript\">document.write(AC_FL_RunContent('id', 'mp3player', 'name', 'mp3player', 'devicefont', 'false', 'width', '100%', 'height', '220px', 'src', '$swfurl', 'menu', 'false',  'allowScriptAccess', 'sameDomain', 'swLiveConnect', 'true', 'wmode', 'transparent'));</script>";
			} else {
				$html = lang('space', 'music_no_content');
			}
			$html = '<div class="content"><div class="ml mls cl">'.$html.'</div></div>';
			break;
		default:

			if($space['self']) {
				$_G['space_group'] = $_G['group'];
			} elseif(empty($_G['space_group'])) {
				$_G['space_group'] = db::fetch_first("SELECT * FROM ".db::table('common_usergroup_field')." WHERE groupid='$space[groupid]'");
			}
			require_once libfile('function/discuzcode');
			if ($_G['space_group']['allowspacediyimgcode']) {
				if (empty($_G['cache']['smilies']['loaded'])) {
					loadcache(array('smilies', 'smileytypes'));
					foreach($_G['cache']['smilies']['replacearray'] AS $skey => $smiley) {
						$_G['cache']['smilies']['replacearray'][$skey] = '[img]'.$_G['siteurl'].'static/image/smiley/'.$_G['cache']['smileytypes'][$_G['cache']['smilies']['typearray'][$skey]]['directory'].'/'.$smiley.'[/img]';
					}
					$_G['cache']['smilies']['loaded'] = 1;
				}
				$parameters['content'] = preg_replace($_G['cache']['smilies']['searcharray'], $_G['cache']['smilies']['replacearray'], censor(trim($parameters['content'])));
			}
			if ($_G['space_group']['allowspacediybbcode'] || $_G['space_group']['allowspacediyimgcode'] || $_G['space_group']['allowspacediyhtml'] ){
				$parameters['content'] = discuzcode($parameters['content'], 1, 0, 1, 0, $_G['space_group']['allowspacediybbcode'], $_G['space_group']['allowspacediyimgcode'], $_G['space_group']['allowspacediyhtml'], 0, 1);
			} else {
				$parameters['content'] = dhtmlspecialchars($parameters['content']);
			}

			if (empty ($parameters['content'])) $parameters['content'] = lang('space',$blockname);
			$html .= '<div class="content">'.$parameters['content'].'</div>';
			break;
	}

	if (isset($parameters['title'])) {
		$title = empty($parameters['title']) ? '' : lang('space', 'block_title', array('bname' => stripslashes($parameters['title'])));
	} else {
		$title = lang('space', 'block_title', array('bname' => getblockdata($blockname)));
	}
	$html = $title.$html;
	return $html;
}

function getonlinemember($uids) {
	global $_G;
	if ($uids && is_array($uids) && empty($_G['ols'])) {
		$_G['ols'] = array();
		$query = DB::query("SELECT * FROM ".DB::table('common_session')." WHERE uid IN (".dimplode($uids).")");
		while ($value = DB::fetch($query)) {
			if(!$value['magichidden']) {
				$_G['ols'][$value['uid']] = $value['lastactivity'];
			}
		}
	}
}

function mkfeedhtml($value) {
	global $_G;

	$html = '';
	$html .= "<li class=\"cl $value[magic_class]\" id=\"feed_{$value[feedid]}_li\">";
	$html .= "<div class=\"cl\" {$value[style]}>";
	$html .= "<a class=\"t\" href=\"home.php?mod=space&uid=$_GET[uid]&do=home&view=$_GET[view]&appid=$value[appid]&icon=$value[icon]\" title=\"".lang('space', 'feed_view_only')."\"><img src=\"$value[icon_image]\" /></a>$value[title_template]";
	$html .= "<span class=\"xg1 mlmgroup\">".dgmdate($value[dateline], 'n-j H:i')."</span>";

	$html .= "<div class=\"ec\">";

	if ($value['image_1']) {
		$html .= "<a href=\"$value[image_1_link]\"{$value[target]}><img src=\"$value[image_1]\" alt=\"\" class=\"tn\" /></a>";
	}
	if ($value['image_2']) {
		$html .= "<a href=\"$value[image_2_link]\"{$value[target]}><img src=\"$value[image_2]\" alt=\"\" class=\"tn\" /></a>";
	}
	if ($value['image_3']) {
		$html .= "<a href=\"$value[image_3_link]\"{$value[target]}><img src=\"$value[image_3]\" alt=\"\" class=\"tn\" /></a>";
	}
	if ($value['image_4']) {
		$html .= "<a href=\"$value[image_4_link]\"{$value[target]}><img src=\"$value[image_4]\" alt=\"\" class=\"tn\" /></a>";
	}

	if ($value['body_template']) {
		$style = $value['image_3'] ? ' style="clear: both; zoom: 1;"' : '';
		$html .= "<div class=\"d\" $style>$value[body_template]</div>";
	}

	if (!empty($value['body_data']['flashvar'])) {
		if(!empty($value['body_data']['imgurl'])) {
			$html .= '<table class="mtm" title="'.lang('space', 'click_play').'" onclick="javascript:showFlash(\''.$value['body_data']['host'].'\', \''.$value['body_data']['flashvar'].'\', this, \''.$value['sid'].'\');"><tr><td class="vdtn hm" style="background: url('.$value['body_data']['imgurl'].') no-repeat"><img src="'.IMGDIR.'/vds.png" alt="'.lang('space', 'click_play').'" /></td></tr></table>';
		} else {
			$html .= "<img src=\"".IMGDIR."/vd.gif\" alt=\"".lang('space', 'click_play')."\" onclick=\"javascript:showFlash('{$value['body_data']['host']}', '{$value['body_data']['flashvar']}', this, '{$value['sid']}');\" class=\"tn\" />";
		}
	}elseif (!empty($value['body_data']['musicvar'])) {
		$html .= "<img src=\"".IMGDIR."/music.gif\" alt=\"".lang('space', 'click_play')."\" onclick=\"javascript:showFlash('music', '{$value['body_data']['musicvar']}', this, '{$value['feedid']}');\" class=\"tn\" />";
	}elseif (!empty($value['body_data']['flashaddr'])) {
		$html .= "<img src=\"".IMGDIR."/flash.gif\" alt=\"".lang('space', 'click_view')."\" onclick=\"javascript:showFlash('flash', '{$value['body_data']['flashaddr']}', this, '{$value['feedid']}');\" class=\"tn\" />";
	}
	
	if ($value['body_general']) {
		$classname = $value['image_1'] ? ' z' : '';
		$html .= "<div class=\"quote$classname\"><blockquote>$value[body_general]</blockquote></div>";
	}

	$html .= "</div>";
	$html .= "</div>";
	$html .= "</li>";
	return $html;
}

function &getlayout($layout='') {
	$layoutarr = array(
			'1:2:1' => array('240', '480', '240'),
			'1:1:2' => array('240', '240', '480'),
			'2:1:1' => array('480', '240', '240'),
			'2:2' => array('480', '480'),
			'1:3' => array('240', '720'),
			'3:1' => array('720', '240'),
			'1:4' => array('190', '770'),
			'4:1' => array('770', '190'),
			'2:2:1' => array('385', '385', '190'),
			'1:2:2' => array('190', '385', '385'),
			'1:1:3' => array('190', '190', '570'),
			'1:3:1' => array('190', '570', '190'),
			'3:1:1' => array('570', '190', '190'),
			'3:2' => array('575', '385'),
			'2:3' => array('385', '575')
	);

	if (!empty($layout)) {
		$rt = (isset($layoutarr[$layout])) ? $layoutarr[$layout] : false;
	} else {
		$rt = $layoutarr;
	}

	return $rt;
}

function getblockdata($blockname = '') {
	$blockarr = lang('space', 'blockdata');
	$r = empty($blockname) ? $blockarr : (isset($blockarr[$blockname]) ? $blockarr[$blockname] : false);
	return $r;
}

?>