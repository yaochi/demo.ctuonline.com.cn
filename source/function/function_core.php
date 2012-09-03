<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_core.php 11560 2010-06-08 05:01:19Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('DISCUZ_CORE_FUNCTION', true);

function error($message, $vars = array(), $return = false) {
	$message = str_replace(array_keys($vars), $vars, lang('error', $message));
	discuz_core::error_log($message);
	if(!$return) {
		global $_G;
		@header('Content-Type: text/html; charset='.$_G['config']['output']['charset']);
		exit($message);
	} else {
		return $message;
	}
}

function updatesession($force = false) {

	global $_G;
	static $updated = false;
	if(!$updated) {
		$discuz = & discuz_core::instance();
		foreach($discuz->session->var as $k => $v) {
			if(isset($_G['member'][$k]) && $k != 'lastactivity') {
				$discuz->session->set($k, $_G['member'][$k]);
			}
		}

		foreach($_G['action'] as $k => $v) {
			$discuz->session->set($k, $v);
		}

		$discuz->session->update();

		$updated = true;
	}
	return $updated;
}

function dmicrotime() {
	return array_sum(explode(' ', microtime()));
}

function setglobal($key , $value, $group = null) {
	global $_G;
	$k = explode('/', $group === null ? $key : $group.'/'.$key);
	switch (count($k)) {
		case 1: $_G[$k[0]] = $value; break;
		case 2: $_G[$k[0]][$k[1]] = $value; break;
		case 3: $_G[$k[0]][$k[1]][$k[2]] = $value; break;
		case 4: $_G[$k[0]][$k[1]][$k[2]][$k[3]] = $value; break;
		case 5: $_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]] =$value; break;
	}
	return true;
}

function getglobal($key, $group = null) {
	global $_G;
	$k = explode('/', $group === null ? $key : $group.'/'.$key);
	switch (count($k)) {
		case 1: return isset($_G[$k[0]]) ? $_G[$k[0]] : null; break;
		case 2: return isset($_G[$k[0]][$k[1]]) ? $_G[$k[0]][$k[1]] : null; break;
		case 3: return isset($_G[$k[0]][$k[1]][$k[2]]) ? $_G[$k[0]][$k[1]][$k[2]] : null; break;
		case 4: return isset($_G[$k[0]][$k[1]][$k[2]][$k[3]]) ? $_G[$k[0]][$k[1]][$k[2]][$k[3]] : null; break;
		case 5: return isset($_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]]) ? $_G[$k[0]][$k[1]][$k[2]][$k[3]][$k[4]] : null; break;
	}
	return null;
}

function getgpc($k, $type='GP') {
	$type = strtoupper($type);
	switch($type) {
		case 'G': $var = &$_GET; break;
		case 'P': $var = &$_POST; break;
		case 'C': $var = &$_COOKIE; break;
		default:
			if(isset($_GET[$k])) {
				$var = &$_GET;
			} else {
				$var = &$_POST;
			}
			break;
	}

	return isset($var[$k]) ? $var[$k] : NULL;

}

function getuserbyuid($uid) {
	static $users = array();
	if(empty($users[$uid])) {
		$users[$uid] = DB::fetch_first("SELECT * FROM ".DB::table('common_member')." WHERE uid='$uid'");
	}
	return $users[$uid];
}

function getuserprofile($field) {
	global $_G;
	if(isset($_G['member'][$field])) {
		return $_G['member'][$field];
	}
	static $tablefields = array(
		'count' => array('extcredits1','extcredits2', 'extcredits3','extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8', 'friends', 'posts', 'threads', 'digestposts', 'doings', 'blogs', 'albums', 'sharings', 'attachsize', 'views'),
		'status' => array('regip', 'lastip', 'lastvisit', 'lastactivity', 'lastpost', 'lastsendmail', 'notifications', 'groupinvitations', 'activityinvitations', 'myinvitations', 'pokes', 'pendingfriends', 'invisible', 'buyercredit', 'sellercredit'),
		'field_forum' => array('publishfeed', 'customshow', 'customstatus', 'medals', 'groupterms', 'authstr', 'groups', 'attentiongroup'),
		'field_home' => array('videophoto', 'domain', 'addsize', 'addfriend', 'theme', 'spacecss', 'blockposition', 'recentnote', 'spacenote', 'privacy', 'feedfriend', 'acceptemail'),
	);
	$profiletable = '';
	foreach($tablefields as $table => $fields) {
		if(in_array($field, $fields)) {
			$profiletable = $table;
			break;
		}
	}
	if($profiletable) {
		$data = DB::fetch_first("SELECT ".implode(',', $tablefields[$table])." FROM ".DB::table('common_member_'.$table)." WHERE uid='$_G[uid]'");
		if(!$data) {
			foreach($tablefields[$table] as $k) {
				$data[$k] = '';
			}
		}
		$_G['member'] = array_merge(is_array($_G['member']) ? $_G['member'] : array(), $data);
		return $_G['member'][$field];
	}
}

function daddslashes($string, $force = 1) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			unset($string[$key]);
			$string[addslashes($key)] = daddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key != '' ? $key : getglobal('authkey'));
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

function dfsockopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
	$return = '';
	$matches = parse_url($url);
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;

	if($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: '.strlen($post)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
	if(!$fp) {
		return '';
	} else {
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
		if(!$status['timed_out']) {
			while (!feof($fp)) {
				if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
					break;
				}
			}

			$stop = false;
			while(!feof($fp) && !$stop) {
				$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
				$return .= $data;
				if($limit) {
					$limit -= strlen($data);
					$stop = $limit <= 0;
				}
			}
		}
		@fclose($fp);
		return $return;
	}
}

function dhtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1',
		str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}

function dexit($message = '') {
	echo $message;
	output();
	exit();
}

function dheader($string, $replace = true, $http_response_code = 0) {
	$string = str_replace(array("\r", "\n"), array('', ''), $string);
	if(empty($http_response_code) || PHP_VERSION < '4.3' ) {
		@header($string, $replace);
	} else {
		@header($string, $replace, $http_response_code);
	}
	if(preg_match('/^\s*location:/is', $string)) {
		exit();
	}
}

function dsetcookie($var, $value = '', $life = 0, $prefix = 1, $httponly = false) {

	global $_G;

	$config = $_G['config']['cookie'];

	$_G['cookie'][$var] = $value;
	$var = ($prefix ? $config['cookiepre'] : '').$var;
	$_COOKIE[$var] = $var;

	if($value == '' || $life < 0) {
		$value = '';
		$life = -1;
	}

	$life = $life > 0 ? getglobal('timestamp') + $life : ($life < 0 ? getglobal('timestamp') - 31536000 : 0);
	$path = $httponly && PHP_VERSION < '5.2.0' ? $config['cookiepath'].'; HttpOnly' : $config['cookiepath'];

	$secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
	if(PHP_VERSION < '5.2.0') {
		setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure);
	} else {
		setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure, $httponly);
	}
}

function getcookie($key) {
	global $_G;
	return isset($_G['cookie'][$key]) ? $_G['cookie'][$key] : '';
}

function fileext($filename) {
	return addslashes(trim(substr(strrchr($filename, '.'), 1, 10)));
}

function formhash($specialadd = '') {
	global $_G;
	$hashadd = defined('IN_ADMINCP') ? 'Only For Discuz! Admin Control Panel' : '';
	return substr(md5(substr($_G['timestamp'], 0, -7).$_G['username'].$_G['uid'].$_G['authkey'].$hashadd.$specialadd), 8, 8);
}

function checkrobot($useragent = '') {
	static $kw_spiders = 'Bot|Crawl|Spider|slurp|sohu-search|lycos|robozilla';
	static $kw_browsers = 'MSIE|Netscape|Opera|Konqueror|Mozilla';

	$useragent = empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent;

	if(!strexists($useragent, 'http://') && preg_match("/($kw_browsers)/i", $useragent)) {
		return false;
	} elseif(preg_match("/($kw_spiders)/i", $useragent)) {
		return true;
	} else {
		return false;
	}
}

function isemail($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

function quescrypt($questionid, $answer) {
	return $questionid > 0 && $answer != '' ? substr(md5($answer.md5($questionid)), 16, 8) : '';
}

function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}

function strexists($string, $find) {
	return !(strpos($string, $find) === FALSE);
}

function avatar($uid, $size = 'middle', $returnsrc = FALSE, $real = FALSE, $static = FALSE, $ucenterurl = '') {
	global $_G;
	static $staticavatar;
	if($staticavatar === null) {
		$staticavatar = $_G['setting']['avatarmethod'];
	}

	$ucenterurl = empty($ucenterurl) ? $_G['setting']['ucenterurl'] : $ucenterurl;
	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
	$uid = abs(intval($uid));
	if(!$staticavatar && !$static) {
		return $returnsrc ? $ucenterurl.'/avatar.php?uid='.$uid.'&size='.$size : '<img src="'.$ucenterurl.'/avatar.php?uid='.$uid.'&size='.$size.($real ? '&type=real' : '').'" />';
	} else {
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		$file = $ucenterurl.'/data/avatar/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).($real ? '_real' : '').'_avatar_'.$size.'.jpg';
		return $returnsrc ? $file : '<img src="'.$file.'" onerror="this.onerror=null;this.src=\''.$ucenterurl.'/images/noavatar_'.$size.'.gif\'" />';
	}
}

function lang($file, $langvar = null, $vars = array()) {
	global $_G;
	list($path, $file) = explode('/', $file);
	if(!$file) {
		$file = $path;
		$path = '';
	}

	if($path != 'plugin') {
		$key = $path == '' ? $file : $path.'_'.$file;
		if(!isset($_G['lang'][$key])) {
			include DISCUZ_ROOT.'./source/language/'.($path == '' ? '' : $path.'/').'lang_'.$file.'.php';
			$_G['lang'][$key] = $lang;
		}
		$returnvalue = &$_G['lang'];
	} else {
		if(!isset($_G['lang']['plugin'])) {
			include DISCUZ_ROOT.'./data/plugindata/lang_plugin.php';
			$_G['lang']['plugin'] = $lang;
		}
		$returnvalue = &$_G['lang']['plugin'];
		$key = &$file;
	}
	$return = $langvar !== null ? (!empty($returnvalue[$key][$langvar]) ? $returnvalue[$key][$langvar] : '') : $returnvalue[$key];
	$return = $return ? $return : $langvar;
	if($vars && is_array($vars)) {
		$searchs = $replaces = array();
		foreach($vars as $k => $v) {
			$searchs[] = '{'.$k.'}';
			$replaces[] = $v;
		}
		$return = str_replace($searchs, $replaces, $return);
	}
	return $return;
}

function checktplrefresh($maintpl, $subtpl, $timecompare, $templateid, $cachefile, $tpldir, $file) {
	static $tplrefresh, $timestamp;
	if($tplrefresh === null) {
		$tplrefresh = getglobal('config/output/tplrefresh');
		$timestamp = getglobal('timestamp');
	}

	if(empty($timecompare) || $tplrefresh == 1 || ($tplrefresh > 1 && !($timestamp % $tplrefresh))) {
		if(empty($timecompare) || @filemtime(DISCUZ_ROOT.$subtpl) > $timecompare) {
			require_once DISCUZ_ROOT.'/source/class/class_template.php';
			$template = new template();
			$template->parse_template($maintpl, $templateid, $tpldir, $file, $cachefile);
			return TRUE;
		}
	}
	return FALSE;
}

function template($file, $templateid = 0, $tpldir = '', $gettplfile = 0) {
	global $_G;
	if(strexists($file, ':')) {
		list($templateid, $file, $clonefile) = explode(':', $file);
		$oldfile = $file;
		$file = empty($clonefile) || STYLEID != $_G['cache']['style_default']['styleid'] ? $file : $file.'_'.$clonefile;
		if($templateid == 'diy' && STYLEID == $_G['cache']['style_default']['styleid']) {
			$_G['style']['prefile'] = '';
			$diypath = DISCUZ_ROOT.'./data/diy/';
			$preend = '_diy_preview';
			$previewname = $diypath.$file.$preend.'.htm';
			$curtplname = $oldfile;
			if(file_exists($diypath.$file.'.htm')) {
				$tpldir = 'data/diy';
				!$gettplfile && $_G['style']['tplsavemod'] = 1;

				$curtplname = $file;
				$flag = file_exists($previewname);
				if($_G['gp_preview'] == 'yes') {
					$file .= $flag ? $preend : '';
				} else {
					$_G['style']['prefile'] = $flag ? 1 : '';
				}

			} elseif(file_exists($diypath.$oldfile.'.htm')) {
				$file = $oldfile;
				$tpldir = 'data/diy';
				!$gettplfile && $_G['style']['tplsavemod'] = 0;

				$curtplname = $file;
				$flag = file_exists($previewname);
				if($_G['gp_preview'] == 'yes') {
					$file .= $flag ? $preend : '';
				} else {
					$_G['style']['prefile'] = $flag ? 1 : '';
				}

			} else {
				$file = $oldfile;
			}
			$tplrefresh = $_G['config']['output']['tplrefresh'];
			if($tpldir == 'data/diy' && ($tplrefresh ==1 || ($tplrefresh > 1 && !($_G['timestamp'] % $tplrefresh))) && @filemtime($diypath.$file.'.htm') &&
			@filemtime($diypath.$file.'.htm') < @filemtime(DISCUZ_ROOT.TPLDIR.'/'.$oldfile.'.htm')) {
				if (!updatediytemplate($file)) {
					@unlink($diypath.$file.'.htm');
					$tpldir = '';
				}
			}

			if (!$gettplfile && empty($_G['style']['tplfile'])) {
				$_G['style']['tplfile'] = empty($clonefile) ? $curtplname : $oldfile.':'.$clonefile;
			}

			$_G['style']['prefile'] = $_G['gp_preview'] == 'yes' && $_G['gp_preview'] == 'yes' ? '' : $_G['style']['prefile'];

		} else {
			$tpldir = './source/plugin/'.$templateid.'/template';
		}
	}
	$file .= !empty($_G['inajax']) && ($file == 'common/header' || $file == 'common/footer') ? '_ajax' : '';
	$tpldir = $tpldir ? $tpldir : (defined('TPLDIR') ? TPLDIR : '');
	$templateid = $templateid ? $templateid : (defined('TEMPLATEID') ? TEMPLATEID : '');
	$tplfile = ($tpldir ? $tpldir.'/' : './template/').$file.'.htm';
	$filebak = $file;
	$file == 'common/header' && defined('CURMODULE') && CURMODULE && $file = 'common/header_'.CURMODULE;
	$cachefile = './data/template/'.(defined('STYLEID') ? STYLEID.'_' : '_').$templateid.'_'.str_replace('/', '_', $file).'.tpl.php';
	if($templateid != 1 && !file_exists(DISCUZ_ROOT.$tplfile)) {
		$tplfile = './template/default/'.$filebak.'.htm';
	}
	if($gettplfile) {
		return $tplfile;
	}

	checktplrefresh($tplfile, $tplfile, @filemtime($cachefile), $templateid, $cachefile, $tpldir, $file);
	return $cachefile;
}

function loaducenter() {
	require_once DISCUZ_ROOT.'./config/config_ucenter.php';
	require_once DISCUZ_ROOT.'./uc_client/client.php';
}

function loadcache($cachenames, $force = false) {
	global $_G;
	static $loadedcache = array();
	$cachenames = is_array($cachenames) ? $cachenames : array($cachenames);
	$caches = array();
	foreach ($cachenames as $k) {
		if(!isset($loadedcache[$k]) || $force) {
			$caches[] = $k;
			$loadedcache[$k] = true;
		}
	}

	if(!empty($caches)) {
		$cachedata = cachedata($caches);
		foreach($cachedata as $cname => $data) {
			if($cname == 'setting') {
				$_G['setting'] = $data;
			} elseif (strexists($cname, 'usergroup_'.$_G['groupid'])) {
				$_G['cache'][$cname] = $_G['group'] = $data;
			} elseif ($cname == 'style_default') {
				$_G['cache'][$cname] = $_G['style'] = $data;
			} elseif($cname == 'grouplevels') {
				$_G['grouplevels'] = $data;
			} else {
				$_G['cache'][$cname] = $data;
			}
		}
	}
	return true;
}

