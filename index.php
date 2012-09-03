<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.php 10420 2010-05-11 06:58:24Z zhaoxiongfei $
 */

@include './config/config_global.php';
if(empty($_config)) {
	if(!file_exists('./data/install.lock')) {
		header('location: install');
		exit;
	} else {
		error('config_notfound');
	}
}

$_t_curapp = '';

foreach($_config['app']['domain'] as $_t_app => $_t_domain) {
	if($_t_domain == $_SERVER['HTTP_HOST']) {
		$_t_curapp = $_t_app;
		break;
	}
}

if(empty($_t_curapp) || $_t_curapp == 'default') {
	$_t_curapp = !empty($_config['app']['default']) ? $_config['app']['default'] : 'forum';
}

$_SERVER['PHP_SELF'] = str_replace('index.php', $_t_curapp.'.php', $_SERVER['PHP_SELF']);

if(!empty($_SERVER['QUERY_STRING'])) {
	if(is_numeric($_SERVER['QUERY_STRING'])) {
		$_t_curapp = 'home';
		$_GET = array('mod'=>'space', 'uid'=>$_SERVER['QUERY_STRING']);

	} elseif(strtolower($_SERVER['QUERY_STRING']{0}) == 'g' && is_numeric(substr($_SERVER['QUERY_STRING'], 1))) {
		$_t_curapp = 'forum';
		$_GET = array('mod'=>'group', 'fid'=>intval(substr($_SERVER['QUERY_STRING'], 1)));

	} elseif(strtolower($_SERVER['QUERY_STRING']{0}) == 'f' && is_numeric(substr($_SERVER['QUERY_STRING'], 1))) {
		$_t_curapp = 'forum';
		$_GET = array('mod'=>'forumdisplay', 'fid'=>intval(substr($_SERVER['QUERY_STRING'], 1)));

	} elseif(strtolower($_SERVER['QUERY_STRING']{0}) == 't' && is_numeric(substr($_SERVER['QUERY_STRING'], 1))) {
		$_t_curapp = 'forum';
		$_GET = array('mod'=>'viewthread', 'tid'=>intval(substr($_SERVER['QUERY_STRING'], 1)));
	}
} else {
	if(!empty($_config['home']['allowdomain']) && !empty($_config['home']['domainroot'])) {
		$_t_hosts = explode('.', $_SERVER['HTTP_HOST']);
		$_t_domainroots = explode('.', $_config['home']['domainroot']);
		$_t_domains = array_diff($_t_hosts, $_t_domainroots);
		if($_t_domains && count($_t_domains) == 1) {
			$_t_domain = array_shift($_t_domains);
			$_t_holddomains = empty($_config['home']['holddomain'])?array():explode(',', $_config['home']['holddomain']);
			if($_t_domain != 'www' && !in_array($_t_domain, $_t_holddomains)) {
				$_t_curapp = 'home';
				$_GET = array('mod'=>'space', 'domain'=>$_t_domain);
			}
		}
	}
}

require './'.$_t_curapp.'.php';

?>