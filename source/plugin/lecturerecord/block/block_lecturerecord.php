<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_lecturerecord {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
        $settings = array(
			array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
		);
		return $settings;
    }
    
    function getstylesetting($style) {
    	$categorys_setting = array();
    	//授课记录创建
    	$categorys_setting["selfstyle"] = array(
    	);
        
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
    	if (!$_GET["fid"]) {
            return array('html' => '请在专区内使用该组件', 'data' => null);
        }
        $result = array();
        $lecbutton = array();
    	$lecbutton["formurl"] = "forum.php?mod=group&action=plugin&fid=".$_GET["fid"]."&plugin_name=lecturerecord&plugin_op=createmenu&diy=&lecturerecord_action=save";
        $result["parameter"] = $parameter;
        $result["lecbutton"] = $lecbutton;
        
        return array('data' => $result);
    }

}
?>
