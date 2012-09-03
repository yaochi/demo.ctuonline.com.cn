<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
/**
 * 相册
 * 排行元素：picnum 图片数
 * @author SK
 *
 */
class block_ranking2 {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
		return array(
            array("html" => '<input type="hidden" name="parameter[plugin_id]" value="' . $plugin_id . '"/>')
        );
    }
    
    function getstylesetting($style) {
        $categorys_setting = array();
        //标题+图片数
        $categorys_setting["title_picnum"] = array(
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
    	
    	$sql = "SELECT * FROM ".DB::table("group_album")." WHERE fid=$fid ";
    	$end = " LIMIT $parameter[items]";
    	$orderby = "";
    	$threads = array();
    	
    	if ($style[key] == "title_picnum") {
    		$orderby = " ORDER BY picnum DESC";
    		$query = DB::query($sql.$orderby.$end);
    		while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=group&action=plugin&fid=".$fid."&plugin_name=groupalbum2&plugin_op=groupmenu&id=".$thread[albumid]."&groupalbum2_action=index";
                $thread["title"] = cutstr($thread["albumname"], $parameter["title_len"]);
				$thread['contenttype']='album';
				$thread['id']=$thread[albumid];
                $threads[$thread[albumid]] = $thread;
            }
    	}
        $result["parameter"] = $parameter;
        $result["listdata"] = $threads;
        
        return array('data' => $result);
    }

}
?>
