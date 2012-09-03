<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
/**
 * 文档
 * 排行元素：
 * @author SK
 *
 */
class block_ranking8 {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        
		return array(
            array("html" => '<input type="hidden" name="parameter[plugin_id]" value="' . $plugin_id . '"/>')
        );
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
    
	function cookparameter($parameter) {
		return $parameter;
	}

    function getdata($style, $parameter) {
    	$fid = $_GET["fid"];

    	$threads = array();
    	
    	$parameter = $this->cookparameter($parameter);
    	
    	require_once libfile('function/doc');
    	$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
    	
    	if ($style[key] == "title_averagescorenum") {
    		$orderby = "averagescore";
    		$filejson = getFileList($items, 1, '', '', $_G['fid'], '', $orderby, 2);
    		
    	} elseif ($style[key] == "title_viewnum") {
    		$orderby = "read";
    		$filejson = getFileList($items, 1, '', '', $_G['fid'], '', $orderby, 2);
    	} elseif ($style[key] == "title_commentnum") {
    		$orderby = "comment";
    		$filejson = getFileList($items, 1, '', '', $_G['fid'], '', $orderby, 2);
    	} elseif ($style[key] == "title_sharenum") {
    		$orderby = "share";
    		$filejson = getFileList($items, 1, '', '', $_G['fid'], '', $orderby, 2);
    	} elseif ($style[key] == "title_favoritenum") {
    		$orderby = "favorite";
    		$filejson = getFileList($items, 1, '', '', $_G['fid'], '', $orderby, 2);
    	}
    	
    	$count = 0;
		$list = $resource = array();
		if($filejson){
			$count = $filejson['totalAmount'];
			$resource = $filejson['resources'];
		}
    	
    	if($count){
			$start = 0;
			foreach($resource as $data){
				if($start < $startrow){
					$start++;
					continue;
				}
				
				$value = array(
					'id' => $data['id'],
					'idtype' => 'gdocid',
					'title' => $data['title'],
					'shorttitle' => cutstr($data['title'], $titlelength),
					'url' => $data['titlelink'],
					'context' => cutstr($data['context'], $contextlength),
					'uploadtime' => dgmdate($data['uploadtime']/1000, 'Y-m-d H:i'),
					'uid' => $data['userid'],
					'username' => $data['username'],
					'typeid' => $data['folderid'],
					'typename' => $categorys[$data['folderid']],
					'img' => $data['imglink'],
					'views' => $data['readnum'],
					'sharenum' => $data['sharenum'],
					'favoritenum' => $data['favoritenum'],
					'commentnum' => $data['commentnum'],
					'averagescore' => $data['averagescore'],
					'downloadnum' => $data['downloadnum'],
					'contenttype'=>'doc',
					'fid' => $data['zoneid'],
					'status' => $data['status'],
				);
				
				$list[] = $value;
			}
		
			$_G['block_groupdoc'] = $list;
			
			return array('data' => array('listdata' => $list));
		}
		else{
			return array('html' => '', 'data' => null);
		}
    }

}
?>
