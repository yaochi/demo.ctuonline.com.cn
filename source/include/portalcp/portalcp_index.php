<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_article.php 7701 2010-04-12 06:01:33Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = $_GET['op'] == 'push' ? 'push' : 'list';

require_once libfile('function/portalcp');

$category = getcategory();

if (!checkperm('allowmanagearticle')) {

	$permission = getallowcategory($_G['uid']);

	if(empty($permission)) {
		showmessage('portal_nopermission');
	}
	$permissioncategory = getpermissioncategory($category,array_keys($permission));
} else {
	$permissioncategory = $category;
}

if($op == 'push') {

	$_GET['id'] = intval($_GET['id']);
	$_GET['idtype'] = in_array($_GET['idtype'], array('tid', 'blogid')) ? $_GET['idtype'] : '';
	if(empty($_GET['idtype'])) {
		showmessage('article_push_invalid_object');
	}
	$havepush = DB::result_first("SELECT COUNT(*) FROM ".DB::table('portal_article_title')." WHERE id='$_GET[id]' AND idtype='$_GET[idtype]'");
	if($havepush) {
		showmessage('article_push_invalid_repeat');
	}

	$categorytree = '';
	foreach($permissioncategory as $key => $value) {
		if ($category[$key]['level'] == 0) {
			$categorytree .= showcategoryrowpush($key, 0);
		}
	}

} else {

	$categorytree = '';
	foreach($permissioncategory as $key => $value) {
		if ($category[$key]['level'] == 0) {
			$categorytree .= showcategoryrow($key, 0);
		}
	}

	$language =  lang('portal/template');
	$navtitle = $language['category_management'].' - '.$language['portal_management'];

}

include_once template("portal/portalcp_index");

?>