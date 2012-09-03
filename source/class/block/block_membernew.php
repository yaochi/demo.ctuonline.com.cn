<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_membernew.php 6757 2010-03-25 09:01:29Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once libfile('block/member', 'class');
class block_membernew extends block_member {
	function block_membernew() {
		$this->setting = array(
			'startrow' => array(
				'title' => 'memberlist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function cookparameter($parameter) {
		$parameter['orderby'] = 'regdate';
		return $parameter;
	}
}

?>