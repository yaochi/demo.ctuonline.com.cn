<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_index.php 6790 2010-03-25 12:30:53Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile("function/resource");

require_once libfile("function/blog");

//最新列表
$newlist = getNewResourceList();
//最热列表
$hotlist = getHotResourceList();

//print_r(adshowbytitle('NP1'));
//官方博客相关信息
//$official_blog_info = getBlog4Portal();
$official_blog_info = getBlog4PortalFromCache();


//社区首页广告
$ads = getads();

include_once template('portal/newindex');
?>