function cachedata($cachenames) {
	static $isfilecache, $allowmem;

	if($isfilecache === null) {
		$isfilecache = getglobal('config/cache/type') == 'file';
		$allowmem = memory('check');
	}

	$data = array();
	$cachenames = is_array($cachenames) ? $cachenames : array($cachenames);
	if($allowmem) {
		$newarray = array();
		foreach ($cachenames as $name) {
			$data[$name] = memory('get', $name);
			if($data[$name] === null) {
				$data[$name] = null;
				$newarray[] = $name;
			}
		}
		if(empty($newarray)) {
			return $data;
		} else {
			$cachenames = $newarray;
		}
	}

	if($isfilecache) {
		$lostcaches = array();
		foreach($cachenames as $cachename) {
			if(!@include_once(DISCUZ_ROOT.'./data/cache/cache_'.$cachename.'.php')) {
				$lostcaches[] = $cachename;
			}
		}
		if(!$lostcaches) {
			return $data;
		}
		$cachenames = $lostcaches;
		unset($lostcaches);
	}
	$query = DB::query("SELECT /*!40001 SQL_CACHE */ * FROM ".DB::table('common_syscache')." WHERE cname IN ('".implode("','", $cachenames)."')");
	while($syscache = DB::fetch($query)) {
		$data[$syscache['cname']] = $syscache['ctype'] ? unserialize($syscache['data']) : $syscache['data'];
		$allowmem && (memory('set', $syscache['cname'], $data[$syscache['cname']]));
		if($isfilecache) {
			$cachedata = '$data[\''.$syscache['cname'].'\'] = '.var_export($data[$syscache['cname']], true).";\n\n";
			if($fp = @fopen(DISCUZ_ROOT.'./data/cache/cache_'.$syscache['cname'].'.php', 'wb')) {
				fwrite($fp, "<?php\n//Discuz! cache file, DO NOT modify me!\n//Identify: ".md5($syscache['cname'].$cachedata)."\n\n$cachedata?>");
				fclose($fp);
			}
		}
	}

	foreach ($cachenames as $name) {
		if($data[$name] === null) {
			$data[$name] = null;
			$allowmem && (memory('set', $name, array()));
		}
	}
	return $data;
}

function dgmdate($timestamp, $format = 'dt', $timeoffset = '9999', $uformat = '') {
	global $_G;
	$format == 'u' && !$_G['setting']['dateconvert'] && $format = 'dt';
	static $dformat, $tformat, $dtformat, $offset, $lang;
	if($dformat === null) {
		$dformat = getglobal('setting/dateformat');
		$tformat = getglobal('setting/timeformat');
		$dtformat = $dformat.' '.$tformat;
		$offset = getglobal('member/timeoffset');
		$lang = lang('core', 'date');
	}
	$timeoffset = $timeoffset == 9999 ? $offset : $timeoffset;
	$timestamp += $timeoffset * 3600;
	$format = empty($format) || $format == 'dt' ? $dtformat : ($format == 'd' ? $dformat : ($format == 't' ? $tformat : $format));
	if($format == 'u') {
		$todaytimestamp = TIMESTAMP - (TIMESTAMP + $timeoffset * 3600) % 86400 + $timeoffset * 3600;
		$s = gmdate(!$uformat ? $dtformat : $uformat, $timestamp);
		$time = TIMESTAMP + $timeoffset * 3600 - $timestamp;
		if($timestamp >= $todaytimestamp) {
			if($time > 3600) {
				return '<span title="'.$s.'">'.intval($time / 3600).'&nbsp;'.$lang['hour'].$lang['before'].'</span>';
			} elseif($time > 1800) {
				return '<span title="'.$s.'">'.$lang['half'].$lang['hour'].$lang['before'].'</span>';
			} elseif($time > 60) {
				return '<span title="'.$s.'">'.intval($time / 60).'&nbsp;'.$lang['min'].$lang['before'].'</span>';
			} elseif($time > 0) {
				return '<span title="'.$s.'">'.$time.'&nbsp;'.$lang['sec'].$lang['before'].'</span>';
			} elseif($time == 0) {
				return '<span title="'.$s.'">'.$lang['now'].'</span>';
			} else {
				return $s;
			}
		} elseif(($days = intval(($todaytimestamp - $timestamp) / 86400)) >= 0 && $days < 7) {
			if($days == 0) {
				return '<span title="'.$s.'">'.$lang['yday'].'&nbsp;'.gmdate($tformat, $timestamp).'</span>';
			} elseif($days == 1) {
				return '<span title="'.$s.'">'.$lang['byday'].'&nbsp;'.gmdate($tformat, $timestamp).'</span>';
			} else {
				return '<span title="'.$s.'">'.($days + 1).'&nbsp;'.$lang['day'].$lang['before'].'</span>';
			}
		} else {
			return $s;
		}
	} else {
		return gmdate($format, $timestamp);
	}
}

function dmktime($date) {
	if(strpos($date, '-')) {
		$time = explode('-', $date);
		return mktime(0, 0, 0, $time[1], $time[2], $time[0]);
	}
	return 0;
}

function save_syscache($cachename, $data) {
	static $isfilecache, $allowmem;
	if($isfilecache === null) {
		$isfilecache = getglobal('config/cache/type');
		$allowmem = memory('check');
	}

	if(is_array($data)) {
		$ctype = 1;
		$data = addslashes(serialize($data));
	} else {
		$ctype = 0;
	}

	DB::query("REPLACE INTO ".DB::table('common_syscache')." (cname, ctype, dateline, data) VALUES ('$cachename', '$ctype', '".TIMESTAMP."', '$data')");

	$allowmem && memory('rm', $cachename);
	$isfilecache && @unlink(DISCUZ_ROOT.'./data/cache/cache_'.$cachename.'.php');
}

function block_get($parameter) {
	global $_G;
	static $allowmem;
	if($allowmem === null) {
		include_once libfile('function/block');
		$allowmem = getglobal('setting/memory/diyblock/enable') && memory('check');
	}
	if(!$allowmem) {
		block_get_batch($parameter);
		return true;
	}
	$blockids = explode(',', $parameter);
	$lostbids = array();
	foreach ($blockids as $bid) {
		$bid = intval($bid);
		if($bid) {
			$_G['block'][$bid] = memory('get', 'blockcache_'.$bid);
			if($_G['block'][$bid] === null) {
				$lostbids[] = $bid;
			}
		}
	}

	if($lostbids) {
		block_get_batch(implode(',', $lostbids));
		foreach ($lostbids as $bid) {
			if(isset($_G['block'][$bid])) {
				memory('set', 'blockcache_'.$bid, $_G['block'][$bid], getglobal('setting/memory/diyblock/ttl'));
			}
		}
	}
}

function block_display($bid) {
	include_once libfile('function/block');
	block_display_batch($bid);
}

function cpmsg_mgr($message, $url = '', $type = '', $values = array(), $extra = '', $halt = TRUE){
	include_once libfile('function/admincp');
	global $_G;
	$vars = explode(':', $message);
	if(count($vars) == 2) {
		$message = lang('plugin/'.$vars[0], $vars[1], $values);
	} else {
		$message = cplang($message, $values);
	}
	switch($type) {
		case 'succeed': $classname = 'infotitle2';break;
		case 'error': $classname = 'infotitle3';break;
		case 'loadingform': case 'loading': $classname = 'infotitle1';break;
		default: $classname = 'marginbot normal';break;
	}
	$message = "<h4 class=\"$classname\">$message</h4>";
	$url .= $url && !empty($_G['gp_scrolltop']) ? '&scrolltop='.intval($_G['gp_scrolltop']) : '';

	if($type == 'form') {
		$message = "<form method=\"post\" action=\"$url\"><input type=\"hidden\" name=\"formhash\" value=\"".FORMHASH."\">".
			"<br />$message$extra<br />".
			"<p class=\"margintop\"><input type=\"submit\" class=\"btn\" name=\"confirmed\" value=\"".cplang('ok')."\"> &nbsp; \n".
			"<input type=\"button\" class=\"btn\" value=\"".cplang('cancel')."\" onClick=\"history.go(-1);\"></p></form><br />";
	} elseif($type == 'loadingform') {
		$message = "<form method=\"post\" action=\"$url\" id=\"loadingform\"><input type=\"hidden\" name=\"formhash\" value=\"".FORMHASH."\"><br />$message$extra<img src=\"static/image/admincp/ajax_loader.gif\" class=\"marginbot\" /><br />".
			'<p class="marginbot"><a href="###" onclick="$(\'loadingform\').submit();" class="lightlink">'.cplang('message_redirect').'</a></p></form><br /><script type="text/JavaScript">setTimeout("$(\'loadingform\').submit();", 2000);</script>';
	} else {
		$message .= $extra.($type == 'loading' ? '<img src="static/image/admincp/ajax_loader.gif" class="marginbot" />' : '');
		$return = cplang('return');
		if($url) {
			if($type == 'button') {
				$message = "<br />$message<br /><p class=\"margintop\"><input type=\"submit\" class=\"btn\" name=\"submit\" value=\"".cplang('start')."\" onclick=\"location.href='$url'\" />";
			} else {
				$message .= '<p class="marginbot"><a href="'.$url.'" class="lightlink">'.cplang('message_redirect').'</a></p>';
				$message .= "<script type=\"text/JavaScript\">setTimeout(\"redirect('$url');\", 2000);</script>";
			}
		} elseif(strpos($message, $return)) {
			$message .= '<p class="marginbot"><a href="javascript:history.go(-1);" class="lightlink">'.cplang('message_return').'</a></p>';
		}
	}
	if($halt) {
		$message =  '<h3>'.cplang('discuz_message').'</h3><div class="infobox">'.$message.'</div>';
	} else {
		$message  = '<div class="infobox">'.$message.'</div>';
	}
	include template("manage/cpmsg");
	exit();
}

function dimplode($array) {
	if(!empty($array)) {
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return '';
	}
}

function libfile($libname, $folder = '') {
	$libpath = DISCUZ_ROOT.'/source/'.$folder;
	if(strstr($libname, '/')) {
		list($pre, $name) = explode('/', $libname);
		return realpath("{$libpath}/{$pre}/{$pre}_{$name}.php");
	} else {
		return realpath("{$libpath}/{$libname}.php");
	}
}

function cutstr($string, $length, $dot = ' ...') {
	if(strlen($string) <= $length) {
		return $string;
	}

	$pre = '{%';
	$end = '%}';
	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), $string);

	$strcut = '';
	if(strtolower(CHARSET) == 'utf-8') {

		$n = $tn = $noc = 0;
		while($n < strlen($string)) {

			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}

			if($noc >= $length) {
				break;
			}

		}
		if($noc > $length) {
			$n -= $tn;
		}

		$strcut = substr($string, 0, $n);

	} else {
		for($i = 0; $i < $length; $i++) {
			$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
		}
	}

	$strcut = str_replace(array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

	return $strcut.$dot;
}

function dstripslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}

function aidencode($aid) {
	global $_G;
	return rawurlencode(base64_encode($aid.'|'.substr(md5($aid.md5($_G['authkey']).TIMESTAMP.$_G['uid']), 0, 8).'|'.TIMESTAMP.'|'.$_G['uid']));
}

function getforumimg($aid, $nocache = 0, $w = 140, $h = 140, $type = '') {
	global $_G;
	$key = authcode("$aid\t$w\t$h", 'ENCODE', $_G['config']['security']['authkey']);
	return 'forum.php?mod=image&aid='.$aid.'&size='.$w.'x'.$h.'&key='.rawurlencode($key).($nocache ? '&nocache=yes' : '').($type ? '&type='.$type : '');
}

function rewritedata() {
	global $_G;
	$data = array();
	if(!defined('IN_ADMINCP')) {
		if(in_array('portal_topic', $_G['setting']['rewritestatus'])) {
			$data['search']['portal_topic'] = "/".$_G['domain']['pregxp']['portal']."\?mod\=topic&(amp;)?topic\=(.+?)?\"([^\>]*)\>/e";
			$data['replace']['portal_topic'] = "rewriteoutput('portal_topic', 0, '\\1', '\\3', '\\4')";
		}

		if(in_array('portal_article', $_G['setting']['rewritestatus'])) {
			$data['search']['portal_article'] = "/".$_G['domain']['pregxp']['portal']."\?mod\=view&(amp;)?aid\=(\d+)(&amp;page\=(\d+))?\"([^\>]*)\>/e";
			$data['replace']['portal_article'] = "rewriteoutput('portal_article', 0, '\\1', '\\3', '\\5', '\\6')";
		}

		if(in_array('forum_forumdisplay', $_G['setting']['rewritestatus'])) {
			$data['search']['forum_forumdisplay'] = "/".$_G['domain']['pregxp']['forum']."\?mod\=forumdisplay&(amp;)?fid\=(\w+)(&amp;page\=(\d+))?\"([^\>]*)\>/e";
			$data['replace']['forum_forumdisplay'] = "rewriteoutput('forum_forumdisplay', 0, '\\1', '\\3', '\\5', '\\6')";
		}

		if(in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
			$data['search']['forum_viewthread'] = "/".$_G['domain']['pregxp']['forum']."\?mod\=viewthread&(amp;)?tid\=(\d+)(&amp;extra\=(page\%3D(\d+))?)?(&amp;page\=(\d+))?\"([^\>]*)\>/e";
			$data['replace']['forum_viewthread'] = "rewriteoutput('forum_viewthread', 0, '\\1', '\\3', '\\8', '\\6', '\\9')";
		}

		if(in_array('group_group', $_G['setting']['rewritestatus'])) {
			$data['search']['group_group'] = "/".$_G['domain']['pregxp']['forum']."\?mod\=group&(amp;)?fid\=(\d+)(&amp;page\=(\d+))?\"([^\>]*)\>/e";
			$data['replace']['group_group'] = "rewriteoutput('group_group', 0, '\\1', '\\3', '\\5', '\\6')";
		}

		if(in_array('home_space', $_G['setting']['rewritestatus'])) {
			$data['search']['home_space'] = "/".$_G['domain']['pregxp']['home']."\?mod=space&(amp;)?(uid\=(\d+)|username\=([^&]+?))\"([^\>]*)\>/e";
			$data['replace']['home_space'] = "rewriteoutput('home_space', 0, '\\1', '\\4', '\\5', '\\6')";
		}

		if(in_array('all_script', $_G['setting']['rewritestatus'])) {
			$data['search']['all_script'] = "/<a href\=\"()([a-z]+)\.php\?mod=([^\"]+?)\"([^\>]*)?\>/e";
			$data['replace']['all_script'] = "rewriteoutput('all_script', 0, '\\1', '\\2', '\\3', '\\4', '\\5')";
		}
	} else {
		$data['rulesearch']['portal_topic'] = 'topic-{name}.html';
		$data['rulereplace']['portal_topic'] = 'portal.php?mod=topic&topic={name}';
		$data['rulevars']['portal_topic']['{name}'] = '(.+)';

		$data['rulesearch']['portal_article'] = 'article-{id}-{page}.html';
		$data['rulereplace']['portal_article'] = 'portal.php?mod=view&aid={id}&page={page}';
		$data['rulevars']['portal_article']['{id}'] = '([0-9]+)';
		$data['rulevars']['portal_article']['{page}'] = '([0-9]+)';

		$data['rulesearch']['forum_forumdisplay'] = 'forum-{fid}-{page}.html';
		$data['rulereplace']['forum_forumdisplay'] = 'forum.php?mod=forumdisplay&fid={fid}&page={page}';
		$data['rulevars']['forum_forumdisplay']['{fid}'] = '(\w+)';
		$data['rulevars']['forum_forumdisplay']['{page}'] = '([0-9]+)';

		$data['rulesearch']['forum_viewthread'] = 'thread-{tid}-{page}-{prevpage}.html';
		$data['rulereplace']['forum_viewthread'] = 'forum.php?mod=viewthread&tid={tid}&extra=page\%3D{prevpage}&page={page}';
		$data['rulevars']['forum_viewthread']['{tid}'] = '([0-9]+)';
		$data['rulevars']['forum_viewthread']['{page}'] = '([0-9]+)';
		$data['rulevars']['forum_viewthread']['{prevpage}'] = '([0-9]+)';

		$data['rulesearch']['group_group'] = 'group-{fid}-{page}.html';
		$data['rulereplace']['group_group'] = 'forum.php?mod=group&fid={fid}&page={page}';
		$data['rulevars']['group_group']['{fid}'] = '([0-9]+)';
		$data['rulevars']['group_group']['{page}'] = '([0-9]+)';

		$data['rulesearch']['home_space'] = 'space-{user}-{value}.html';
		$data['rulereplace']['home_space'] = 'home.php?mod=space&{user}={value}';
		$data['rulevars']['home_space']['{user}'] = '(username|uid)';
		$data['rulevars']['home_space']['{value}'] = '(.+)';

		$data['rulesearch']['all_script'] = '{script}-{param}.html';
		$data['rulereplace']['all_script'] = '{script}.php?rewrite={param}';
		$data['rulevars']['all_script']['{script}'] = '([a-z]+)';
		$data['rulevars']['all_script']['{param}'] = '(.+)';
	}
	return $data;
}

