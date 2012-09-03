<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
/**
 * 资源列表
 * 排行元素：
 * @author SK
 *
 */
class block_ranking7 {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
		$settings = array(
			'select_ordertype' => array(
				'title' => '选择排序元素',
				'type' => 'select',
				'value' => array(),
			),
			array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
		);
		
		$settings['select_ordertype']['value'][] = array('0', '全部');
		$settings['select_ordertype']['value'][] = array('1', '官方文档');
		$settings['select_ordertype']['value'][] = array('2', '案例');
		$settings['select_ordertype']['value'][] = array('4', '课程');
		
		return $settings;
    }
    
    function getstylesetting($style) {
        $categorys_setting = array();
        //标题+平均分
        $categorys_setting["title_averagescorenum"] = array(
            'title_len' => array(
                'title' => '标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
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
        //标题+分享次数
        $categorys_setting["title_sharenum"] = array(
            'title_len' => array(
                'title' => '标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
        //标题+收藏次数
        $categorys_setting["title_favoritenum"] = array(
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
    	
    	$sql = "SELECT * FROM ".DB::table("resourcelist")." WHERE fid=$fid ";
    	if ($parameter[select_ordertype]==1) {
    		$and = " AND typeid=1";
    	} elseif ($parameter[select_ordertype]==2) {
    		$and = " AND typeid=2";
    	} elseif ($parameter[select_ordertype]==4) {
    		$and = " AND typeid=4";
    	}
    	$end = " LIMIT $parameter[items]";
    	$orderby = "";
    	$threads = array();
    	
    	if ($style[key] == "title_averagescorenum") {
    		$orderby = " ORDER BY averagescore DESC";
    		$query = DB::query($sql.$and.$orderby.$end);
    		while ($thread = DB::fetch($query)) {
                $thread["url"] = $thread[titlelink];
                $thread["title"] = cutstr($thread["title"], $parameter["title_len"]);
				$thread['contenttype']='resourcelist';
                $threads[$thread[id]] = $thread;
            }
    	} elseif ($style[key] == "title_viewnum") {
    		$orderby = " ORDER BY readnum DESC";
    		$query = DB::query($sql.$and.$orderby.$end);
    		while ($thread = DB::fetch($query)) {
                $thread["url"] = $thread[titlelink];
                $thread["title"] = cutstr($thread["title"], $parameter["title_len"]);
                $thread["views"] = $thread[readnum];
				$thread['contenttype']='resourcelist';
                $threads[$thread[id]] = $thread;
            }
    	} elseif ($style[key] == "title_commentnum") {
    		$orderby = " ORDER BY commentnum DESC";
    		$query = DB::query($sql.$and.$orderby.$end);
    		while ($thread = DB::fetch($query)) {
                $thread["url"] = $thread[titlelink];
                $thread["title"] = cutstr($thread["title"], $parameter["title_len"]);
                $thread["replies"] = $thread[commentnum];
				$thread['contenttype']='resourcelist';
                $threads[$thread[id]] = $thread;
            }
    	} elseif ($style[key] == "title_sharenum") {
    		$orderby = " ORDER BY sharenum DESC";
    		$query = DB::query($sql.$and.$orderby.$end);
    		while ($thread = DB::fetch($query)) {
                $thread["url"] = $thread[titlelink];
                $thread["title"] = cutstr($thread["title"], $parameter["title_len"]);
				$thread['contenttype']='resourcelist';
                $threads[$thread[id]] = $thread;
            }
    	} elseif ($style[key] == "title_favoritenum") {
    		$orderby = " ORDER BY favoritenum DESC";
    		$query = DB::query($sql.$and.$orderby.$end);
    		while ($thread = DB::fetch($query)) {
                $thread["url"] = $thread[titlelink];
                $thread["title"] = cutstr($thread["title"], $parameter["title_len"]);
				$thread['contenttype']='resourcelist';
                $threads[$thread[id]] = $thread;
            }
    	} 
        $result["parameter"] = $parameter;
        $result["listdata"] = $threads;
        
        return array('data' => $result);
    }

}
?>
