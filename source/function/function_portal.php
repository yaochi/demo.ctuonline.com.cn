<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_portal.php 10056 2010-05-06 08:17:37Z zhangguosheng $
 */

function category_remake($catid) {
	global $_G;

	$cat = $_G['cache']['portalcategory'][$catid];
	if(empty($cat)) return array();

	$_G['portal_categorys'] = array();
	foreach ($_G['cache']['portalcategory'] as $value) {
		if($value['upid']<1) {
			$_G['portal_categorys'][] = $value;
		}
		if($value['catid'] == $cat['upid']) {
			$cat['ups'][$value['catid']] = $value;
		} elseif($value['upid'] == $cat['catid']) {
			$cat['subs'][$value['catid']] = $value;
		} elseif($value['upid'] == $cat['upid']) {
			$cat['others'][$value['catid']] = $value;
		}
	}
	return $cat;
}

?>