<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: javascript.php 7086 2010-03-29 02:06:27Z monkey $
 */

header('Expires: '.gmdate('D, d M Y H:i:s', time() + 60).' GMT');

if(!defined('IN_API')) {
	exit('document.write(\'Access Denied\')');
}

loadcore();

include_once libfile('function/block');

loadcache('blockclass');
$bid = intval($_G['gp_bid']);
block_get_batch($bid);
$data = block_fetch_content($bid, true);

$search = array("/\<(a href)\=(\"|')(\w+)/i", "/\<(img src)\=(\"|')(\w+)/i");
$replace = "<\\1=\\2$_G[siteurl]\\3";
$data = preg_replace($search, $replace, $data);

echo 'document.write(\''.preg_replace("/\r\n|\n|\r/", '\n', addcslashes($data, "'\\")).'\');';

?>