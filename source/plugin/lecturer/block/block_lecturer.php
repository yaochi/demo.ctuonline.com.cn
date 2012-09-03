<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_lecturer {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
        $settings = array(
        	'select_type' => array(
				'title' => '选择分类',
				'type' => 'select',
				'value' => array(),
			),
			array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
		);
	    $settings['select_type']['value'][] = array(1, "申请讲师");
	    $settings['select_type']['value'][] = array(2, "推荐讲师");
		return $settings;
    }
    
    function getstylesetting($style) {
    	$categorys_setting = array();
    	
    	//按钮
    	$categorys_setting["button"] = array(
    	);
    	/*
    	//纯列表
        $categorys_setting["general"] = array(
            'title_len' => array(
                'title' => '记录标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
        //焦点
        $categorys_setting["focus"] = array(
            'title_len' => array(
                'title' => '记录标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'top_desc_len' => array(
                'title' => '首条记录摘要字数',
                'type' => 'text',
                'value' => 50
            ),
            'top_pic_width' => array(
                'title' => '首条记录的图片宽度',
                'type' => 'text',
                'value' => 200
            ),
            'top_pic_height' => array(
                'title' => '首条记录的图片高度',
                'type' => 'text',
                'value' => 200
            ),
        );*/
        
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
    	if (!$_GET["fid"]) {
            return array('html' => '请在专区内使用该组件', 'data' => null);
        }
        $result = array();
        
        $lecbuttons = array();
    	$mytype = $parameter["select_type"];
    	if ($mytype == 1 ) {
    		$lecbutton["url"] = "forum.php?mod=group&action=plugin&fid=$_GET[fid]&plugin_name=lecturer&plugin_op=groupmenu&lecturer_action=petition";
			$lecbutton["buttonname"] = "申请讲师";
    	} elseif ($mytype == 2) {
    		$lecbutton["url"] = "forum.php?mod=group&action=plugin&fid=$_GET[fid]&plugin_name=lecturer&plugin_op=groupmenu&lecturer_action=commend";
			$lecbutton["buttonname"] = "推荐讲师";
    	}
    	$lecbuttons[] = $lecbutton;
        $result["parameter"] = $parameter;
        $result["listdata"] = $lecbuttons;
        
        return array('data' => $result);
    }

}
?>
