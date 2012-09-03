<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
/**
 * 专区
 * 排行元素：参与人数、活跃度
 * @author SK
 *
 */
class block_ranking9 {

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
        //标题+评论数
        $categorys_setting["title_hotnum"] = array(
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
    	
    	$sql = "SELECT ff.*, fff.membernum joiner, fff.hot hotnum FROM ".DB::table("forum_forum")." ff, ".DB::table("forum_forumfield")." fff 
			WHERE ff.fid=fff.fid ";
    	$end = " LIMIT $parameter[items]";
    	$orderby = "";
    	$threads = array();
    	
    	if ($style[key] == "title_joinnum") {
    		$orderby = " ORDER BY fff.membernum DESC";
    		$query = DB::query($sql.$orderby.$end);
    		while ($thread = DB::fetch($query)) {
    			$thread["url"] = "forum.php?mod=group&fid=".$thread[fid];
                $thread["title"] = cutstr($thread["name"], $parameter["title_len"]);
				$thread['contenttype']='forum';
                $threads[$thread[fid]] = $thread;
            }
    	} elseif ($style[key] == "title_hotnum") {
    		$orderby = " ORDER BY fff.hot DESC";
    		$query = DB::query($sql.$orderby.$end);
    		while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=group&fid=".$thread[fid];
                $thread["title"] = cutstr($thread["name"], $parameter["title_len"]);
				$thread['contenttype']='forum';
                $threads[$thread[fid]] = $thread;
            }
    	}
        $result["parameter"] = $parameter;
        $result["listdata"] = $threads;
        
        return array('data' => $result);
    }

}
?>
