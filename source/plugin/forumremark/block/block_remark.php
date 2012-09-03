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

class block_remark {

	function getsetting() {
		
		$plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        return array(
                array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>'), );
	}
	
	function getstylesetting($style) {
		return array();
    }

	function getdata($style, $parameter) {
		
		global $_G;

		 
			
	
		$result=array();
		$result["parameter"] = $parameter;
	
        return array('data' => $result);
	}
}

?>