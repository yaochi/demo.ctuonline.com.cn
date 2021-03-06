<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_activity.php 6799 2010-03-25 12:56:43Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_myapp {

	var $setting = array();

	function block_myapp(){
		$this->setting = array(
			'titlelength' => array(
				'title' => 'myapp_titlelength',
				'type' => 'text',
				'default' => 40
			),
			'startrow' => array(
				'title' => 'myapp_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		return $settings;
	}

	function cookparameter($parameter) {
		return $parameter;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);

		$titlelength	= !empty($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$startrow       = !empty($parameter['startrow']) ? intval($parameter['startrow']) : '0';
		$items          = !empty($parameter['items']) ? intval($parameter['items']) : 10;

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();
		$bansql = $bannedids ? ' AND appid NOT IN ('.dimplode($bannedids).')' : '';

		$sql = 'SELECT * FROM '.DB::table('common_myapp')." WHERE flag>=0 $bansql ORDER BY flag DESC, displayorder LIMIT $startrow, $items";
		$query = DB::query($sql);
		while($data = DB::fetch($query)) {
			$list[] = array(
				'id' => $data['appid'],
				'idtype' => 'appid',
				'title' => cutstr(str_replace('\\\'', '&#39;', $data['appname']), $titlelength),
				'url' => 'userapp.php?id='.$data['appid'],
				'pic' => '',
				'picflag' => '',
				'summary' => '',
				'fields' => array(
					'icon' => 'http://appicon.manyou.com/logos/'.$data['appid'],
					'icon_small' => 'http://appicon.manyou.com/icons/'.$data['appid'],
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}
}


?>