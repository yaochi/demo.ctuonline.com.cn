<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_checktools.php 11029 2010-05-20 04:05:04Z monkey $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

if(!isfounder()) cpmsg('noaccess_isfounder', '', 'error');

if($operation == 'filecheck') {

	$step = max(1, intval($_G['gp_step']));
	shownav('tools', 'nav_filecheck');
	showsubmenusteps('nav_filecheck', array(
		array('nav_filecheck_confirm', $step == 1),
		array('nav_filecheck_verify', $step == 2),
		array('nav_filecheck_completed', $step == 3)
	));
	if($step == 1) {
		cpmsg($lang[filecheck_tips_step1], 'action=checktools&operation=filecheck&step=2', 'button', '', FALSE);
	} elseif($step == 2) {
		cpmsg(cplang('filecheck_verifying'), "action=checktools&operation=filecheck&step=3", 'loading', '', FALSE);
	} elseif($step == 3) {

		if(!$discuzfiles = @file('./source/admincp/discuzfiles.md5')) {
			cpmsg('filecheck_nofound_md5file', '', 'error');
		}

		$md5data = array();
		$cachelist = checkcachefiles('data/cache/');
		checkfiles('./', '\.php|\.txt|\.ico|\.xml', 0);
		checkfiles('config/', '\.php', 1, 'config_global.php,config_ucenter.php');
		checkfiles('data/', '\.xml', 0);
		checkfiles('data/avatar/', '\.htm', 0);
		checkfiles('data/cache/', '\.htm', 0);
		checkfiles('data/ipdata/', '\.htm|\.dat', 0);
		checkfiles('data/template/', '\.htm', 0);
		checkfiles('data/threadcache/', '\.htm', 0);
		checkfiles('template/', '\.php|\.htm|\.css|\.jpg|\.xml');
		checkfiles('api/', '\.php|\.htm');
		checkfiles('source/', '\.php|\.md5|\.htm|\.table|\.xml', 1, 'discuzfiles.md5');
		checkfiles('static/', '\.js');
		checkfiles('static/', '\.png|\.css|\.jpg');
		checkfiles('uc_client/', '\.php|\.htm', 0);
		checkfiles('uc_client/data/', '\.htm');
		checkfiles('uc_client/control/', '\.php|\.htm');
		checkfiles('uc_client/model/', '\.php|\.htm');
		checkfiles('uc_client/lib/', '\.php|\.htm');
		checkfiles('uc_server/', '\.php|\.htm|\.txt|\.xml', 0);
		checkfiles('uc_server/data/', '\.htm');
		checkfiles('uc_server/api/', '\.php|\.htm');
		checkfiles('uc_server/control/', '\.php|\.htm|\.md5');
		checkfiles('uc_server/model/', '\.php|\.htm');
		checkfiles('uc_server/lib/', '\.php|\.htm');
		checkfiles('uc_server/plugin/', '\.php|\.htm|\.xml');
		checkfiles('uc_server/upgrade/', '\.php');
		checkfiles('uc_server/images/', '\..+?');
		checkfiles('uc_server/js/', '\.js|\.htm');
		checkfiles('uc_server/release/', '\.php');
		checkfiles('uc_server/view/', '\.php|\.htm');

		foreach($discuzfiles as $line) {
			$file = trim(substr($line, 34));
			$md5datanew[$file] = substr($line, 0, 32);
			if($md5datanew[$file] != $md5data[$file]) {
				$modifylist[$file] = $md5data[$file];
			}
			$md5datanew[$file] = $md5data[$file];
		}

		$weekbefore = TIMESTAMP - 604800;
		$addlist = @array_merge(@array_diff_assoc($md5data, $md5datanew), $cachelist[2]);
		$dellist = @array_diff_assoc($md5datanew, $md5data);
		$modifylist = @array_merge(@array_diff_assoc($modifylist, $dellist), $cachelist[1]);
		$showlist = @array_merge($md5data, $md5datanew, $cachelist[0]);
		$doubt = 0;
		$dirlist = $dirlog = array();
		foreach($showlist as $file => $md5) {
			$dir = dirname($file);
			if(@array_key_exists($file, $modifylist)) {
				$fileststus = 'modify';
			} elseif(@array_key_exists($file, $dellist)) {
				$fileststus = 'del';
			} elseif(@array_key_exists($file, $addlist)) {
				$fileststus = 'add';
			} else {
				$filemtime = @filemtime($file);
				if($filemtime > $weekbefore) {
					$fileststus = 'doubt';
					$doubt++;
				} else {
					$fileststus = '';
				}
			}
			if(file_exists($file)) {
				$filemtime = @filemtime($file);
				$fileststus && $dirlist[$fileststus][$dir][basename($file)] = array(number_format(filesize($file)).' Bytes', dgmdate($filemtime));
			} else {
				$fileststus && $dirlist[$fileststus][$dir][basename($file)] = array('', '');
			}
		}
		$result = $resultjs = '';
		$dirnum = 0;
		foreach($dirlist as $status => $filelist) {
			$dirnum++;
			$class = $status == 'modify' ? 'edited' : ($status == 'del' ? 'del' : 'unknown');
			$result .= '<tbody id="status_'.$status.'" style="display:'.($status != 'modify' ? 'none' : '').'">';
			foreach($filelist as $dir => $files) {
				$result .= '<tr><td colspan="4"><div class="ofolder">'.$dir.'</div><div class="lightfont filenum left">';
				foreach($files as $filename => $file) {
					$result .= '<tr><td><em class="files bold">'.$filename.'</em></td><td style="text-align: right">'.$file[0].'&nbsp;&nbsp;</td><td>'.$file[1].'</td><td><em class="'.$class.'">&nbsp;</em></td></tr>';
				}
			}
			$result .= '</tbody>';
			$resultjs .= '$(\'status_'.$status.'\').style.display=\'none\';';
		}

		$modifiedfiles = count($modifylist);
		$deletedfiles = count($dellist);
		$unknownfiles = count($addlist);
		$doubt = intval($doubt);

		$result .= '<script>function showresult(o) {'.$resultjs.'$(\'status_\' + o).style.display=\'\';}</script>';
		showtips('filecheck_tips');
		showtableheader('filecheck_completed');
		showtablerow('', 'colspan="4"', "<div class=\"lightfont filenum left\">".
			"<em class=\"edited\">$lang[filecheck_modify]: $modifiedfiles</em> ".($modifiedfiles > 0 ? "<a href=\"###\" onclick=\"showresult('modify')\">[$lang[view]]</a> " : '').
			"&nbsp;&nbsp;&nbsp;&nbsp;<em class=\"del\">$lang[filecheck_delete]: $deletedfiles</em> ".($deletedfiles > 0 ? "<a href=\"###\" onclick=\"showresult('del')\">[$lang[view]]</a> " : '').
			"&nbsp;&nbsp;&nbsp;&nbsp;<em class=\"unknown\">$lang[filecheck_unknown]: $unknownfiles</em> ".($unknownfiles > 0 ? "<a href=\"###\" onclick=\"showresult('add')\">[$lang[view]]</a> " : '').
			($doubt > 0 ? "&nbsp;&nbsp;&nbsp;&nbsp;<em class=\"unknown\">$lang[filecheck_doubt]: $doubt</em> <a href=\"###\" onclick=\"showresult('doubt')\">[$lang[view]]</a> " : '').
			"</div>");
		showsubtitle(array('filename', '', 'lastmodified', ''));
		echo $result;
		showtablefooter();

	}

} elseif($operation == 'ftpcheck') {

	$alertmsg = '';
	$testcontent = md5('Discuz!' + $_G['config']['security']['authkey']);
	$testfile = 'test/discuztest.txt';
	$attach_dir = $_G['setting']['attachdir'];
	@mkdir($attach_dir.'test', 0777);
	if($fp = @fopen($attach_dir.'/'.$testfile, 'w')) {
		fwrite($fp, $testcontent);
		fclose($fp);
	}

	if(!$alertmsg) {
		$settingnew = $_G['gp_settingnew'];
		$settings['ftp'] = unserialize(DB::result_first("SELECT svalue FROM ".DB::table('common_setting')." WHERE skey='ftp'"));
		$settings['ftp']['password'] = authcode($settings['ftp']['password'], 'DECODE', md5($_G['config']['security']['authkey']));
		$pwlen = strlen($settingnew['ftp']['password']);
		if($settingnew['ftp']['password']{0} == $settings['ftp']['password']{0} && $settingnew['ftp']['password']{$pwlen - 1} == $settings['ftp']['password']{strlen($settings['ftp']['password']) - 1} && substr($settingnew['ftp']['password'], 1, $pwlen - 2) == '********') {
			$settingnew['ftp']['password'] = $settings['ftp']['password'];
		}
		$settingnew['ftp']['password'] = authcode($settingnew['ftp']['password'], 'ENCODE', md5($_G['config']['security']['authkey']));
		$settingnew['ftp']['attachurl'] .= substr($settingnew['ftp']['attachurl'], -1, 1) != '/' ? '/' : '';
		$_G['setting']['ftp'] = $settingnew['ftp'];

		ftpcmd('upload', $testfile);
		$ftp = ftpcmd('object');
		if($ftp->error()) {
			$alertmsg = cplang('setting_attach_remote_'.$ftp->error());
		}
		if(!$alertmsg) {
			$str = dfsockopen($_G['setting']['ftp']['attachurl'].$testfile);
			if($str !== $testcontent) {
				$alertmsg = cplang('setting_attach_remote_geterr');
			}
		}
		if(!$alertmsg) {
			ftpcmd('delete', $testfile);
			$ftp->ftp_rmdir('test');
			$str = dfsockopen($_G['setting']['ftp']['attachurl'].$testfile);
			if($str === $testcontent) {
				$alertmsg = cplang('setting_attach_remote_delerr');
			}
			@unlink($attach_dir.'/'.$testfile);
			@rmdir($attach_dir.'test');
		}
	}
	if(!$alertmsg) {
		$alertmsg = cplang('setting_attach_remote_ok');
	}

	echo '<script language="javascript">alert(\''.str_replace('\'', '\\\'', $alertmsg).'\');parent.$(\'cpform\').action=\''.ADMINSCRIPT.'?action=setting&edit=yes\';parent.$(\'cpform\').target=\'_self\'</script>';

} elseif($operation == 'mailcheck') {
	$oldmail = unserialize($_G['setting']['mail']);
	$passwordmask = $oldmail['auth_password'] ? $oldmail['auth_password']{0}.'********'.substr($oldmail['auth_password'], -2) : '';
	$settingnew = $_G['gp_settingnew'];
	$settingnew['mail']['auth_password'] = $settingnew['mail']['auth_password'] == $passwordmask ? $oldmail['auth_password'] : $settingnew['mail']['auth_password'];
	$_G['setting']['mail'] = serialize($settingnew['mail']);
	$test_to = $_G['gp_test_to'];
	$test_from = $_G['gp_test_from'];
	$date = date('Y-m-d H:i:s');
	$alertmsg = '';

	$title = $lang['setting_mailcheck_title_'.$settingnew['mail']['mailsend']];
	$message = $lang['setting_mailcheck_message_'.$settingnew['mail']['mailsend']].' '.$test_from.$lang['setting_mailcheck_date'].' '.$date;

	$_G['setting']['bbname'] = $lang['setting_mail_check_method_1'];
	include libfile('function/mail');
	$succeed = sendmail($test_to, $title.' @ '.$date, $_G['setting']['bbname']."\n\n\n$message", $test_from);
	$_G['setting']['bbname'] = $lang['setting_mail_check_method_2'];
	$succeed = sendmail($test_to, $title.' @ '.$date, $_G['setting']['bbname']."\n\n\n$message", $test_from);

	if($succeed) {
		$alertmsg = $lang['setting_mail_check_success_1']."$title @ $date".$lang['setting_mail_check_success_2'];
	} else {
		$alertmsg = $lang['setting_mail_check_error'].$alertmsg;
	}

	echo '<script language="javascript">alert(\''.str_replace(array('\'', "\n", "\r"), array('\\\'', '\n', ''), $alertmsg).'\');parent.$(\'cpform\').action=\''.ADMINSCRIPT.'?action=setting&edit=yes\';parent.$(\'cpform\').target=\'_self\'</script>';

} elseif($operation == 'imagepreview') {

	$settingnew = $_G['gp_settingnew'];
	if(!empty($_G['gp_previewthumb'])) {
		$_G['setting']['imagelib'] = $settingnew['imagelib'];
		$_G['setting']['imageimpath'] = $settingnew['imageimpath'];
		$_G['setting']['thumbwidth'] = $settingnew['thumbwidth'];
		$_G['setting']['thumbheight'] = $settingnew['thumbheight'];
		$_G['setting']['thumbquality'] = $settingnew['thumbquality'];

		require_once libfile('class/image');
		@unlink(DISCUZ_ROOT.'./data/attachment/temp/watermark_temp1.jpg');
		@unlink(DISCUZ_ROOT.'./data/attachment/temp/watermark_temp2.jpg');
		$image = new image;
		$r = 0;
		if(!($r = $image->Thumb(DISCUZ_ROOT.'./static/image/admincp/watermarkpreview.jpg', 'temp/watermark_temp1.jpg', $_G['setting']['thumbwidth'], $_G['setting']['thumbheight'], 1))) {
			$r = $image->error();
		}
		$sizetarget1 = $image->imginfo['size'];
		$image->Thumb(DISCUZ_ROOT.'./static/image/admincp/watermarkpreview.jpg', 'temp/watermark_temp2.jpg', $_G['setting']['thumbwidth'], $_G['setting']['thumbheight'], 2);
		$sizetarget2 = $image->imginfo['size'];
		if($r > 0) {
			showsubmenu('imagepreview_thumb');
			$sizesource = filesize(DISCUZ_ROOT.'./static/image/admincp/watermarkpreview.jpg');
			echo '<img src="data/attachment/temp/watermark_temp1.jpg?'.random(5).'"><br /><br />'.
				$lang['imagepreview_imagesize_source'].' '.number_format($sizesource).' Bytes &nbsp;&nbsp;'.
				$lang['imagepreview_imagesize_target'].' '.number_format($sizetarget1).' Bytes ('.
				(sprintf("%2.1f", $sizetarget1 / $sizesource * 100)).'%)<br /><br />';
			echo '<img src="data/attachment/temp/watermark_temp2.jpg?'.random(5).'"><br /><br />'.
				$lang['imagepreview_imagesize_source'].' '.number_format($sizesource).' Bytes &nbsp;&nbsp;'.
				$lang['imagepreview_imagesize_target'].' '.number_format($sizetarget2).' Bytes ('.
				(sprintf("%2.1f", $sizetarget2 / $sizesource * 100)).'%)';
		} else {
			cpmsg('imagepreview_errorcode_'.$r, '', 'error');
		}
	} else {
		$_G['setting']['watermarkstatus'] = $settingnew['watermarkstatus'];
		if(!$_G['setting']['watermarkstatus']) {
			cpmsg('watermarkpreview_error', '', 'error');
		}
		$_G['setting']['imagelib'] = $settingnew['imagelib'];
		$_G['setting']['imageimpath'] = $settingnew['imageimpath'];
		$_G['setting']['watermarktype'] = $settingnew['watermarktype'];
		$_G['setting']['watermarktrans'] = $settingnew['watermarktrans'];
		$_G['setting']['watermarkquality'] = $settingnew['watermarkquality'];
		$_G['setting']['watermarkminwidth'] = $settingnew['watermarkminwidth'];
		$_G['setting']['watermarkminheight'] = $settingnew['watermarkminheight'];
		$settingnew['watermarktext']['size'] = intval($settingnew['watermarktext']['size']);
		$settingnew['watermarktext']['angle'] = intval($settingnew['watermarktext']['angle']);
		$settingnew['watermarktext']['shadowx'] = intval($settingnew['watermarktext']['shadowx']);
		$settingnew['watermarktext']['shadowy'] = intval($settingnew['watermarktext']['shadowy']);
		$settingnew['watermarktext']['translatex'] = intval($settingnew['watermarktext']['translatex']);
		$settingnew['watermarktext']['translatey'] = intval($settingnew['watermarktext']['translatey']);
		$settingnew['watermarktext']['skewx'] = intval($settingnew['watermarktext']['skewx']);
		$settingnew['watermarktext']['skewy'] = intval($settingnew['watermarktext']['skewy']);
		$settingnew['watermarktext']['fontpath'] = str_replace(array('\\', '/'), '', $settingnew['watermarktext']['fontpath']);
		$settingnew['watermarktext']['color'] = preg_replace('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/e', "hexdec('\\1').','.hexdec('\\2').','.hexdec('\\3')", $settingnew['watermarktext']['color']);
		$settingnew['watermarktext']['shadowcolor'] = preg_replace('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/e', "hexdec('\\1').','.hexdec('\\2').','.hexdec('\\3')", $settingnew['watermarktext']['shadowcolor']);

		if($_G['setting']['watermarktype'] == 'text') {
			if($settingnew['watermarktext']['fontpath']) {
				$fontpath = $settingnew['watermarktext']['fontpath'];
				$fontpathnew = 'ch/'.$fontpath;
				$settingnew['watermarktext']['fontpath'] = file_exists('static/image/seccode/font/'.$fontpathnew) ? $fontpathnew : '';
				if(!$settingnew['watermarktext']['fontpath']) {
					$fontpathnew = 'en/'.$fontpath;
					$settingnew['watermarktext']['fontpath'] = file_exists('static/image/seccode/font/'.$fontpathnew) ? $fontpathnew : '';
				}
				if(!$settingnew['watermarktext']['fontpath']) {
					cpmsg('watermarkpreview_fontpath_error', '', 'error');
				}
				$settingnew['watermarktext']['fontpath'] = 'static/image/seccode/font/'.$settingnew['watermarktext']['fontpath'];
			}

			if($settingnew['watermarktext']['text'] && strtoupper(CHARSET) != 'UTF-8') {
				include libfile('class/chinese');
				$c = new Chinese(CHARSET, 'utf8');
				$settingnew['watermarktext']['text'] = $c->Convert($settingnew['watermarktext']['text']);
			}
			$settingnew['watermarktext']['text'] = bin2hex($settingnew['watermarktext']['text']);
			$_G['setting']['watermarktext'] = $settingnew['watermarktext'];
		}

		require_once libfile('class/image');
		@unlink(DISCUZ_ROOT.'./data/attachment/temp/watermark_temp3.jpg');
		$image = new image;
		if(!($r = $image->Watermark(DISCUZ_ROOT.'./static/image/admincp/watermarkpreview.jpg', 'temp/watermark_temp3.jpg'))) {;
			$r = $image->error();
		}
		if($r > 0) {
			showsubmenu('imagepreview_watermark');
			$sizesource = filesize('static/image/admincp/watermarkpreview.jpg');
			$sizetarget = $image->imginfo['size'];
			echo '<img src="data/attachment/temp/watermark_temp3.jpg?'.random(5).'"><br /><br />'.
				$lang['imagepreview_imagesize_source'].' '.number_format($sizesource).' Bytes &nbsp;&nbsp;'.
				$lang['imagepreview_imagesize_target'].' '.number_format($sizetarget).' Bytes ('.
				(sprintf("%2.1f", $sizetarget / $sizesource * 100)).'%)';
		} else {
			cpmsg('imagepreview_errorcode_'.$r, '', 'error');
		}
	}

} elseif($operation == 'rewrite') {

	$rule = array();
	$rewritedata = rewritedata();
	foreach($rewritedata['rulesearch'] as $k => $v) {
		$v = !$_G['setting']['rewriterule'][$k] ? $v : $_G['setting']['rewriterule'][$k];
		$pvmaxv = count($rewritedata['rulevars'][$k]) + 2;
		$vkeys = array_keys($rewritedata['rulevars'][$k]);
		$rewritedata['rulereplace'][$k] = pvsort($vkeys, $v, $rewritedata['rulereplace'][$k]);
		$v = str_replace($vkeys, $rewritedata['rulevars'][$k], addcslashes($v, '?*+^$.[]()|'));
		$rule['{apache1}'] .= "\t".'RewriteCond %{QUERY_STRING} ^(.*)$'."\n\t".'RewriteRule ^(.*)/'.$v.'$ $1/'.pvadd($rewritedata['rulereplace'][$k])."&%1\n";
		$rule['{apache2}'] .= 'RewriteCond %{QUERY_STRING} ^(.*)$'."\n".'RewriteRule ^'.$v.'$ '.$rewritedata['rulereplace'][$k]."&%1\n";
		$rule['{iis}'] .= 'RewriteRule ^(.*)/'.$v.'\?*(.*)$ $1/'.addcslashes(pvadd($rewritedata['rulereplace'][$k]).'&$'.$pvmaxv, '.?')."\n";
		$rule['{zeus}'] .= 'match URL into $ with ^(.*)/'.$v.'\?*(.*)$'."\n".'if matched then'."\n\t".'set URL = $1/'.pvadd($rewritedata['rulereplace'][$k]).'&$'.$pvmaxv."\nendif\n";
		$rule['{nginx}'] .= 'rewrite ^([^\.]*)/'.$v.'$ $1/'.stripslashes(pvadd($rewritedata['rulereplace'][$k]))." last;\n";
	}
	$rule['{nginx}'] .= "if (!-e \$request_filename) {\n\treturn 404;\n}";
	echo str_replace(array_keys($rule), $rule, cplang('rewrite_message'));

}

