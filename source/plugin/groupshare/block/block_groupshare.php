<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_groupshare {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
        $settings = array(
			array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
		);
		return $settings;
    }
    
    function getstylesetting($style) {
    	$categorys_setting = array();
    	//标准样式
        $categorys_setting["standard"] = array(
            
        );
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
		global $_G;
		require_once libfile('function/share');
    	if (!$_GET["fid"]) {
            return array('html' => '请在专区内使用该组件', 'data' => null);
        }
        $result = array();
    	$fid = $_GET["fid"];
        $sql = "SELECT * FROM ".DB::table('group_share')." WHERE fid=".$fid." order by dateline desc LIMIT $parameter[items] ";
		$query=DB::query($sql);
      	while($value=DB::fetch($query)){
			$value=mkshare($value);
			$sharevalue[sid]=$value[sid];
			$sharevalue[uid]=$value[uid];
			$sharevalue[fid]=$value[fid];
			$sharevalue[username]=$value[username];
			$sharevalue[action]=$value[title_template];
			if("link"==$value[type] || "video"==$value[type] || "music"==$value[type] || "flash"==$value[type]){			
				$sharevalue[subject]=$value[body_data]['link'];
			}else{
				$sharevalue[subject]=$value[body_data][subject];
			}
			$sharevalue[dateline]=$value[dateline];
			$list[]=$sharevalue;
		}
        $result["parameter"] = $parameter;
		$result["listdata"]=$list;
		//$result["more_list"]=$more_list;
        //print_r($result);
        return array('data' => $result);
    }

}
?>
