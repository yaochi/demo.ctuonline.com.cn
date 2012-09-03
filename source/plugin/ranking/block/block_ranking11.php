<?php

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
/**
 * 成员
 * 排行元素：
 * @author SK
 *
 */
class block_ranking11 {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
		return array(
            array("html" => '<input type="hidden" name="parameter[plugin_id]" value="' . $plugin_id . '"/>')
        );
    }
    
    function getstylesetting($style) {
        $categorys_setting = array();
        //标题+经验值
        $categorys_setting["title_empiricalnum"] = array(
            'title_len' => array(
                'title' => '标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
        //标题+积分
        $categorys_setting["title_creditnum"] = array(
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
    	
    	$sql = "SELECT fg.*, cmp.realname realname FROM ".DB::table("forum_groupuser")." fg, ".DB::table("common_member_profile")." cmp WHERE fg.uid=cmp.uid AND fid=$fid ";
    	$end = " LIMIT $parameter[items]";
    	$orderby = "";
    	$threads = array();
    	
    	if ($style[key] == "title_empiricalnum") {
    		$orderby = " ORDER BY empirical_value DESC";
    		$query = DB::query($sql.$orderby.$end);
    		while ($thread = DB::fetch($query)) {
    			$thread["url"] = "home.php?mod=space&uid=".$thread[uid];
    			if (!$thread["realname"]) {
    				$thread["realname"] = $thread["username"];
    			}
                $thread["title"] = cutstr($thread["realname"], $parameter["title_len"]);
				$thread['contenttype']='member';
                $threads[$thread[uid]] = $thread;
            }
    	} elseif ($style[key] == "title_creditnum") {
    		require_once libfile('function/credit');
    		$credits = get_credit_rank(1,$parameter[items]);
    		foreach ($credits as $credit) {
    			$thread["uid"] = $credit["id"];
				$credit["name"] = iconv('GBK', 'UTF-8', $credit["name"]);
	    		$thread["name"] = $credit["name"];
	    		$thread["username"] = $credit["loginName"];
	    		$thread["credit"] = $credit["credit"];
	    		$thread["url"] = "home.php?mod=space&uid=".$thread[uid];
				$thread['contenttype']='member';
	    		$threads[$thread["uid"]] = $thread;
    		}
    	}
        $result["parameter"] = $parameter;
        $result["listdata"] = $threads;
        
        return array('data' => $result);
    }

}
?>
