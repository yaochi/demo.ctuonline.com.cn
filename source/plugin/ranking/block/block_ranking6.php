<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
/**
 * 直播
 * 表名：pre_group_live
 * 排行元素：viewnum 查看数 playnum 播放数
 * @author SK
 *
 */
class block_ranking6 {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
		return array(
            array("html" => '<input type="hidden" name="parameter[plugin_id]" value="' . $plugin_id . '"/>')
        );
    }
    
    function getstylesetting($style) {
        $categorys_setting = array();
        //标题+查看次数
        $categorys_setting["title_viewnum"] = array(
            'title_len' => array(
                'title' => '标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
        //标题+点播数
        $categorys_setting["title_playnum"] = array(
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
    	
    	$sql = "SELECT * FROM ".DB::table("group_live")." WHERE fid=$fid ";
    	$end = " LIMIT $parameter[items]";
    	$orderby = "";
    	$threads = array();
    	
    	if ($style[key] == "title_viewnum") {
    		$orderby = " ORDER BY viewnum DESC";
    		$query = DB::query($sql.$orderby.$end);
    		while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=group&action=plugin&fid=".$fid."&plugin_name=grouplive&plugin_op=groupmenu&grouplive_action=index&&id=".$thread[liveid];
                $thread["views"] = $thread[viewnum];
                $thread["title"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread['contenttype']='live';
				$thread['id']=$thread[liveid];
                $threads[$thread[liveid]] = $thread;
            }
    	} elseif ($style[key] == "title_playnum") {
    		$orderby = " ORDER BY playnum DESC";
    		$query = DB::query($sql.$orderby.$end);
    		while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=group&action=plugin&fid=".$fid."&plugin_name=grouplive&plugin_op=groupmenu&grouplive_action=index&&id=".$thread[liveid];
                $thread["title"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread['contenttype']='live';
				$thread['id']=$thread[liveid];
                $threads[$thread[liveid]] = $thread;
            }
    	}
        $result["parameter"] = $parameter;
        $result["listdata"] = $threads;
        
        return array('data' => $result);
    }

}
?>
