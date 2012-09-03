<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_blank.php 7141 2010-03-29 12:25:15Z xupeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_blank {

	function getsetting() {
		global $_G;
		$settings = array(
			'content' => array(
				'title' => 'blank_content',
				'type' => 'textarea'
			)
		);
		return $settings;
	}
	
	function getstylesetting($style) {
		return array();
    }

	function getdata($style, $parameter) {
		require_once libfile('function/home');
		$return = getstr($parameter['content'], '', 1, 0, 1, 0, 1);
		
		return array('html' => $return, 'data' => array('listdata' => $return));
	}
}

?>