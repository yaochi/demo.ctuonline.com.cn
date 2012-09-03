<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_lecturermanage {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
        $settings = array(
			array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
		);
		return $settings;
    }
    
	function getstylesetting($style) {
    	$categorys_setting = array();
    	
    	//搜索框
    	$categorys_setting["searchbox1"] = array(
    	);
    	$categorys_setting["searchbox2"] = array(
    	);
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
    	if (!$_GET["fid"]) {
            return array('html' => '请在专区内使用该组件', 'data' => null);
        }
        $result = array();
        $result["parameter"] = $parameter;
        $result["listdata"] = array();
        
        return array('data' => $result);
    }

}
?>