function rewriteoutput($type, $returntype, $host) {
	global $_G;
	$host = $host ? 'http://'.$host : '';
	$fextra = '';
	if($type == 'forum_forumdisplay') {
		list(,,, $fid, $page, $extra) = func_get_args();
		$r = array(
			'{fid}' => empty($_G['setting']['forumkeys'][$fid]) ? $fid : $_G['setting']['forumkeys'][$fid],
			'{page}' => $page ? $page : 1,
		);

	} elseif($type == 'forum_viewthread') {
		list(,,, $tid, $page, $prevpage, $extra) = func_get_args();
		$r = array(
			'{tid}' => $tid,
			'{page}' => $page ? $page : 1,
			'{prevpage}' => $prevpage && !IS_ROBOT ? $prevpage : 1,
		);
	} elseif($type == 'home_space') {
		list(,,, $uid, $username, $extra) = func_get_args();
		$_G['setting']['rewritecompatible'] && $username = rawurlencode($username);
		$r = array(
			'{user}' => $uid ? 'uid' : 'username',
			'{value}' => $uid ? $uid : $username,
		);
	} elseif($type == 'group_group') {
		list(,,, $fid, $page, $extra) = func_get_args();
		$r = array(
			'{fid}' => $fid,
			'{page}' => $page ? $page : 1,
		);
	} elseif($type == 'portal_topic') {
		list(,,, $name, $extra) = func_get_args();
		$r = array(
			'{name}' => $name,
		);
	} elseif($type == 'portal_article') {
		list(,,, $id, $page, $extra) = func_get_args();
		$r = array(
			'{id}' => $id,
			'{page}' => $page ? $page : 1,
		);
	} elseif($type == 'all_script') {
		list(,,, $script, $param, $extra) = func_get_args();
		if(strexists($extra, 'showWindow') || strexists($extra, 'ajax') || strexists($param, '/') || strexists($param, '%2F') || strexists($param, '-')) {
			return '<a href="'.$script.'.php?mod='.$param.'"'.dstripslashes($extra).'>';
		}
		if(($apos = strrpos($param, '#')) !== FALSE) {
			$fextra = substr($param, $apos);
			$param = substr($param, 0, $apos);
		}
		$param = str_replace('&amp;', '&', $param);
		parse_str($param, $params);
		$param = $comma = '';
		$i = 0;
		foreach($params as $k => $v) {
			if($i) {
				$param .= $comma.$k.'-'.rawurlencode($v);
				$comma = '-';
			} else {
				$param .= $k.'-';$i++;
			}
		}
		$r = array(
			'{script}' => $script,
			'{param}' => substr($param, -1) != '-' ? $param : substr($param, 0, strlen($param) -1),
		);
	} elseif($type == 'site_default') {
		list(,,, $url) = func_get_args();
		if(!preg_match('/^\w+\.php/i', $url) || preg_match('/^member\.php/i', $url)) {
			$host = '';
		}
		if(!$returntype) {
			return '<a href="'.$host.$url.'"';
		} else {
			return $host.$url;
		}
	}
	$href = str_replace(array_keys($r), $r, $_G['setting']['rewriterule'][$type]).$fextra;
	if(!$returntype) {
		return '<a href="'.$host.$href.'"'.dstripslashes($extra).'>';
	} else {
		return $host.$href;
	}
}

function output() {

	global $_G;


	if(defined('DISCUZ_OUTPUTED')) {
		return;
	} else {
		define('DISCUZ_OUTPUTED', 1);
	}

	if(!empty($_G['blockupdate'])) {
		block_updatecache($_G['blockupdate']['bid']);
	}

	$_G['domain'] = array();
	foreach($_G['config']['app']['domain'] as $app => $domain) {
		if($domain || $_G['config']['app']['domain']['default']) {
			$domain = empty($domain) ? $_G['config']['app']['domain']['default'] : $domain;
			$_G['domain']['search'][$app] = "<a href=\"{$app}.php";
			$_G['domain']['replace'][$app] = '<a href="http://'.$domain.$_G['siteroot'].$app.'.php';
			$_G['domain']['pregxp'][$app] = '<a href\="http\:\/\/('.preg_quote($domain.$_G['siteroot'], '/').')'.preg_quote($app.'.php', '/');
		} else {
			$_G['domain']['pregxp'][$app] = "<a href\=\"(){$app}.php";
		}
	}

	if($_G['setting']['rewritestatus'] || $_G['domain']['search']) {

		$content = ob_get_contents();

		$_G['domain']['search'] && $content = str_replace($_G['domain']['search'], $_G['domain']['replace'], $content);

		$_G['config']['app']['domain']['default'] && $content = preg_replace("/<a href=\"([^\"]+)\"/e", "rewriteoutput('site_default', 0, '".$_G['config']['app']['domain']['default'].$_G['siteroot']."', '\\1')", $content);

		if($_G['setting']['rewritestatus'] && !defined('IN_MODCP') && !defined('IN_ADMINCP')) {
			$searcharray = $replacearray = array();
			$array = rewritedata();
			$content = preg_replace($array['search'], $array['replace'], $content);
		}

		ob_end_clean();
		$_G['gzipcompress'] ? ob_start('ob_gzhandler') : ob_start();

		echo $content;
	}
	if($_G['setting']['ftp']['connid']) {
		@ftp_close($_G['setting']['ftp']['connid']);
	}
	$_G['setting']['ftp'] = array();

	if(defined('CACHE_FILE') && CACHE_FILE && !defined('CACHE_FORBIDDEN')) {
		global $_G;
		if(diskfreespace(DISCUZ_ROOT.'./'.$_G['setting']['cachethreaddir']) > 1000000) {
			if($fp = @fopen(CACHE_FILE, 'w')) {
				flock($fp, LOCK_EX);
				fwrite($fp, empty($content) ? ob_get_contents() : $content);
			}
			@fclose($fp);
			chmod(CACHE_FILE, 0777);
		}
	}

	if(defined('DISCUZ_DEBUG') && DISCUZ_DEBUG && @include(libfile('function/debug'))) {
		function_exists('debugmessage') && debugmessage();
	}
}

function runhooks() {
	global $_G;
	if(defined('CURMODULE') && isset($_G['setting']['hookscript'][$_G['basescript']][CURMODULE]['module'])) {
		hookscript(CURMODULE);
	}
}

function hookscript($script, $type = 'funcs', $param = array()) {
	global $_G;
	static $pluginclasses;
	$hscript = $script != 'global' ? $_G['basescript'] : 'global';
	if(!isset($_G['cache']['plugin'])) {
		loadcache('plugin');
	}
	foreach((array)$_G['setting']['hookscript'][$hscript][$script]['module'] as $identifier => $include) {
		$hooksadminid[$identifier] = !$_G['setting']['hookscript'][$hscript][$script]['adminid'][$identifier] || ($_G['setting']['hookscript'][$hscript][$script]['adminid'][$identifier] && $_G['adminid'] > 0 && $_G['setting']['hookscript'][$_G['basescript']][$script]['adminid'][$identifier] >= $_G['adminid']);
		if($hooksadminid[$identifier]) {
			@include_once DISCUZ_ROOT.'./source/plugin/'.$include.'.class.php';
		}
	}
	if(@is_array($_G['setting']['hookscript'][$hscript][$script][$type])) {
		foreach($_G['setting']['hookscript'][$hscript][$script][$type] as $hookkey => $hookfuncs) {
			foreach($hookfuncs as $hookfunc) {
				if($hooksadminid[$hookfunc[0]]) {
					$classkey = 'plugin_'.($hookfunc[0].($hscript != 'global' ? '_'.$hscript : ''));
					if(!class_exists($classkey)) {
						continue;
					}
					if(!isset($pluginclasses[$classkey])) {
						$pluginclasses[$classkey] = new $classkey;
					}
					if(!method_exists($pluginclasses[$classkey], $hookfunc[1])) {
						continue;
					}
					$return = $pluginclasses[$classkey]->$hookfunc[1]($param);
					if(is_array($return)) {
						foreach($return as $k => $v) {
							$_G['setting']['pluginhooks'][$hookkey][$k] .= $v;
						}
					} else {
						$_G['setting']['pluginhooks'][$hookkey] .= $return;
					}
				}
			}
		}
	}
}

function hookscriptoutput($tplfile) {
	global $_G;
	if(isset($_G['setting']['hookscript']['global']['global']['module'])) {
		hookscript('global');
	}
	if(isset($_G['setting']['hookscript'][$_G['basescript']][CURMODULE]['outputfuncs'])) {
		hookscript(CURMODULE, 'outputfuncs', array('template' => $tplfile, 'message' => $_G['hookscriptmessage'], 'values' => $_G['hookscriptvalues']));
	}
}

function pluginmodule($pluginid, $type) {
	global $_G;
	if(!isset($_G['cache']['plugin'])) {
		loadcache('plugin');
	}
	list($identifier, $module) = explode(':', $pluginid);
	if(!is_array($_G['setting']['plugins'][$type]) || !array_key_exists($pluginid, $_G['setting']['plugins'][$type])) {
		showmessage('undefined_action');
	}
	if(!empty($_G['setting']['plugins'][$type][$pluginid]['url'])) {
		dheader('location: '.$_G['setting']['plugins'][$type][$pluginid]['url']);
	}
	$directory = $_G['setting']['plugins'][$type][$pluginid]['directory'];
	if(empty($identifier) || !preg_match("/^[a-z]+[a-z0-9_]*\/$/i", $directory) || !preg_match("/^[a-z0-9_\-]+$/i", $module)) {
		showmessage('undefined_action');
	}
	if(@!file_exists(DISCUZ_ROOT.($modfile = './source/plugin/'.$directory.$module.'.inc.php'))) {
		showmessage('plugin_module_nonexistence', '', array('mod' => $modfile));
	}
	return DISCUZ_ROOT.$modfile;
}

function pluginapi($pluginid, $param) {
	global $_G;
	if(!isset($_G['cache']['plugin'])) {
		loadcache('plugin');
	}
	$param=urldecode($param);
	list($identifier, $module) = explode(':', $pluginid);
	$directory = $_G['setting']['plugins']['appapi'][$pluginid][$pluginid]['directory'];
	if(empty($identifier) || !preg_match("/^[a-z]+[a-z0-9_]*\/$/i", $directory) || !preg_match("/^[a-z0-9_\-]+$/i", $module)) {
		$res[data]=array();
		$res[error]='1';
		return json_encode($res);
	}
	if(@!file_exists(DISCUZ_ROOT.($modfile = './source/plugin/'.$directory.$module.'.inc.php'))) {
		showmessage('plugin_module_nonexistence', '', array('mod' => $modfile));
	}else{
		$url=$_G[config][app][url].'/source/plugin/'.$directory.$module.'.inc.php?'.$param;
	}

	return openURL($url);
}

function join_home_pluginmodule($pluginname){
	global $_G;
	if(!isset($_G['cache']['plugin'])) {
		loadcache('plugin');
	}

	$directory = $_G['setting']['plugins']["available_plugins"][$pluginname]['directory'];
	foreach($_G['setting']['plugins']["available_plugins"][$pluginname]['modules'] as $key=>$value){
		if($value[type]==nosomething){
			$module=$value[name];
		}
	}
	if(!preg_match("/^[a-z]+[a-z0-9_]*\/$/i", $directory) || !preg_match("/^[a-z0-9_\-]+$/i", $module)) {
		//showmessage('undefined_action');
		//begin,update by qiaoyongzhi 2011-2-23 ,当操作没有权限时修改显示语。EKSN 103
		showmessage("未定义操作!<br/>原因：没有权限。");
		//end
	}
	if(@!file_exists(DISCUZ_ROOT.($modfile = './source/plugin/'.$directory.$module.'.inc.php'))) {
		showmessage('plugin_module_nonexistence', '', array('mod' => $modfile));
	}
	return DISCUZ_ROOT.$modfile;
}

function join_group_pluginmodule($pluginname, $pluginop){
	global $_G;
	if(!$_G["group_plugins"]["group_available"][$pluginname]){
		showmessage('undefined_action');
	}
	if(!empty($_G["group_plugins"]["group_available"][$pluginname]['url'])) {
		dheader('location: '.$_G['group_plugins']['group_available'][$pluginname]['url']);
	}
	$directory = $_G["group_plugins"]["group_available"][$pluginname]['directory'];
	$module = $_G["group_plugins"]["group_available"][$pluginop][$pluginname]["name"];
	if(!preg_match("/^[a-z]+[a-z0-9_]*\/$/i", $directory) || !preg_match("/^[a-z0-9_\-]+$/i", $module)) {
		//showmessage('undefined_action');
		//begin,update by qiaoyongzhi 2011-2-23 ,当操作没有权限时修改显示语。EKSN 103
		showmessage("未定义操作!<br/>原因：没有权限。");
		//end
	}
	if(@!file_exists(DISCUZ_ROOT.($modfile = './source/plugin/'.$directory.$module.'.inc.php'))) {
		showmessage('plugin_module_nonexistence', '', array('mod' => $modfile));
	}
	return DISCUZ_ROOT.$modfile;
}

function updatecreditbyaction($action, $uid = 0, $extrasql = array(), $needle = '', $coef = 1, $update = 1, $fid = 0) {
	/*去掉积分策略
	 include_once libfile('class/credit');
	 $credit = & credit::instance();
	 if($extrasql) {
		$credit->extrasql = $extrasql;
		}
		return $credit->execrule($action, $uid, $needle, $coef, $update, $fid);*/

}

function checklowerlimit($action, $uid = 0, $coef = 1) {
	global $_G;

	include_once libfile('class/credit');
	$credit = & credit::instance();
	$limit = $credit->lowerlimit($action, $uid, $coef);
	if($limit !== true) {
		$GLOBALS['id'] = $limit;
		showmessage('credits_policy_lowerlimit', '', array(
			'title' => $_G['setting']['extcredits'][$limit]['title'],
			'lowerlimit' => $_G['setting']['creditspolicy']['lowerlimit'][$limit],
			'unit' => $_G['setting']['extcredits'][$limit]['unit'])
		);
	}
}

function batchupdatecredit($action, $uids = 0, $extrasql = array(), $coef = 1, $fid = 0) {

	include_once libfile('class/credit');
	$credit = & credit::instance();
	if($extrasql) {
		$credit->extrasql = $extrasql;
	}
	return $credit->updatecreditbyrule($action, $uids, $coef, $fid);
}


function updatemembercount($uids, $dataarr = array(), $checkgroup = true, $operation = '', $relatedid = 0, $ruletxt = '') {
	/*if(!is_array($dataarr) || empty($dataarr)) return;
	 if($operation && $relatedid) {
		$writelog = true;
		$log = array(
		'uid' => $uids,
		'operation' => $operation,
		'relatedid' => $relatedid,
		'dateline' => time(),
		);
		} else {
		$writelog = false;
		}
		$data = array();
		foreach($dataarr as $key => $val) {
		if(empty($val)) continue;
		$val = intval($val);
		$id = intval($key);
		if(0< $id && $id < 9) {
		$data['extcredits'.$id] = $val;
		$writelog && $log['extcredits'.$id] = $val;
		} else {
		$data[$key] = $val;
		}
		}
		if($writelog) {
		DB::insert('common_credit_log', $log);
		}
		if($data) {
		include_once libfile('class/credit');
		$credit = & credit::instance();
		$credit->updatemembercount($data, $uids, $checkgroup, $ruletxt);
		}*/
}

