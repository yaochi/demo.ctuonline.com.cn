<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
/**
 * 问卷
 * 排行元素：pre_questionary.joiner 参与人数
 * @author SK
 *
 */
class block_ranking4 {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
		return array(
            array("html" => '<input type="hidden" name="parameter[plugin_id]" value="' . $plugin_id . '"/>')
        );
    }
    
    function getstylesetting($style) {
        $categorys_setting = array();
        //标题+参与人数
        $categorys_setting["title_joinnum"] = array(
            'title_len' => array(
                'title' => '标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
    	return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
    	$fid = $_GET["fid"];
    	
    	$sql = "SELECT * FROM ".DB::table("questionary")." WHERE fid=$fid AND moderated>-1";
    	$end = " LIMIT $parameter[items]";
    	$orderby = "";
    	$threads = array();
    	
    	if ($style[key] == "title_joinnum") {
    		$orderby = " ORDER BY joiner DESC";
    		$query = DB::query($sql.$orderby.$end);
    		while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=group&action=plugin&fid=".$fid."&plugin_name=questionary&plugin_op=groupmenu&questid=".$thread[questid]."&questionary_action=answer";
                $thread["title"] = cutstr($thread["questname"], $parameter["title_len"]);
				$thread['contenttype']='questionary';
				$thread['id']=$thread[questid];
                $threads[$thread[questid]] = $thread;
            }
    	}
        $result["parameter"] = $parameter;
        $result["listdata"] = $threads;
        
        return array('data' => $result);
    }

}
?>