function pvsort($key, $v, $s) {
	$r = '/';
	$p = '';
	foreach($key as $k) {
		$r .= $p.preg_quote($k);
		$p = '|';
	}
	$r .= '/';
	preg_match_all($r, $v, $a);
	$a = $a[0];
	$a = array_flip($a);
	foreach($a as $key => $value) {
		$s = str_replace($key, '$'.($value + 1), $s);
	}
	return $s;
}

function pvadd($s) {
	$s = str_replace(array('$3', '$2', '$1'), array('~4', '~3', '~2'), $s);
	return str_replace(array('~4', '~3', '~2'), array('$4', '$3', '$2'), $s);
}

function checkfiles($currentdir, $ext = '', $sub = 1, $skip = '') {
	global $md5data;
	$dir = @opendir(DISCUZ_ROOT.$currentdir);
	$exts = '/('.$ext.')$/i';
	$skips = explode(',', $skip);

	while($entry = @readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..' && (preg_match($exts, $entry) || $sub && is_dir($file)) && !in_array($entry, $skips)) {
			if($sub && is_dir($file)) {
				checkfiles($file.'/', $ext, $sub, $skip);
			} else {
				$md5data[$file] = md5_file($file);
			}
		}
	}
}

function checkcachefiles($currentdir) {
	global $_G;
	$dir = opendir($currentdir);
	$exts = '/\.php$/i';
	$showlist = $modifylist = $addlist = array();
	while($entry = readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..' && preg_match($exts, $entry)) {
			$fp = fopen($file, "rb");
			$cachedata = fread($fp, filesize($file));
			fclose($fp);

			if(preg_match("/^<\?php\n\/\/Discuz! cache file, DO NOT modify me!\n\/\/Created: [\w\s,:]+\n\/\/Identify: (\w{32})\n\n(.+?)\?>$/s", $cachedata, $match)) {
				$showlist[$file] = $md5 = $match[1];
				$cachedata = $match[2];

				if(md5($entry.$cachedata.$_G['config']['security']['authkey']) != $md5) {
					$modifylist[$file] = $md5;
				}
			} else {
				$showlist[$file] = $addlist[$file] = '';
			}
		}

	}

	return array($showlist, $modifylist, $addlist);
}

function checkmailerror($type, $error) {
	global $alertmsg;
	$alertmsg .= !$alertmsg ? $error : '';
}

?>