function checkusergroup($uid = 0) {
	include_once libfile('class/credit');
	$credit = & credit::instance();
	$credit->checkusergroup($uid);
}

function checkformulasyntax($formula, $operators, $tokens) {
	$var = implode('|', $tokens);
	$operator = implode('', $operators);

	$operator = str_replace(
	array('+', '-', '*', '/', '(', ')', '{', '}', '\''),
	array('\+', '\-', '\*', '\/', '\(', '\)', '\{', '\}', '\\\''),
	$operator
	);

	if(!empty($formula)) {
		if(!preg_match("/^([$operator\.\d\(\)]|(($var)([$operator\(\)]|$)+))+$/", $formula) || !is_null(eval(preg_replace("/($var)/", "\$\\1", $formula).';'))){
			return false;
		}
	}
	return true;
}

function checkformulacredits($formula) {
	return checkformulasyntax(
	$formula,
	array('+', '-', '*', '/'),
	array('extcredits[1-8]', 'digestposts', 'posts', 'threads', 'friends', 'doings', 'polls', 'blogs', 'albums', 'sharings')
	);
}

function checkformulaperm($formula) {
	$formula = preg_replace('/(\{([\d\.\-]+?)\})/', "'\\1'", $formula);
	return checkformulasyntax(
	$formula,
	array('+', '-', '*', '/', '(', ')', '<', '=', '>', '!', 'and', 'or', ' ', '{', '}', "'"),
	array('regdate', 'regday', 'regip', 'lastip', 'buyercredit', 'sellercredit', 'digestposts', 'posts', 'threads', 'extcredits[1-8]', 'field[\d]+')
	);
}

function debug($var = null) {
	echo '<pre>';
	if($var === null) {
		print_r($GLOBALS);
	} else {
		print_r($var);
	}
	exit();
}

function debuginfo() {
	global $_G;
	if(getglobal('setting/debug')) {
		$db = & DB::object();
		$_G['debuginfo'] = array('time' => number_format((dmicrotime() - $_G['starttime']), 6), 'queries' => $db->querynum, 'memory' => ucwords($_G['memory']));
		return TRUE;
	} else {
		return FALSE;
	}
}

function getfocus_rand($module) {
	global $_G;

	if(empty($_G['setting']['focus']) || !array_key_exists($module, $_G['setting']['focus'])) {
		return null;
	}
	do {
		$focusid = $_G['setting']['focus'][$module][array_rand($_G['setting']['focus'][$module])];
		if(!empty($_G['cookie']['nofocus_'.$focusid])) {
			unset($_G['setting']['focus'][$module][$focusid]);
			$continue = 1;
		} else {
			$continue = 0;
		}
	} while(!empty($_G['setting']['focus'][$module]) && $continue);
	if(!$_G['setting']['focus'][$module]) {
		return null;
	}
	loadcache('focus');
	if(empty($_G['cache']['focus']['data']) || !is_array($_G['cache']['focus']['data'])) {
		return null;
	}
	return $focusid;
}

function check_seccode($value, $idhash) {
	global $_G;
	if(!$_G['setting']['seccodestatus']) {
		return true;
	}
	if(!isset($_G['cookie']['seccode'.$idhash])) {
		return false;
	}
	list($checkvalue, $checktime, $checkidhash, $checksid, $checkformhash) = explode("\t", authcode($_G['cookie']['seccode'.$idhash], 'DECODE', $_G['config']['security']['authkey']));
	return $checkvalue == strtoupper($value) && TIMESTAMP - 180 > $checktime && $checkidhash == $idhash && $checksid == $_G['sid'] && FORMHASH == $checkformhash;
}

function check_secqaa($value, $idhash) {
	global $_G;
	if(!$_G['setting']['secqaa']) {
		return true;
	}
	if(!isset($_G['cookie']['secqaa'.$idhash])) {
		return false;
	}
	loadcache('secqaa');
	list($checkvalue, $checktime, $checkidhash, $checksid, $checkformhash) = explode("\t", authcode($_G['cookie']['secqaa'.$idhash], 'DECODE', $_G['config']['security']['authkey']));
	return $checkvalue == md5($value) && TIMESTAMP - 180 > $checktime && $checkidhash == $idhash && $checksid == $_G['sid'] && FORMHASH == $checkformhash;
}

function adshow($parameter) {
	global $_G;
	$params = explode('/', $parameter);
	$customid = 0;
	$customc = explode('_', $params[0]);
	if($customc[0] == 'custom') {
		$params[0] = $customc[0];
		$customid = $customc[1];
	}
	if(empty($_G['setting']['advtype']) || !in_array($params[0], $_G['setting']['advtype'])) {
		return null;
	}
	loadcache('advs');
	$adids = array();
	$evalcode = &$_G['cache']['advs']['evalcode'][$params[0]];
	$parameters = &$_G['cache']['advs']['parameters'][$params[0]];
	$codes = &$_G['cache']['advs']['code'][$_G['basescript']][$params[0]];
	if(!empty($codes)) {
		foreach($codes as $adid => $code) {
			$parameter = &$parameters[$adid];
			$checked = true;
			@eval($evalcode['check']);
			if($checked) {
				$adids[] = $adid;
			}
		}
		if(!empty($adids)) {
			$adcode = $extra = '';
			@eval($evalcode['create']);
			return '<div'.($params[1] != '' ? ' class="'.$params[1].'"' : '').$extra.'>'.$adcode.'</div>';
		}
	}
	return null;
}

function adshowbytitle($title) {
	$query = DB::query("SELECT * FROM ".DB::table("common_advertisement")." ad WHERE ad.available=1 AND ad.title='".$title."'");
	$result = DB::fetch($query);
	if($result){
		return $result[code];
	}
	return null;
}



// 获得站点portal首页广告  [title]=>[code]
// 先从cache中获取，没有则从数据库中查询
function getads(){
	
	$allowmem = memory('check');
	$cache_key = 'portal_ads' ;
	
	if($allowmem){
		$cache = memory("get", $cache_key);
		if(!empty($cache)){
			//print_r('<br>ad from cache<br>');
			$result=unserialize($cache);
			return $result;
		}
	}
	
	//print_r('<br>ad from db<br>');
	$query = DB::query("SELECT ad.title , ad.code FROM ".DB::table("common_advertisement")." ad WHERE ad.available=1 ");
	
	while($row = DB::fetch($query)) {
		
		$result[$row[title]] = $row[code] ;// preg_replace("/\r\n|\n|\r/", '\n', addcslashes($row[code], "'\\")) ;
	}
	if($allowmem){
		//print_r("<br>ad set cache<br>");
		memory("set", $cache_key, serialize($result));
	}
	
	return $result;
}
// 刷新站点portal首页广告  [title]=>[code]  
// 先从cache中删除，再从数据库中查询放置到cache中
//  add by songsp  2011-3-17 16:53:46
function ads_flush(){
	
	$allowmem = memory('check');
	$cache_key = 'portal_ads' ;
	
	if($allowmem){
		memory("rm", $cache_key);
		
		
	$query = DB::query("SELECT ad.title , ad.code FROM ".DB::table("common_advertisement")." ad WHERE ad.available=1 ");
	
	while($row = DB::fetch($query)) {
		
		$result[$row[title]] = $row[code] ;// preg_replace("/\r\n|\n|\r/", '\n', addcslashes($row[code], "'\\")) ;
	}

	memory("set", $cache_key, serialize($result));
	
	
		
	}
	
}





function showmessage($message, $url_forward = '', $values = array(), $extraparam = array(), $custom = 0) {
	global $_G;

	$param = array(
		'header'	=> false,
		'timeout'	=> null,
		'refreshtime'	=> null,
		'closetime'	=> null,
		'locationtime'	=> null,
		'alert'		=> null,
		'return'	=> false,
		'redirectmsg'	=> 0,
		'msgtype'	=> 1,
		'showmsg'	=> true,
		'showdialog'	=> false,
		'login'		=> false,
		'handle'	=> false,
	);

	if($custom) {
		$alerttype = 'alert_info';
		$show_message = $message;
		include template('common/showmessage');
		dexit();
	}

	define('CACHE_FORBIDDEN', TRUE);
	$_G['setting']['msgforward'] = @unserialize($_G['setting']['msgforward']);
	$handlekey = '';

	if(empty($_G['inajax']) && (!empty($_G['gp_quickforward']) || $_G['setting']['msgforward']['quick'] && $_G['setting']['msgforward']['messages'] && @in_array($message, $_G['setting']['msgforward']['messages']))) {
		$param['header'] = true;
	}
	if(!empty($_G['inajax'])) {
		$handlekey = $_G['gp_handlekey'] = !empty($_G['gp_handlekey']) ? htmlspecialchars($_G['gp_handlekey']) : '';
		$param['handle'] = true;
	}
	if(!empty($_G['inajax'])) {
		$param['msgtype'] = empty($_G['gp_ajaxmenu']) && (empty($_POST) || !empty($_G['gp_nopost'])) ? 2 : 3;
	}
	if($url_forward) {
		$param['timeout'] = true;
		if($param['handle'] && !empty($_G['inajax'])) {
			$param['showmsg'] = false;
		}
	}

	foreach($extraparam as $k => $v) {
		$param[$k] = $v;
	}
	if(array_key_exists('set', $extraparam)) {
		$setdata = array('1' => array('msgtype' => 3));
		if($setdata[$extraparam['set']]) {
			foreach($setdata[$extraparam['set']] as $k => $v) {
				$param[$k] = $v;
			}
		}
	}

	if($param['timeout'] !== null) {
		$refreshtime = intval($param['refreshtime'] === null ? $_G['setting']['msgforward']['refreshtime'] : $param['refreshtime']);
		$refreshsecond = !empty($refreshtime) ? $refreshtime : 3;
		$refreshtime = $refreshsecond * 1000;
	} else {
		$refreshtime = $refreshsecond = 0;
	}

	if($param['login'] && $_G['uid'] || $url_forward) {
		$param['login'] = false;
	}

	$param['header'] = $url_forward && $param['header'] ? true : false;

	if($param['header']) {
		header("HTTP/1.1 301 Moved Permanently");
		dheader("location: ".str_replace('&amp;', '&', $url_forward));
	}

	$_G['hookscriptmessage'] = $message;
	$_G['hookscriptvalues'] = $values;
	$vars = explode(':', $message);
	if(count($vars) == 2) {
		$show_message = lang('plugin/'.$vars[0], $vars[1], $values);
	} else {
		$show_message = lang('message', $message, $values);
	}
	$show_jsmessage = str_replace("'", "\\\'", $show_message);
	if(!$param['showmsg']) {
		$show_message = '';
	}

	if($param['msgtype'] == 3) {
		$show_message = str_replace(lang('message', 'return_search'), lang('message', 'return_replace'), $show_message);
	}

	$allowreturn = !$param['timeout'] && stristr($show_message, lang('message', 'return')) || $param['return'] ? true : false;
	if($param['alert'] === null) {
		$alerttype = $url_forward ? (preg_match('/\_(succeed|success)$/', $message) ? 'alert_right' : 'alert_info') : ($allowreturn ? 'alert_error' : 'alert_info');
	} else {
		$alerttype = 'alert_'.$param['alert'];
	}

	$extra = '';
	if($param['handle']) {
		$valuesjs = $comma = $subjs = '';
		foreach($values as $k => $v) {
			if (is_array($v)) {
				$subcomma = '';
				foreach ($v as $subk=>$subv) {
					$subjs .= $subcomma.'\''.$subk.'\':\''.$subv.'\'';
					$subcomma = ',';
				}
				$valuesjs .= $comma.'\''.$k.'\':{'.$subjs.'}';
			} else {
				$valuesjs .= $comma.'\''.$k.'\':\''.$v.'\'';
			}
			$comma = ',';
		}
		$valuesjs = '{'.$valuesjs.'}';
		if($url_forward) {
			$extra .= 'if($(\'return_'.$handlekey.'\')) $(\'return_'.$handlekey.'\').className=\'onright\';if(typeof succeedhandle_'.$handlekey.'==\'function\') {succeedhandle_'.$handlekey.'(\''.$url_forward.'\', \''.$show_jsmessage.'\', '.$valuesjs.');}';
		} else {
			$extra .= 'if(typeof errorhandle_'.$handlekey.'==\'function\') {errorhandle_'.$handlekey.'(\''.$show_jsmessage.'\', '.$valuesjs.');}';
		}
	}

	if($handlekey) {
		if($param['showdialog']) {
			$st = $param['closetime'] !== null ? 'setTimeout("hideMenu(\'fwin_dialog\', \'dialog\')", '.($param['closetime'] * 1000).');' : '';
			$st .= $param['locationtime'] !== null ?'setTimeout("window.location.href =\''.$url_forward.'\';", '.($param['locationtime'] * 1000).');' : '';
			$extra .= 'hideWindow(\''.$handlekey.'\');showDialog(\''.$show_jsmessage.'\', \'notice\', null, '.($param['locationtime'] !== null ? 'function () { window.location.href =\''.$url_forward.'\'; }' : 'null').', 0);'.$st.'';
			$param['closetime'] = null;
		}
		if($param['closetime'] !== null) {
			$extra .= 'setTimeout("hideWindow(\''.$handlekey.'\')", '.($param['closetime'] * 1000).');';
		}
	}
	if(!$extra && $param['timeout']) {
		$extra .= 'setTimeout("window.location.href =\''.$url_forward.'\';", '.$refreshtime.');';
	}
	$show_message .= $extra ? '<script type="text/javascript" reload="1">'.$extra.'</script>' : '';

	include template('common/showmessage');
	dexit();
}

