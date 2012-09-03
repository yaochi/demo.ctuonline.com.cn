<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_lecturerecord2 {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
        $settings = array(
			array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
		);
		return $settings;
    }
    
    function getstylesetting($style) {
    	$categorys_setting = array();
    	
    	$categorys_setting["topcredit"] = array(
    	);
        
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
    	if (!$_GET["fid"]) {
            return array('html' => '请在专区内使用该组件', 'data' => null);
        }
        $year=time();
		$year=date('Y', $year);
        $result = array();
        $result["parameter"] = $parameter;
        require_once (dirname(dirname(__FILE__))."/function/function_lecturerecord.php");
	   	$num=$parameter[items];
	   	if($num>10) $num=10;
        $result["list"] =getTop($year,$num);
        
        return array('data' => $result);
    }

}
?>
