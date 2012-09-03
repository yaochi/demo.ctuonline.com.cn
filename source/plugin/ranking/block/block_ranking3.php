<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
/**
 * 提问吧
 * pre_forum_thread.special = 3
 * 排行元素：views 查看次数（点击次数），replies 评论数（回复数）
 * @author SK
 *
 */
class block_ranking3 {

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
        //标题+评论数
        $categorys_setting["title_commentnum"] = array(
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
    	
    	$sql = "SELECT * FROM ".DB::table("forum_thread")."
			WHERE fid=$fid AND special=3 AND displayorder IN (0, 1, 2, 3, 4)";
    	$end = " LIMIT $parameter[items]";
    	$orderby = "";
    	$threads = array();
    	
    	if ($style[key] == "title_viewnum") {
    		$orderby = " ORDER BY views DESC";
    		$query = DB::query($sql.$orderby.$end);
    		while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=viewthread&tid=".$thread[tid]."&from=home";
                $thread["title"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread['contenttype']='qbar';
				$thread['id']=$thread[tid];
                $threads[$thread[tid]] = $thread;
            }
    	} elseif ($style[key] == "title_commentnum") {
    		$orderby = " ORDER BY replies DESC";
    		$query = DB::query($sql.$orderby.$end);
    		while ($thread = DB::fetch($query)) {
                $thread["url"] = "forum.php?mod=viewthread&tid=".$thread[tid]."&from=home";
                $thread["title"] = cutstr($thread["subject"], $parameter["title_len"]);
				$thread['contenttype']='qbar';
				$thread['id']=$thread[tid];
                $threads[$thread[tid]] = $thread;
            }
    	}
        $result["parameter"] = $parameter;
        $result["listdata"] = $threads;
        
        return array('data' => $result);
    }

}
?>