function showmessage_template($message, $url_forward = '', $values = array(), $extraparam = array(), $custom = 0, $template='common/showmessage') {
	global $_G;

	$param = array(
		'header'	=> false,
		'timeout'	=> null,
		'refreshtime'	=> null,
		'closetime'	=> null,
		'locationtime'	=> null,
		'alert'		=> null,
		'return'	=> false,
		'redirectmsg'	=> 0,
		'msgtype'	=> 1,
		'showmsg'	=> true,
		'showdialog'	=> false,
		'login'		=> false,
		'handle'	=> false,
	);

	if($custom) {
		$alerttype = 'alert_info';
		$show_message = $message;
		include template($template);
		dexit();
	}

	define('CACHE_FORBIDDEN', TRUE);
	$_G['setting']['msgforward'] = @unserialize($_G['setting']['msgforward']);
	$handlekey = '';

	if(empty($_G['inajax']) && (!empty($_G['gp_quickforward']) || $_G['setting']['msgforward']['quick'] && $_G['setting']['msgforward']['messages'] && @in_array($message, $_G['setting']['msgforward']['messages']))) {
		$param['header'] = true;
	}
	if(!empty($_G['inajax'])) {
		$handlekey = $_G['gp_handlekey'] = !empty($_G['gp_handlekey']) ? htmlspecialchars($_G['gp_handlekey']) : '';
		$param['handle'] = true;
	}
	if(!empty($_G['inajax'])) {
		$param['msgtype'] = empty($_G['gp_ajaxmenu']) && (empty($_POST) || !empty($_G['gp_nopost'])) ? 2 : 3;
	}
	if($url_forward) {
		$param['timeout'] = true;
		if($param['handle'] && !empty($_G['inajax'])) {
			$param['showmsg'] = false;
		}
	}

	foreach($extraparam as $k => $v) {
		$param[$k] = $v;
	}
	if(array_key_exists('set', $extraparam)) {
		$setdata = array('1' => array('msgtype' => 3));
		if($setdata[$extraparam['set']]) {
			foreach($setdata[$extraparam['set']] as $k => $v) {
				$param[$k] = $v;
			}
		}
	}

	if($param['timeout'] !== null) {
		$refreshtime = intval($param['refreshtime'] === null ? $_G['setting']['msgforward']['refreshtime'] : $param['refreshtime']);
		$refreshsecond = !empty($refreshtime) ? $refreshtime : 3;
		$refreshtime = $refreshsecond * 1000;
	} else {
		$refreshtime = $refreshsecond = 0;
	}

	if($param['login'] && $_G['uid'] || $url_forward) {
		$param['login'] = false;
	}

	$param['header'] = $url_forward && $param['header'] ? true : false;

	if($param['header']) {
		header("HTTP/1.1 301 Moved Permanently");
		dheader("location: ".str_replace('&amp;', '&', $url_forward));
	}

	$_G['hookscriptmessage'] = $message;
	$_G['hookscriptvalues'] = $values;
	$vars = explode(':', $message);
	if(count($vars) == 2) {
		$show_message = lang('plugin/'.$vars[0], $vars[1], $values);
	} else {
		$show_message = lang('message', $message, $values);
	}
	$show_jsmessage = str_replace("'", "\\\'", $show_message);
	if(!$param['showmsg']) {
		$show_message = '';
	}

	if($param['msgtype'] == 3) {
		$show_message = str_replace(lang('message', 'return_search'), lang('message', 'return_replace'), $show_message);
	}

	$allowreturn = !$param['timeout'] && stristr($show_message, lang('message', 'return')) || $param['return'] ? true : false;
	if($param['alert'] === null) {
		$alerttype = $url_forward ? (preg_match('/\_(succeed|success)$/', $message) ? 'alert_right' : 'alert_info') : ($allowreturn ? 'alert_error' : 'alert_info');
	} else {
		$alerttype = 'alert_'.$param['alert'];
	}

	$extra = '';
	if($param['handle']) {
		$valuesjs = $comma = $subjs = '';
		foreach($values as $k => $v) {
			if (is_array($v)) {
				$subcomma = '';
				foreach ($v as $subk=>$subv) {
					$subjs .= $subcomma.'\''.$subk.'\':\''.$subv.'\'';
					$subcomma = ',';
				}
				$valuesjs .= $comma.'\''.$k.'\':{'.$subjs.'}';
			} else {
				$valuesjs .= $comma.'\''.$k.'\':\''.$v.'\'';
			}
			$comma = ',';
		}
		$valuesjs = '{'.$valuesjs.'}';
		if($url_forward) {
			$extra .= 'if($(\'return_'.$handlekey.'\')) $(\'return_'.$handlekey.'\').className=\'onright\';if(typeof succeedhandle_'.$handlekey.'==\'function\') {succeedhandle_'.$handlekey.'(\''.$url_forward.'\', \''.$show_jsmessage.'\', '.$valuesjs.');}';
		} else {
			$extra .= 'if(typeof errorhandle_'.$handlekey.'==\'function\') {errorhandle_'.$handlekey.'(\''.$show_jsmessage.'\', '.$valuesjs.');}';
		}
	}

	if($handlekey) {
		if($param['showdialog']) {
			$st = $param['closetime'] !== null ? 'setTimeout("hideMenu(\'fwin_dialog\', \'dialog\')", '.($param['closetime'] * 1000).');' : '';
			$st .= $param['locationtime'] !== null ?'setTimeout("window.location.href =\''.$url_forward.'\';", '.($param['locationtime'] * 1000).');' : '';
			$extra .= 'hideWindow(\''.$handlekey.'\');showDialog(\''.$show_jsmessage.'\', \'notice\', null, '.($param['locationtime'] !== null ? 'function () { window.location.href =\''.$url_forward.'\'; }' : 'null').', 0);'.$st.'';
			$param['closetime'] = null;
		}
		if($param['closetime'] !== null) {
			$extra .= 'setTimeout("hideWindow(\''.$handlekey.'\')", '.($param['closetime'] * 1000).');';
		}
	}
	if(!$extra && $param['timeout']) {
		$extra .= 'setTimeout("window.location.href =\''.$url_forward.'\';", '.$refreshtime.');';
	}
	$show_message .= $extra ? '<script type="text/javascript" reload="1">'.$extra.'</script>' : '';

	include template($template);
	dexit();
}

function submitcheck($var, $allowget = 0, $seccodecheck = 0, $secqaacheck = 0) {
	if(!getgpc($var)) {
		return FALSE;
	} else {
		global $_G;
		if($allowget || ($_SERVER['REQUEST_METHOD'] == 'POST' && $_G['formhash'] == formhash() && empty($_SERVER['HTTP_X_FLASH_VERSION']) && (empty($_SERVER['HTTP_REFERER']) ||
		preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])))) {
			if(checkperm('seccode')) {
				if($secqaacheck && !check_secqaa($_G['gp_secanswer'], $_G['gp_sechash'])) {
					showmessage('submit_secqaa_invalid');
				}
				if($seccodecheck && !check_seccode($_G['gp_seccodeverify'], $_G['gp_sechash'])) {
					showmessage('submit_seccode_invalid');
				}
			}
			return TRUE;
		} else {
			showmessage('submit_invalid');
		}
	}
}

function multi($num, $perpage, $curpage, $mpurl, $maxpages = 0, $page = 10, $autogoto = FALSE, $simple = FALSE) {
	global $_G;
	$ajaxtarget = !empty($_G['gp_ajaxtarget']) ? " ajaxtarget=\"".htmlspecialchars($_G['gp_ajaxtarget'])."\" " : '';

	$a_name = '';
	if(strpos($mpurl, '#')) {
		$a_strs = explode('#', $mpurl);
		$mpurl = $a_strs[0];
		$a_name = '#'.$a_strs[1];
	}

	if(defined('IN_ADMINCP')) {
		$shownum = $showkbd = TRUE;
		$lang['prev'] = '&lsaquo;&lsaquo;';
		$lang['next'] = '&rsaquo;&rsaquo;';
	} else {
		$shownum = $showkbd = FALSE;
		$lang['prev'] = '&nbsp;&nbsp;';
		$lang['next'] = lang('core', 'nextpage');
	}

	$multipage = '';
	$mpurl .= strpos($mpurl, '?') ? '&amp;' : '?';

	$realpages = 1;
	$_G['page_next'] = 0;
	if($num > $perpage) {
		$offset = 2;

		$realpages = @ceil($num / $perpage);
		$pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;

		if($page > $pages) {
			$from = 1;
			$to = $pages;
		} else {
			$from = $curpage - $offset;
			$to = $from + $page - 1;
			if($from < 1) {
				$to = $curpage + 1 - $from;
				$from = 1;
				if($to - $from < $page) {
					$to = $page;
				}
			} elseif($to > $pages) {
				$from = $pages - $page + 1;
				$to = $pages;
			}
		}
		$_G['page_next'] = $to;

		$multipage = ($curpage - $offset > 1 && $pages > $page ? '<a href="'.$mpurl.'page=1'.$a_name.'" class="first"'.$ajaxtarget.'>1 ...</a>' : '').
		($curpage > 1 && !$simple ? '<a href="'.$mpurl.'page='.($curpage - 1).$a_name.'" class="prev"'.$ajaxtarget.'>'.$lang['prev'].'</a>' : '');
		for($i = $from; $i <= $to; $i++) {
			$multipage .= $i == $curpage ? '<strong>'.$i.'</strong>' :
			'<a href="'.$mpurl.'page='.$i.($ajaxtarget && $i == $pages && $autogoto ? '#' : $a_name).'"'.$ajaxtarget.'>'.$i.'</a>';
		}

		$multipage .= ($to < $pages ? '<a href="'.$mpurl.'page='.$pages.$a_name.'" class="last"'.$ajaxtarget.'>... '.$realpages.'</a>' : '').
		($curpage < $pages && !$simple ? '<a href="'.$mpurl.'page='.($curpage + 1).$a_name.'" class="nxt"'.$ajaxtarget.'>'.$lang['next'].'</a>' : '').
		($showkbd && !$simple && $pages > $page && !$ajaxtarget ? '<kbd><input type="text" name="custompage" size="3" onkeydown="if(event.keyCode==13) {window.location=\''.$mpurl.'page=\'+this.value; doane(event);}" /></kbd>' : '');

		$multipage = $multipage ? '<div class="pg">'.($shownum && !$simple ? '<em>&nbsp;'.$num.'&nbsp;</em>' : '').$multipage.'</div>' : '';
	}
	$maxpage = $realpages;
	return $multipage;
}

function simplepage($num, $perpage, $curpage, $mpurl) {
	$return = '';
	$lang['next'] = lang('core', 'nextpage');
	$lang['prev'] = lang('core', 'prevpage');
	$next = $num == $perpage ? '<a class="nxt" href="'.$mpurl.'&amp;page='.($curpage + 1).'">'.$lang['next'].'</a>' : '';
	$prev = $curpage > 1 ? '<span class="pgb"><a href="'.$mpurl.'&amp;page='.($curpage - 1).'">'.$lang['prev'].'</a></span>' : '';
	if($next || $prev) {
		$return = '<div class="pg">'.$prev.$next.'</div>';
	}
	return $return;
}
function censor($message, $modword = NULL) {
	if(!class_exists('discuz_censor')) {
		include libfile('class/censor');
	}
	$censor = discuz_censor::instance();
	$censor->check($message, $modword);
	if($censor->modbanned()) {
		showmessage('word_banned');
	}
	return $message;
}

function space_merge(&$values, $tablename) {
	global $_G;

	$uid = empty($values['uid'])?$_G['uid']:$values['uid'];
	$var = "member_{$uid}_{$tablename}";
	if($uid) {
		if(!isset($_G[$var])) {
			
			// modify  by songsp   2011-3-25 13:22:33
			$tmp = $_G['myself']['common_member_'.$tablename][$uid];
			if(!isset($tmp)){
				$query = DB::query("SELECT * FROM ".DB::table('common_member_'.$tablename)." WHERE uid='$uid'");
				$tmp = DB::fetch($query);
				$_G['myself']['common_member_'.$tablename][$uid] = $tmp;
			}
			$_G[$var] = $tmp;
			if( $_G[$var] ) {
				if($tablename == 'field_home') {
					$_G['setting']['privacy'] = empty($_G['setting']['privacy']) ? array() : (is_array($_G['setting']['privacy']) ? $_G['setting']['privacy'] : unserialize($_G['setting']['privacy']));
					$_G[$var]['privacy'] = empty($_G[$var]['privacy'])? array() : is_array($_G[$var]['privacy']) ? $_G[$var]['privacy'] : unserialize($_G[$var]['privacy']);
					foreach (array('feed','view','profile') as $pkey) {
						if(empty($_G[$var]['privacy'][$pkey])) {
							$_G[$var]['privacy'][$pkey] = isset($_G['setting']['privacy'][$pkey]) ? $_G['setting']['privacy'][$pkey] : array();
						}
					}
					$_G[$var]['acceptemail'] = empty($_G[$var]['acceptemail'])? array() : unserialize($_G[$var]['acceptemail']);
					if(empty($_G[$var]['acceptemail'])) {
						$_G[$var]['acceptemail'] = empty($_G['setting']['acceptemail'])?array():unserialize($_G['setting']['acceptemail']);
					}
				}
			} else {
				DB::insert('common_member_'.$tablename, array('uid'=>$uid));
				$_G[$var] = array();
			}
		}
		$values = array_merge($values, $_G[$var]);
	}
}

function runlog($file, $message, $halt=0) {
	global $_G;

	$nowurl = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
	$log = dgmdate($_G['timestamp'], 'Y-m-d H:i:s')."\t".$_G['clientip']."\t$_G[uid]\t{$nowurl}\t".str_replace(array("\r", "\n"), array(' ', ' '), trim($message))."\n";
	$yearmonth = dgmdate($_G['timestamp'], 'Ym');
	$logdir = DISCUZ_ROOT.'./data/log/';
	if(!is_dir($logdir)) mkdir($logdir, 0777);
	$logfile = $logdir.$yearmonth.'_'.$file.'.php';
	if(@filesize($logfile) > 2048000) {
		$dir = opendir($logdir);
		$length = strlen($file);
		$maxid = $id = 0;
		while($entry = readdir($dir)) {
			if(strexists($entry, $yearmonth.'_'.$file)) {
				$id = intval(substr($entry, $length + 8, -4));
				$id > $maxid && $maxid = $id;
			}
		}
		closedir($dir);
		$logfilebak = $logdir.$yearmonth.'_'.$file.'_'.($maxid + 1).'.php';
		@rename($logfile, $logfilebak);
	}
	if($fp = @fopen($logfile, 'a')) {
		@flock($fp, 2);
		fwrite($fp, "<?PHP exit;?>\t".str_replace(array('<?', '?>', "\r", "\n"), '', $log)."\n");
		fclose($fp);
	}
	if($halt) exit();
}

function stripsearchkey($string) {
	$string = trim($string);
	$string = str_replace('*', '%', addcslashes($string, '%_'));
	$string = str_replace('_', '\_', $string);
	return $string;
}

function dmkdir($dir, $mode = 0777){
	if(!is_dir($dir)) {
		dmkdir(dirname($dir));
		@mkdir($dir, $mode);
		@touch($dir.'/index.html'); @chmod($dir.'/index.html', 0777);
	}
	return true;
}

function dreferer($default = '') {
	global $_G;

	$default = empty($default) ? $GLOBALS['_t_curapp'] : '';
	if(empty($_G['referer'])) {
		$referer = !empty($_G['gp_referer']) ? $_G['gp_referer'] : $_SERVER['HTTP_REFERER'];
		$_G['referer'] = preg_replace("/([\?&])((sid\=[a-z0-9]{6})(&|$))/i", '\\1', $referer);
		$_G['referer'] = substr($_G['referer'], -1) == '?' ? substr($_G['referer'], 0, -1) : $_G['referer'];
	} else {
		$_G['referer'] = htmlspecialchars($_G['referer']);
	}

	if(strpos($_G['referer'], 'member.php?mod=logging')) {
		$_G['referer'] = $default;
	}
	return strip_tags($_G['referer']);
}

function ftpcmd($cmd, $arg1 = '') {
	static $ftp;
	$ftpon = getglobal('setting/ftp/on');
	if(!$ftpon) {
		return $cmd == 'error' ? -101 : 0;
	} elseif($ftp == null) {
		require_once libfile('class/ftp');
		$ftp = & discuz_ftp::instance();
	}
	if(!$ftp->enabled) {
		return 0;
	} elseif($ftp->enabled && !$ftp->connectid) {
		$ftp->connect();
	}
	switch ($cmd) {
		case 'upload' : return $ftp->upload(getglobal('setting/attachdir').'/'.$arg1, $arg1); break;
		case 'delete' : return $ftp->ftp_delete($arg1); break;
		case 'close'  : return $ftp->ftp_close(); break;
		case 'error'  : return $ftp->error(); break;
		case 'object' : return $ftp; break;
		default       : return false;
	}

}

function upload_icon_banner(&$data, $file, $type, $noresize=false) {
	global $_G;
	$data['extid'] = empty($data['extid']) ? $data['fid'] : $data['extid'];
	if(empty($data['extid'])) return '';

	if($data['status'] == 3 && $_G['setting']['group_imgsizelimit']) {
		$file['size'] > ($_G['setting']['group_imgsizelimit'] * 1024) && showmessage('file_size_overflow', '', array('size' => $_G['setting']['group_imgsizelimit'] * 1024));
	}
	require_once libfile('class/upload');
	$upload = new discuz_upload();
	$uploadtype = $data['status'] == 3 ? 'group' : 'common';

	if(!$upload->init($file, $uploadtype, $data['extid'], $type)) {
		return false;
	}

	if(!$upload->save()) {
		if(!defined('IN_ADMINCP')) {
			showmessage($upload->errormessage());
		} else {
			cpmsg($upload->errormessage(), '', 'error');
		}
	}
	if(($data['status'] == 3 || $type == 'banner') && !$noresize) {
		$imgwh = array('icon' => array('48', '48'), 'banner' => array('644', '150'));
		require_once libfile('class/image');
		$img = new image;
		$img->Thumb($upload->attach['target'], './'.$uploadtype.'/'.$upload->attach['attachment'], $imgwh[$type][0], $imgwh[$type][1], 'fixwr');
	}
	return $upload->attach['attachment'];
}

function diconv($str, $in_charset, $out_charset = CHARSET, $ForceTable = FALSE) {
	global $_G;

	$in_charset = strtoupper($in_charset);
	$out_charset = strtoupper($out_charset);
	if($in_charset != $out_charset) {
		require_once libfile('class/chinese');
		$chinese = new Chinese($in_charset, $out_charset, $ForceTable);
		return $chinese->Convert($str);
	} else {
		return $str;
	}
}

function renum($array) {
	$newnums = $nums = array();
	foreach ($array as $id => $num) {
		$newnums[$num][] = $id;
		$nums[$num] = $num;
	}
	return array($nums, $newnums);
}

function getonlinenum($fid = 0, $tid = 0, $apptypeid = APPTYPEID) {
	global $_G;

	$sql = "action='$apptypeid'";
	if($fid) {
		$sql .= " AND fid='$fid'";
	}
	if($tid) {
		$sql .= " AND tid='$tid'";
	}
	return DB::result_first('SELECT count(*) FROM '.DB::table("common_session")." WHERE $sql");
}

function sizecount($size) {
	if($size >= 1073741824) {
		$size = round($size / 1073741824 * 100) / 100 . ' GB';
	} elseif($size >= 1048576) {
		$size = round($size / 1048576 * 100) / 100 . ' MB';
	} elseif($size >= 1024) {
		$size = round($size / 1024 * 100) / 100 . ' KB';
	} else {
		$size = $size . ' Bytes';
	}
	return $size;
}

function swapclass($class1, $class2 = '') {
	static $swapc = null;
	$swapc = isset($swapc) && $swapc != $class1 ? $class1 : $class2;
	return $swapc;
}

function writelog($file, $log) {
	global $_G;
	$yearmonth = dgmdate(TIMESTAMP, 'Ym', $_G['setting']['timeoffset']);
	$logdir = DISCUZ_ROOT.'./data/log/';
	$logfile = $logdir.$yearmonth.'_'.$file.'.php';
	if(@filesize($logfile) > 2048000) {
		$dir = opendir($logdir);
		$length = strlen($file);
		$maxid = $id = 0;
		while($entry = readdir($dir)) {
			if(strexists($entry, $yearmonth.'_'.$file)) {
				$id = intval(substr($entry, $length + 8, -4));
				$id > $maxid && $maxid = $id;
			}
		}
		closedir($dir);

		$logfilebak = $logdir.$yearmonth.'_'.$file.'_'.($maxid + 1).'.php';
		@rename($logfile, $logfilebak);
	}
	if($fp = @fopen($logfile, 'a')) {
		@flock($fp, 2);
		$log = is_array($log) ? $log : array($log);
		foreach($log as $tmp) {
			fwrite($fp, "<?PHP exit;?>\t".str_replace(array('<?', '?>'), '', $tmp)."\n");
		}
		fclose($fp);
	}
}
function getcolorpalette($colorid, $id, $background) {
	return "<input id=\"c$colorid\" onclick=\"c{$colorid}_frame.location='static/image/admincp/getcolor.htm?c{$colorid}|{$id}';showMenu({'ctrlid':'c$colorid'})\" type=\"button\" class=\"colorwd\" value=\"\" style=\"background: $background\"><span id=\"c{$colorid}_menu\" style=\"display: none\"><iframe name=\"c{$colorid}_frame\" src=\"\" frameborder=\"0\" width=\"166\" height=\"186\" scrolling=\"no\"></iframe></span>";
}

function notification_add($touid, $type, $note, $notevars = array(), $system = 0, $isOfficial=0,$from_id=0){
	global   $_G;

	//首先对消息进行合并
	$notevars['actor'] = "<a href=\"home.php?mod=space&uid=$_G[uid]\">".user_get_user_name($_G[uid])."</a>";
	$notestring = lang('notification', $note, $notevars);
	if($notevars[logo]){
		$extra[logo] = $notevars[logo];
	}
	if($system===1){
		$uid = 0;
		$username = "";
	}else{
		$uid = $_G[uid];
		$username = user_get_user_name($_G[uid]);
	}
	//之后发送
	require_once(dirname(dirname(__FILE__)) . "/api/lt_msg/lt_msg_api.php");
	$msg = new msg_lt();
	return $msg->sendMsg("notice" , $uid ,$username, $touid  ,"personal" ,
	$notestring ,$type ,$isOfficial ,json_encode($extra) , "ESNSQ" ,0,$from_id);/*
	//    notification_add_old($touid, $type, $note, $notevars, $system);*/
}

function notification_add_old($touid, $type, $note, $notevars = array(), $system = 0) {
	global $_G;

	$tospace = array('uid'=>$touid);
	space_merge($tospace, 'field_home');
	$filter = empty($tospace['privacy']['filter_note'])?array():array_keys($tospace['privacy']['filter_note']);

	if($filter && (in_array($type.'|0', $filter) || in_array($type.'|'.$_G['uid'], $filter))) {
		return false;
	}

	$notevars['actor'] = "<a href=\"home.php?mod=space&uid=$_G[uid]\">".$_G['member']['username']."</a>";
	$notestring = lang('notification', $note, $notevars);

	$oldnote = array();
	if($notevars['from_id'] && $notevars['from_idtype']) {
		$oldnote = db::fetch_first("SELECT * FROM ".db::table('home_notification')."
			WHERE from_id='$notevars[from_id]' AND from_idtype='$notevars[from_idtype]'");
	}
	if(empty($oldnote['from_num'])) $oldnote['from_num'] = 0;

	$setarr = array(
		'uid' => $touid,
		'type' => $type,
		'new' => 1,
		'authorid' => $_G['uid'],
		'author' => $_G['username'],
		'note' => addslashes($notestring),
		'dateline' => $_G['timestamp'],
		'from_id' => $notevars['from_id'],
		'from_idtype' => $notevars['from_idtype'],
		'from_num' => ($oldnote['from_num']+1)
	);
	if($system) {
		$setarr['authorid'] = 0;
		$setarr['author'] = '';
	}

	if($oldnote['id']) {
		db::update('home_notification', $setarr, array('id'=>$oldnote['id']));
	} else {
		$oldnote['new'] = 0;
		DB::insert('home_notification', $setarr);
	}

	if(empty($oldnote['new'])) {
		DB::query("UPDATE ".DB::table('common_member_status')." SET notifications=notifications+1 WHERE uid='$touid'");
		DB::query("UPDATE ".DB::table('common_member')." SET newprompt=newprompt+1 WHERE uid='$touid'");

		require_once libfile('function/mail');
		$mail_subject = lang('notification', 'mail_to_user');
		sendmail_touser($touid, $mail_subject, $notestring, $type);
	}

	if(!$system && $_G['uid'] && $touid != $_G['uid']) {
		DB::query("UPDATE ".DB::table('home_friend')." SET num=num+1 WHERE uid='$_G[uid]' AND fuid='$touid'");
	}
}

function sendpm($toid, $subject, $message, $fromid = '') {
	global $_G;
	if($fromid === '') {
		$fromid = $_G['uid'];
	}
	loaducenter();
	uc_pm_send($fromid, $toid, $subject, $message);
}

function g_icon($groupid, $return = 0) {
	global $_G;
	if(empty($_G['cache']['usergroups'][$groupid]['icon'])) {
		$s =  '';
	} else {
		if(substr($_G['cache']['usergroups'][$groupid]['icon'], 0, 5) == 'http:') {
			$s = '<img src="'.$_G['cache']['usergroups'][$groupid]['icon'].'" align="absmiddle">';
		} else {
			$s = '<img src="'.$_G['setting']['attachurl'].'common/'.$_G['cache']['usergroups'][$groupid]['icon'].'" align="absmiddle">';
		}
	}
	if($return) {
		return $s;
	} else {
		echo $s;
	}
}
function updatediytemplate($targettplname = '') {
	global $_G;
	$r = false;
	$where = empty($targettplname) ? '' : " WHERE targettplname='$targettplname'";
	$query = DB::query("SELECT * FROM ".DB::table('common_diy_data')."$where");
	require_once libfile('function/portalcp');
	while($value = DB::fetch($query)) {
		$r = save_diy_data($value['primaltplname'], $value['targettplname'], unserialize($value['diycontent']));
	}
	return $r;
}

function space_key($uid, $appid=0) {
	global $_G;

	$siteuniqueid = DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='siteuniqueid'");
	return substr(md5($siteuniqueid.'|'.$uid.(empty($appid)?'':'|'.$appid)), 8, 16);
}


function getposttablebytid($tid) {
	global $_G;
	loadcache('threadtableids');
	$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
	if(!in_array(0, $threadtableids)) {
		$threadtableids = array_merge(array(0), $threadtableids);
	}
	foreach($threadtableids as $tableid) {
		$threadtable = $tableid ? "forum_thread_$tableid" : 'forum_thread';
		$posttableid = DB::result_first("SELECT posttableid FROM ".DB::table($threadtable)." WHERE tid='$tid'");
		if($posttableid !== false) {
			break;
		}
	}
	if(!$posttableid) {
		return 'forum_post';
	}
	return 'forum_post_'.$posttableid;
}

function getposttableid($type) {
	global $_G;
	loadcache('posttable_info');
	if($type == 'a') {
		$tabletype = 'addition';
	} else {
		$tabletype = 'primary';
	}
	if(!empty($_G['cache']['posttable_info'])) {
		foreach($_G['cache']['posttable_info'] as $key => $value) {
			if($value['type'] == $tabletype) {
				return $key;
			}
		}
	}
	return NULL;
}

function getposttable($type, $noprefix = true) {
	$tableid = getposttableid($type);
	if($type == 'a' && $tableid === NULL) {
		return NULL;
	}
	if($tableid) {
		$tablename = "forum_post_$tableid";
	} else {
		$tablename = 'forum_post';
	}

	if(!$noprefix) {
		$tablename = DB::table($tablename);
	}
	return $tablename;
}

function getcountofposts($from, $condition) {
	$ptable = getposttable('p');
	$atable = getposttable('a');

	$from_clause = str_replace(DB::table('forum_post'), DB::table($ptable), $from);
	$sum = DB::result_first("SELECT COUNT(*) FROM $from_clause WHERE $condition");
	if($atable) {
		$from_clause = str_replace(DB::table('forum_post'), DB::table($atable), $from);
		$sum += DB::result_first("SELECT COUNT(*) FROM $from_clause WHERE $condition");
	}
	return $sum;
}

function getfieldsofposts($field, $condition) {
	$ptable = getposttable('p');
	$atable = getposttable('a');

	$query = DB::query("SELECT $field FROM ".DB::table($ptable)." WHERE $condition");
	$result = array();
	while($post = DB::fetch($query)) {
		$result[] = $post;
	}
	if($atable) {
		$query = DB::query("SELECT $field FROM ".DB::table($atable)." WHERE $condition");
		while($post = DB::fetch($query)) {
			$result[] = $post;
		}
	}
	return $result;
}

function getallwithposts($sqlstruct, $onlyprimarytable = false) {
	$ptable = getposttable('p');
	$atable = getposttable('a');
	$result = array();

	$from_clause = str_replace(DB::table('forum_post'), DB::table($ptable), $sqlstruct['from']);
	$sql = "SELECT {$sqlstruct['select']} FROM $from_clause WHERE {$sqlstruct['where']}";
	$sqladd = '';
	if (!empty($sqlstruct['order'])) {
		$sqladd .= " ORDER BY {$sqlstruct['order']}";
	}
	if(!empty($sqlstruct['limit'])) {
		$sqladd .= " LIMIT {$sqlstruct['limit']}";
	}
	$sql = $sql . $sqladd;
	$query = DB::query($sql);
	while($row = DB::fetch($query)) {
		$result[] = $row;
	}

	if(!$onlyprimarytable && $atable !== NULL) {
		$from_clause = str_replace(DB::table('forum_post'), DB::table($atable), $sqlstruct['from']);
		$sql = "SELECT {$sqlstruct['select']} FROM $from_clause WHERE {$sqlstruct['where']}";
		$sql = $sql . $sqladd;

		$query = DB::query($sql);
		while($row = DB::fetch($query)) {
			$result[] = $row;
		}
	}
	return $result;
}

function insertpost($data) {
	if(isset($data['tid'])) {
		$tableid = DB::result_first("SELECT posttableid FROM ".DB::table('forum_thread')." WHERE tid='{$data['tid']}'");
	} else {
		$tableid = getposttableid('p');
		$data['tid'] = 0;
	}
	$pid = DB::insert('forum_post_tableid', array('pid' => null), true);

	if(!$tableid) {
		$tablename = 'forum_post';
	} else {
		$tablename = "forum_post_$tableid";
	}

	$data = array_merge($data, array('pid' => $pid));

	DB::insert($tablename, $data);
	if($pid % 1024 == 0) {
		DB::delete('forum_post_tableid', "pid<$pid");
	}
	save_syscache('max_post_id', $pid);
	return $pid;
}

function updatepost($data, $condition, $unbuffered = false) {
	global $_G;
	loadcache('posttableids');
	if(!empty($_G['cache']['posttableids'])) {
		$posttableids = $_G['cache']['posttableids'];
	} else {
		$posttableids = array('0');
	}
	foreach($posttableids as $id) {
		if($id == 0) {
			DB::update('forum_post', $data, $condition, $unbuffered);
		} else {
			DB::update("forum_post_$id", $data, $condition, $unbuffered);
		}
	}
}

function loadcacheobject($type) {
	static $object = null;

	global $_G;
	return $_G['ea'];
	$cachetype = in_array($type, array('file', 'memcache', 'sql')) ? $type : $_G['config']['cache']['type'];
	if($type && $type != $cachetype) {
		return;
	}
	if($cachetype == 'memcache') {
		$cachetype = $_G['config']['cache']['main']['type'];
	}
	if(!$cachetype) {
		return;
	}
	if(!isset($object[$cachetype])) {
		require_once libfile('cache/'.$cachetype, 'class');
		$object[$cachetype] = new ultrax_cache($_G['config']['cache']['main'][$cachetype]);
	}
	return $object[$cachetype];
}

function updategroupcreditlog($fid, $uid) {
	global $_G;
	if(empty($fid) || empty($uid)) {
		return false;
	}
	$today = date('Ymd', TIMESTAMP);
	$updategroupcredit = getcookie('updategroupcredit_'.$fid);
	if($updategroupcredit < $today) {
		$status = DB::result_first("SELECT logdate FROM ".DB::table('forum_groupcreditslog')." WHERE fid='$fid' AND uid='$uid' AND logdate='$today'");
		if(empty($status)) {
			DB::query("UPDATE ".DB::table('forum_forum')." SET commoncredits=commoncredits+1 WHERE fid='$fid'");
			DB::query("REPLACE INTO ".DB::table('forum_groupcreditslog')." (fid, uid, logdate) VALUES ('$fid', '$uid', '$today')");
			if(empty($_G['forum']) || empty($_G['forum']['level'])) {
				$forum = DB::fetch_first("SELECT name, level, commoncredits FROM ".DB::table('forum_forum')." WHERE fid='$fid'");
			} else {
				$_G['forum']['commoncredits'] ++;
				$forum = &$_G['forum'];
			}
			if(empty($_G['grouplevels'])) {
				loadcache('grouplevels');
			}
			$grouplevel = $_G['grouplevels'][$forum['level']];

			if($grouplevel['type'] == 'default' && !($forum['commoncredits'] >= $grouplevel['creditshigher'] && $forum['commoncredits'] < $grouplevel['creditslower'])) {
				$levelid = DB::result_first("SELECT levelid FROM ".DB::table('forum_grouplevel')." WHERE type='default' AND creditshigher<='$forum[commoncredits]' AND creditslower>'$forum[commoncredits]' LIMIT 1");
				if(!empty($levelid)) {
					DB::query("UPDATE ".DB::table('forum_forum')." SET level='$levelid' WHERE fid='$fid'");
					$groupfounderuid = DB::result_first("SELECT founderuid FROM ".DB::table('forum_forumfield')." WHERE fid='$fid' LIMIT 1");
					notification_add($groupfounderuid, 'system', 'grouplevel_update', array(
						'groupname' => '<a href="forum.php?mod=group&fid='.$fid.'">'.$forum['name'].'</a>',
						'newlevel' => $_G['grouplevels'][$levelid]['leveltitle']
					));
				}
			}
		}
		dsetcookie('updategroupcredit_'.$fid, $today, 86400);
	}
}

function memory($cmd, $key='', $value='', $ttl = 0) {
	$discuz = & discuz_core::instance();
	if($cmd == 'check') {
		return  $discuz->mem->enable ? $discuz->mem->type : '';
	} elseif($discuz->mem->enable && in_array($cmd, array('set', 'get', 'rm'))) {
		switch ($cmd) {
			case 'set': return $discuz->mem->set($key, $value, $ttl); break;
			case 'get': return $discuz->mem->get($key); break;
			case 'rm': return $discuz->mem->rm($key); break;
		}
	}
	return null;
}

function ipaccess($ip, $accesslist) {
	return preg_match("/^(".str_replace(array("\r\n", ' '), array('|', ''), preg_quote($accesslist, '/')).")/", $ip);
}

function ipbanned($onlineip) {
	global $_G;

	if($_G['setting']['ipaccess'] && !ipaccess($onlineip, $_G['setting']['ipaccess'])) {
		return TRUE;
	}

	loadcache('ipbanned');
	if(empty($_G['cache']['ipbanned'])) {
		return FALSE;
	} else {
		if($_G['cache']['ipbanned']['expiration'] < TIMESTAMP) {
			require_once libfile('function/cache');
			updatecache('ipbanned');
		}
		return preg_match("/^(".$_G['cache']['ipbanned']['regexp'].")$/", $onlineip);
	}
}

function getcount($tablename, $condition) {
	if(empty($condition)) {
		$where = '1';
	} elseif(is_array($condition)) {
		$where = DB::implode_field_value($condition, ' AND ');
	} else {
		$where = $condition;
	}
	$row = DB::fetch_first("SELECT COUNT(*) AS num FROM ".DB::table($tablename)." WHERE $where");
	return $row['num'];
}

function sysmessage($message) {
	require libfile('function/sysmessage');
	show_system_message($message);
}

function forumperm($permstr) {
	global $_G;

	$groupidarray = array($_G['groupid']);
	foreach(explode("\t", $_G['member']['extgroupids']) as $extgroupid) {
		if($extgroupid = intval(trim($extgroupid))) {
			$groupidarray[] = $extgroupid;
		}
	}
	return preg_match("/(^|\t)(".implode('|', $groupidarray).")(\t|$)/", $permstr);
}


if(!function_exists('file_put_contents')) {
	if(!defined('FILE_APPEND')) define('FILE_APPEND', 8);
	function file_put_contents($filename, $data, $flag = 0) {
		$return = false;
		if($fp = @fopen($filename, $flag != FILE_APPEND ? 'w' : 'a')) {
			if($flag == LOCK_EX) @flock($fp, LOCK_EX);
			$return = fwrite($fp, is_array($data) ? implode('', $data) : $data);
			fclose($fp);
		}
		return $return;
	}
}

function checkperm($perm) {
	global $_G;
	/*
	 * 如果是三级管理员返回true
	 * added by fumz，2010-10-9 17:12:55
	 */
	//begin
	if($_G['cookie']['validate_ismanager']){
		return true;
	}
	//echo "checkperm函数判断结果不是三级管理员";
	//end
	return (empty($_G['group'][$perm])?'':$_G['group'][$perm]);
}

function checkpermblog($perm) {
	global $_G;
	
	return (empty($_G['group'][$perm])?'':$_G['group'][$perm]);
}

function periodscheck($periods, $showmessage = 1) {
	global $_G;

	if(!$_G['group']['disableperiodctrl'] && $_G['setting'][$periods]) {
		$now = dgmdate(TIMESTAMP, 'G.i');
		foreach(explode("\r\n", str_replace(':', '.', $_G['setting'][$periods])) as $period) {
			list($periodbegin, $periodend) = explode('-', $period);
			if(($periodbegin > $periodend && ($now >= $periodbegin || $now < $periodend)) || ($periodbegin < $periodend && $now >= $periodbegin && $now < $periodend)) {
				$banperiods = str_replace("\r\n", ', ', $_G['setting'][$periods]);
				if($showmessage) {
					showmessage('period_nopermission', NULL, array('banperiods' => $banperiods), array('login' => 1));
				} else {
					return TRUE;
				}
			}
		}
	}
	return FALSE;
}

function cknewuser($return=0) {
	global $_G;

	$result = true;

	if(!$_G['uid']) return true;

	if(checkperm('disablepostctrl')) {
		return $result;
	}
	$ckuser = $_G['member'];

	if($_G['setting']['newbiespan'] && $_G['timestamp']-$ckuser['regdate']<$_G['setting']['newbiespan']*3600) {
		if(empty($return)) showmessage('no_privilege_newbiespan', '', array('newbiespan' => $_G['setting']['newbiespan']));
		$result = false;
	}
	if($_G['setting']['need_avatar'] && empty($ckuser['avatarstatus'])) {
		if(empty($return)) showmessage('no_privilege_avatar');
		$result = false;
	}
	if($_G['setting']['need_email'] && empty($ckuser['emailstatus'])) {
		if(empty($return)) showmessage('no_privilege_email');
		$result = false;
	}
	if($_G['setting']['need_friendnum']) {
		space_merge($ckuser, 'count');
		if($ckuser['friends'] < $_G['setting']['need_friendnum']) {
			if(empty($return)) showmessage('no_privilege_friendnum', '', array('friendnum' => $_G['setting']['need_friendnum']));
			$result = false;
		}
	}
	return $result;
}

function manyoulog($logtype, $uids, $action, $fid = '') {
	global $_G;

	$action = daddslashes($action);
	if($logtype == 'user') {
		$values = array();
		$uids = is_array($uids) ? $uids : array($uids);
		foreach($uids as $uid) {
			$uid = intval($uid);
			$values[$uid] = "('$uid', '$action', '".TIMESTAMP."')";
		}
		if($values) {
			DB::query("REPLACE INTO ".DB::table('common_member_log')." (`uid`, `action`, `dateline`) VALUES ".implode(',', $values));
		}
	}
}

function getpanelapp() {
	global $_G;

	$_G['my_panelapp'] = array();
	if($_G['uid'] && $_G['setting']['my_app_status']) {
		loadcache('myapp');
		$query = DB::query("SELECT appid, appname FROM ".DB::table('home_userapp')." WHERE uid='$_G[uid]' ORDER BY menuorder DESC");
		while($value = DB::fetch($query)) {
			$value['userpanelarea'] = intval($_G['cache']['myapp'][$value['appid']]['userpanelarea']);
			if($value['userpanelarea']) {

				$_G['my_panelapp'][$value['appid']] = $value;
			}
		}
	}
}



/*
 * add by songsp 2010-7-23 17:13:48
 * 判断是否是官方博客用户
 */
function checkIsOfficial($uid){
	//

	return  getOfficialBlogUid() == $uid;
}
//获得官方博客用户id
function getOfficialBlogUid(){
	return 1000;// 测试418841
}
//获得官方博客作为好友的信息
function getOfficialBlogAsFriend() {
	$obuid = getOfficialBlogUid();
	$query = DB::query("SELECT cm.*, cmp.realname FROM ".DB::table("common_member")." cm, ".DB::table("common_member_profile")." cmp WHERE cm.uid=cmp.uid AND cm.uid=".$obuid);
	return DB::fetch($query);
}

function plugin_check_category($plugin){
	return in_array($plugin, array("topic", "questionary", "activity", "notice", "poll", "qbar", "grouplive", "groupdoc", "resourcelist", "groupad","selection"));
}

function hook_create_resource($rid,$rtype,$fid){
	global $_G;
	require_once libfile("function/org");
	$arr["uid"] = $_G[uid];
	$arr["rid"] = $rid;
	$arr["rtype"] = $rtype;
	$arr["dateline"] = TIMESTAMP;
	if(is_numeric($fid)){
		$arr["fid"] = $fid;
	}
	$org_id =  get_org_id_by_user($_G[username]);
	if($org_id==-1){
		return false;
	}
	$arr["org_id"] = $org_id;
	$arr["company_id"]=9002;
	if($_G['cookie']['municipality']){//用户直属市公司
		$arr["company_id"]=$_G['cookie']['municipality'];
	}elseif($_G['cookie']['province']){//用户直属省公司
		$arr["company_id"]=$_G['cookie']['province'];
	}
	DB::insert("common_resource", $arr);
}

function hook_delete_resource($rid, $rtype){
	if($rid&&$rtype){
		DB::query("DELETE FROM ".DB::table("common_resource")." WHERE rid=".$rid." AND rtype='".$rtype."'");
	}
}
/**
 *fumz
 **/
function hook_delete_resources($ridarray,$rtype){
	if(!empty($ridarray)){
		DB::query("DELETE FROM pre_common_resource WHERE rid IN(".implode(',',$ridarray).") AND rtype like '$rtype'");
	}
}

//获取外网IP
function get_onlineip() {
	$onlineip = '';
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$onlineip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$onlineip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	return $onlineip;
}

function get_real_ip(){
	$ip=false;
	if(!empty($_SERVER["HTTP_CLIENT_IP"])){
		$ip = $_SERVER["HTTP_CLIENT_IP"];
	}
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
		if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
		for ($i = 0; $i < count($ips); $i++) {
			if (!eregi ("^(10│172.16│192.168).", $ips[$i])) {
				$ip = $ips[$i];
				break;
			}
		}
	}
	return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}
/*
 * added by fumz,用户公司信息
 * $uid用户登录名
 */
function get_user_corporationname_by_uid($uid){
	if($uid){
		require_once libfile('function/org'); //路径按照实际情况修改 
		
		$org_id = getUserGroupByUid("$uid"); // 根据用户的id获取该用户组织信息 
		//echo("uid:".$uid);
		//echo("org_id:".$org_id);
		$company_id =array_pop(getParentGroupById($org_id));
		
		//echo("company_id:".$company_id);
		$provinceArray=getOrgById($company_id); 
		//print_r($provinceArray);
		$user_org_name=$provinceArray[0]['name'];
		return $user_org_name;
/*		require_once libfile('function/org');
		require_once dirname(dirname(__FILE__))."/api/lt_org/user.php";
		$user=new User();
		$org_id=$user->getGroupidByUserid($uid);
		//$org_id = getUserGroupByUid($uid);
		echo("org_id:".$org_id);
		unset($user);
		$company_id = getParentGroupById($org_id);
		$provinceArray=getOrgById($company_id[1]);
		$user_org_name=$provinceArray[0]['name'];
		$provinceArray=getOrgById($company_id[2]);
		$user_org_name.=$provinceArray[0]['name'];
		return $user_org_name;*/
		
	}
}

function addblogs($uid){
	DB::query("update ".DB::TABLE("common_member_status")." set blogs=blogs+1 where uid=".$uid);
}

function delblogs($uid){
	DB::query("update ".DB::TABLE("common_member_status")." set blogs=blogs-1 where uid=".$uid);
}

//上传xls文件的方法
function upload_xls($file,$filepath,$filemaxsize,$accept_overwrite = 1){
	global $_G;
	$upload_file=$file['tmp_name'];  //文件被上传后在服务端储存的临时文件名 
	$upload_file_name=$file['name']; //文件名 
	$filepath=DISCUZ_ROOT.$filepath;
	$upload_file_size=$file['size'];  //文件大小 
	//print_r($_FILES['upload']);//此句可以输出上传文件的全部信息 
    if($upload_file) 
		{ 
            //检查文件内型
            preg_match('|\.(\w+)$|', $upload_file_name, $ext);
			$ext = strtolower($ext[1]);
   			if($ext != "xls") {
				if(!defined('IN_ADMINCP')) {
					showmessage("上传的文件类型型错误，请重新上传！请返回");
				} else {
					cpmsg("上传的文件类型型错误，请重新上传！", '', 'error');
				}
     		}

     		//检查文件大小 
   			if($upload_file_size > $filemaxsize) {
				if(!defined('IN_ADMINCP')) {
					showmessage("上传的文件超过2M，请重新上传！请返回");
				} else {
					cpmsg("上传的文件超过2M，请重新上传！", '', 'error');
				}
   					
   			}
          	//检查存储目录，如果存储目录不存在，则创建之 
          	if(!is_dir($filepath)) 
            		mkdir($filepath); 
   			//检查读写文件 
  			if(file_exists($filepath.'/'.$upload_file_name) && $accept_overwrite){
				if(!defined('IN_ADMINCP')) {
					showmessage("以存在相同名称的文件！请返回");
				} else {
					cpmsg("以存在相同名称的文件！", '', 'error');
				}
			}
  					

   			//复制文件到指定目录 
   			//$new_file_name=$_G[fid].".xls";//上传过后的保存的名称
   			if(!move_uploaded_file($upload_file,$filepath.'/'.$upload_file_name)) {
				if(!defined('IN_ADMINCP')) {
					showmessage("复制文件失败！请返回");
				} else {
					cpmsg("复制文件失败！", '', 'error');
				}
			}
   					

   			//文件的MIME类型为：$_FILES['upload']['type'](文件的 MIME 类型，需要浏览器提供该信息的支持，例如“image/gif”)
   			//文件上传后被临时储存为：$_FILES['upload']['tmp_name'](文件被上传后在服务端储存的临时文件名) 
   			$info="你上传了文件:".$upload_file_name.";文件大小："	.$upload_file_size."B。<br/>"	;
   			//文件检查
   			$error=$file['error']; 
			
			return $error;
		} 	
}

function parseat($message,$fromuid=0,$sendnotice=0 ){
	global $_G;
	$message=str_replace('&nbsp;',' ',$message);
	$message=daddslashes($message);
	$mesarray=explode(' ',$message);
	for($i=0;$i<count($mesarray);$i++){
		if(strripos($mesarray[$i],'@')===0||strripos($mesarray[$i],'@')){
			$uid=DB::result_first("select profile.uid from ".DB::TABLE('home_friend')." main INNER JOIN ".DB::TABLE("common_member_profile")." profile ON profile.uid=main.fuid where main.uid=".$fromuid." and profile.realname='".substr($mesarray[$i],strripos($mesarray[$i],'@')+1)."'");
			$fid=DB::result_first("select ff.fid from ".DB::TABLE("forum_forum")." ff,".DB::TABLE("forum_forumfield")." fff where ff.name='".substr($mesarray[$i],strripos($mesarray[$i],'@')+1)."' and ff.type='sub' and fff.gviewperm=1");
			if($uid){
				$replace='<a id='.$uid.' href="home.php?mod=space&uid='.$uid.'" target="_blank" class="perPanel xw1 xi2" >'.strrchr($mesarray[$i],"@").'</a>';
				$mesarray[$i]=substr_replace($mesarray[$i],$replace,strripos($mesarray[$i],'@'));
				if($sendnotice){
				}
				$uids[]=$uid;
			}
			if($fid){
				$replace='<a href="forum.php?mod=group&fid='.$fid.'" target="_blank" class="perPanel xw1 xi2" >'.strrchr($mesarray[$i],"@").'</a>';
				$mesarray[$i]=substr_replace($mesarray[$i],$replace,strripos($mesarray[$i],'@'));
				$fids[]=$fid;
			}
		}
		$message=implode(' ',$mesarray);
	}
	$mesarray=explode(':',$message);
	for($i=0;$i<count($mesarray);$i++){
		if(strripos($mesarray[$i],'@')===0||strripos($mesarray[$i],'@')){
			$uid=DB::result_first("select profile.uid from ".DB::TABLE('home_friend')." main INNER JOIN ".DB::TABLE("common_member_profile")." profile ON profile.uid=main.fuid where main.uid=".$fromuid." and profile.realname='".substr($mesarray[$i],strripos($mesarray[$i],'@')+1)."'");
			$fid=DB::result_first("select ff.fid from ".DB::TABLE("forum_forum")." ff,".DB::TABLE("forum_forumfield")." fff where ff.name='".substr($mesarray[$i],strripos($mesarray[$i],'@')+1)."' and ff.type='sub' and fff.gviewperm=1");
			if($uid){
				$replace='<a id='.$uid.' href="home.php?mod=space&uid='.$uid.'" target="_blank" class="perPanel xw1 xi2" >'.strrchr($mesarray[$i],"@").'</a>';
				$mesarray[$i]=substr_replace($mesarray[$i],$replace,strripos($mesarray[$i],'@'));
				if($sendnotice){
				}
				$uids[]=$uid;
			}
			if($fid){
				$replace='<a href="forum.php?mod=group&fid='.$fid.'" target="_blank" class="perPanel xw1 xi2" >'.strrchr($mesarray[$i],"@").'</a>';
				$mesarray[$i]=substr_replace($mesarray[$i],$replace,strripos($mesarray[$i],'@'));
				$fids[]=$fid;
			}
		}
	}
	$message=implode(':',$mesarray);
	
	$message=stripslashes($message);
	$uids=array_unique($uids);
	$fids=array_unique($fids);
	
	$res[atuids]=$uids;
	$res[atfids]=$fids;
	$res[message]=$message;

	return $res;
}

function parseat1($message,$uid,$jasonvalue){
	global $_G;
	
	$jasonvalue=stripslashes($jasonvalue);
	$atarray=json_decode($jasonvalue);
	foreach($atarray as $value){
		if($value->note && $value->note!="undefined"){
			$name='@'.$value->note;
		}else{
			$name='@'.$value->name;
		}
		if($value->id){
			if($value->type=='group'){
				$gviewperm=DB::result_first("SELECT gviewperm from ".DB::TABLE("forum_forumfield")." where fid='".$value->id."'");
				if($gviewperm){
					$replace[$value->id.$value->name.$value->type]='<a id='.$value->id.' href="forum.php?mod=group&fid='.$value->id.'" target="_blank" class="perPanel xw1 xi2" >'.$name.'</a>';
				}else{
					$replace[$value->id.$value->name.$value->type]=$name;
				}
				$fids[]=$value->id;
			}elseif($value->type=='user'){
				$replace[$value->id.$value->name.$value->type]='<a id='.$value->id.' href="home.php?mod=space&uid='.$value->id.'" target="_blank" class="perPanel xw1 xi2" >'.'@'.$value->name.'</a>';
				$uids[]=$value->id;
			}else{
			}
			$message=preg_replace('/'.$name.'/','α'.$value->id.$value->name.$value->type,$message,'1');
			$repl[$value->id.$value->name.$value->type]='α'.$value->id.$value->name.$value->type;
		}else{
			if($value->type=='group'){
				$fid=DB::result_first("SELECT ff.fid as fid from ".DB::TABLE('forum_forum')." ff,".DB::TABLE("forum_groupuser")." fg,".DB::TABLE("forum_forumfield")." fff where ff.fid=fg.fid and ff.fid=fff.fid and fff.gviewperm=1 and fg.uid=$uid and  ff.type='sub' and ff.name ='".$value->name."'");
				$replace[$value->id.$value->name.$value->type]='<a id='.$fid.' href="forum.php?mod=group&fid='.$fid.'" target="_blank" class="perPanel xw1 xi2" >'.$name.'</a>';
				$fids[]=$fid;
			}elseif($value->type=='user'){
				$query=DB::query("SELECT main.fuid AS uid,realname FROM ".DB::table('home_friend')." main INNER JOIN ".DB::table('common_member_profile')." profile ON profile.uid=main.fuid WHERE main.uid='".$uid."' and ( profile.realname ='".$value->name."' or  main.note ='".$value->name."') ");
				while($uservalue=DB::fetch($query)){
					$replace[$value->id.$value->name.$value->type]=$replace[$value->id.$value->name.$value->type].'<a id='.$uservalue[uid].' href="home.php?mod=space&uid='.$uservalue[uid].'" target="_blank" class="perPanel xw1 xi2" >'.'@'.$uservalue[realname].'</a> ';
					$uids[]=$uservalue[uid];
				}
				
			}else{
				$query=DB::query("SELECT main.fuid AS uid,realname FROM ".DB::table('home_friend')." main INNER JOIN ".DB::table('common_member_profile')." profile ON profile.uid=main.fuid WHERE main.uid='".$uid."' and ( profile.realname ='".$value->name."' or  main.note ='".$value->name."') ");
				while($uservalue=DB::fetch($query)){
					$replace[$value->id.$value->name.$value->type]=$replace[$value->id.$value->name.$value->type].'<a id='.$uservalue[uid].' href="home.php?mod=space&uid='.$uservalue[uid].'" target="_blank" class="perPanel xw1 xi2" >'.'@'.$uservalue[realname].'</a> ';
					$uids[]=$uservalue[uid];
				}
				
				$fid=DB::result_first("SELECT ff.fid as fid from ".DB::TABLE('forum_forum')." ff,".DB::TABLE("forum_groupuser")." fg,".DB::TABLE("forum_forumfield")." fff where ff.fid=fg.fid and ff.fid=fff.fid and  fff.gviewperm=1 and fg.uid=$uid and  ff.type='sub' and ff.name ='".$value->name."'");
				if($fid){
					$replace[$value->id.$value->name.$value->type]=$replace[$value->id.$value->name.$value->type].'<a id='.$fid.' href="forum.php?mod=group&fid='.$fid.'" target="_blank" class="perPanel xw1 xi2" >'.$name.'</a>';
					$fids[]=$fid;
				}
			}
			if($replace[$value->id.$value->name.$value->type]){
				$message=preg_replace('/'.$name.'/','α'.$value->id.$value->name.$value->type,$message,'1');
			}
			$repl[$value->id.$value->name.$value->type]='α'.$value->id.$value->name.$value->type;
		}
	}
	foreach($repl as $key=>$value){
		if($replace[$key]){
			$message=preg_replace('/'.$value.'/',$replace[$key],$message);
		}
	}

	$message=stripslashes($message);
	$uids=array_unique($uids);
	$fids=array_unique($fids);
	
	$res[atuids]=$uids;
	$res[atfids]=$fids;
	$res[message]=$message;
	
	
	return $res;
}


function parsetag($title,$message,$type,$contentid,$uid){
	global $_G;
	preg_match_all('/#[0-9a-zA-Z\x{4e00}-\x{9fa5}]+#/u',$title,$matchestitle);
	preg_match_all('/#[0-9a-zA-Z\x{4e00}-\x{9fa5}]+#/u',$message,$matches);
		
	$uid=empty($uid)?$_G[uid]:$uid;
	
	if(empty($uid)){
		showmessage("用户id不能为空！");
	}	
	
	$allmatches=array_merge($matchestitle[0],$matches[0]);
	$tagarray=array_unique($allmatches);
	for($i=0;$i<count($tagarray);$i++){
		$tag=str_replace('#','',$tagarray[$i]);
		$gbk_tag = iconv('UTF-8','GBK',$tag);
		if(strlen($gbk_tag)<=20){
			$tagvalue=DB::fetch_first("select * from ".DB::TABLE("home_tag")." where tagname='".$tag."'");
			if($tagvalue[id]){
				//解析start			
				if(time()-7*24*3600>$tagvalue['weekdate']){
					$tagvalue['weekdate']=time();
					$tagvalue['weekcc']=1;
				}else{
					$tagvalue['weekcc']=$tagvalue['weekcc']+1;
				}
				if(time()-30*24*3600>$tagvalue['monthdate']){
					$tagvalue['monthdate']=time();
					$tagvalue['monthcc']=1;
				}else{
					$tagvalue['monthcc']=$tagvalue['monthcc']+1;
				}
				if(time()-90*24*3600>$tagvalue['seasondate']){
					$tagvalue['seasondate']=time();
					$tagvalue['seasoncc']=1;
				}else{
					$tagvalue['seasoncc']=$tagvalue['seasoncc']+1;
				}
				if(time()-365*24*3600>$tagvalue['yeardate']){
					$tagvalue['yeardate']=time();
					$tagvalue['yearcc']=1;
				}else{
					$tagvalue['yearcc']=$tagvalue['yearcc']+1;
				}
				$tagvalue['content']=$tagvalue['content']+1;
				$tagvalue['updateline']=time();
				//end
				DB::update('home_tag',$tagvalue,array('id'=>$tagvalue[id]));
				DB::insert("home_tagrelation",array(
					"contentid"=>$contentid,
					"contenttype"=>$type,
					"dateline"=>time(),
					"tagid"=>$tagvalue[id],
					"tagname"=>$tag
					));
				$tagarr[$tagvalue[id]][id]=$tagvalue[id];
				$tagarr[$tagvalue[id]][name]=$tag;
			}else{
				DB::insert("home_tag", array(
						"tagname"=>$tag,
						"content"=>1,
						"weekcc"=>1,
						"monthcc"=>1,
						"seasoncc"=>1,
						"yearcc"=>1,
						"dateline"=>time(),
						"updateline"=>time(),
						"weekdate"=>time(),
						"monthdate"=>time(),
						"seasondate"=>time(),
						"yeardate"=>time()
						));
				$tagid=DB::insert_id();
				$tagarr[$tagid][id]=$tagid;
				$tagarr[$tagid][name]=$tag;
				DB::insert("home_tagrelation",array(
					"contentid"=>$contentid,
					"contenttype"=>$type,
					"dateline"=>time(),
					"tagid"=>$tagid,
					"tagname"=>$tag
					));
			}
		}
	}
	
	$usertags=DB::fetch_first("select * from ".DB::TABLE("common_user_tag")." where uid=".$uid);
	$usertags['tags']=unserialize($usertags['tags']);
	if($usertags['tags']){
		if($tagarr){
		
			if($usertags['tags'][$type]){
				$usertags['tags'][$type]=$usertags['tags'][$type].','.implode(',',array_keys($tagarr));
			}else{
				$usertags['tags'][$type]=implode(',',array_keys($tagarr));
			}
			$usertags['tags'][$type]=implode(',',array_unique(explode(',',$usertags['tags'][$type])));
			$usertags['tags']=addslashes(serialize($usertags['tags']));
			DB::update("common_user_tag",$usertags,array('uid'=>$uid));
		}
	}else{
		$typearr=array( $type=>implode(',',array_keys($tagarr)));
		$arr=array('uid'=>$uid,
			'tags'=>addslashes(serialize($typearr))
		);
		DB::insert('common_user_tag',$arr);
	}
	
	return $tagarr;

}

function atrecord($uids,$feedid){
	for($i=0;$i<count($uids);$i++){
		$value=DB::fetch_first("select * from ".DB::TABLE("common_user_at")." where uid=".$uids[$i]);
		$arrat=explode(',',$value[feedids]);
		if(count($arrat)){
			array_unshift($arrat,$feedid);
			$newarr=array_unique($arrat);
			$value[feedids]=implode(',',$newarr);
			DB::update('common_user_at',$value,array('uid'=>$uids[$i]));
		}else{
			$arr=array('uid'=>$uids[$i],'feedids'=>$feedid);
			DB::insert('common_user_at',$arr);
		}
	}
}

function useravatar($uid){
	$uid = abs(intval($uid));
	$uid = sprintf("%09d", $uid);
	$dir1 = substr($uid, 0, 3);
	$dir2 = substr($uid, 3, 2);
	$dir3 = substr($uid, 5, 2);
	$avatar="./uc_server/data/avatar/".$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_small.jpg";
	if(file_exists(dirname(dirname(dirname(__FILE__))).'/'.$avatar)){
		return $avatar;
	}else{
		return "uc_server/images/noavatar_small.gif";
	}
}

function validatefile($filename,$filerealname=''){
	$file = fopen($filename, "rb");  
	$bin = fread($file, 2); //只读2字节  
	fclose($file);  
	$strInfo = @unpack("c2chars", $bin);  
	$typeCode = intval($strInfo['chars1'].$strInfo['chars2']);  
	$fileType = ''; 
	if($filerealname){
		$temp_arr = explode(".", $filerealname);
		$file_ext = array_pop($temp_arr);
		$file_ext = trim($file_ext);
		$file_ext = strtolower($file_ext);
	}
	switch ($typeCode)  {  
		case 7790:  
			$fileType = 'exe';  
			break;  
		case 7784:  
			$fileType = 'midi';  
			break;  
		case 8297:  
			$fileType = 'rar';  
			break;  
		case 8075:
			if($file_ext=='docx'){
			}else{  
           		$fileType = 'zip';  
			}
            break;  
		case 255216:  
			$fileType = 'jpg';  
			break;  
		case 7173:  
			$fileType = 'gif';  
			break;  
		case 6677:  
			$fileType = 'bmp';  
			break;  
		case 13780:  
			$fileType = 'png';  
			break;  
		case 7076:
			$fileType = 'flv';  
			break;
		default:  
	} 
	if ($strInfo['chars1']=='-1' && $strInfo['chars2']=='-40' ) {
		$fileType = 'jpg';
	}
	if ($strInfo['chars1']=='-119' && $strInfo['chars2']=='80' ) {
		$fileType = 'png';
	}
	
	return $fileType;

}

//用户切换马甲活匿名时改变用户状态
function changeviewstatus($uid,$statusid){
	DB::query("update ".DB::TABLE("common_member")." set repeatsstatus=".$statusid." where uid=".$uid);
}

function updownsql($type,$table,$order,$id,$num,$keyid,$wheresql=''){
	if($type=='up'){
		$sql['refresh']='0';
		$sql['ordersql']=" order by $order desc limit 0,$num ";
	}elseif($type=='down'){
		if($wheresql){
			$count=DB::result_first("select count(*) from ".DB::table($table)." where $keyid>$id and ".$wheresql);
		}else{
			$count=DB::result_first("select count(*) from ".DB::table($table)." where $keyid>$id");
		}
		if($count>$num){
			$sql['refresh']='1';
			$sql['ordersql']=" order by $order desc limit 0,$num ";	
		}else{
			$sql['refresh']='0';
			$sql['ordersql']=" order by $order desc limit 0,$num ";
		}
	
	}
	return $sql;
}

function openURL($url) {
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	return curl_exec($ch);
}

//根据用户uids获取省信息
function getprovincebyuids($uids){
	global $_G;
	$uid_arr= explode(",",$uids);
	$progroups=array();
	if($_G[config]['memory']['redis']['on']){
		$redis = new Redis();
		$redis->connect($_G[config]['memory']['redis']['server'],$_G[config]['memory']['redis']['port']);

		$news="-1";

		foreach($uid_arr as $uid){
			$key = "user_province_group_".$uid;
			$proarr=$redis->get($key);
			if($proarr)
				$progroups[$uid]=json_decode($proarr);
			else
				$news.=",".$uid;
			unset($key);
		}

		if($news!="-1"){
			$new_arr= explode(",",substr($news,3));
			$data=getprovinceByJk(substr($news,3));
			foreach($new_arr as $n)
				$progroups[$n]=$data[$n];
		}
	}else{
		$progroups=getprovinceByJk($uids);
	}

	$jsondata = json_encode ($progroups);
	$callback = isset($_GET['callback']) ? $_GET['callback'] : '';
	if($callback)
		return "$callback($jsondata)";
	else
		return $jsondata;
}

//根据接口获取用户省组织
function getprovinceByJk($uids){
	global $_G;
	$uid_arr= explode(",",$uids);
	$regnames="-1";
	$arr=$data=array();
	foreach($uid_arr as  $key =>$uid){
		$userarr=getuserbyuid($uid);
		$regname=$userarr[username];
		if($regname){
			$arr[$uid]=$regname;
			$regnames.=",".$regname;
		}
		else
			unset($uid_arr[$key]);
	}

	if(count($uid_arr)>0){
		$FILE_SEARCH_PAGE = "http://".$_G[config][expert][activeurl]."/usermanage/getUsersprovinceServlet.do?regNames=".substr($regnames,3);
		//$FILE_SEARCH_PAGE = "http://10.127.1.7/usermanage/getUsersprovinceServlet.do?regNames=".substr($regnames,3);
		$res=openFileAPIcompany($FILE_SEARCH_PAGE);
		$res=json_decode($res,true);

		foreach($uid_arr as $user)
			$data[$user]=$res[$arr[$user]];

		if($_G[config]['memory']['redis']['on']){
			$redis = new Redis();
			$redis->connect($_G[config]['memory']['redis']['server'],$_G[config]['memory']['redis']['port']);

			foreach($uid_arr as $id){
				$key = "user_province_group_".$id;
				$redis->setex($key, 5*24*3600, json_encode($data[$id]));
			}
		}
	}
	return $data;
}

/**
 *调用接口
 */
function openFileAPIcompany($url) {
	$opts = array (
		'http' => array (
			'method' => 'GET',
			'timeout' => 300000,
		)
	);
	$context = @ stream_context_create($opts);
	$result = file_get_contents($url, false, $context);
	return $result;
}













?>