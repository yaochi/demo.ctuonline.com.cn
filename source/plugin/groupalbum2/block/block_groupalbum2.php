<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once (dirname(dirname(__FILE__))."/function/function_groupalbum.php");

class block_groupalbum2 {

function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        $groupid = $_GET["fid"];
		$settings = array(
        	'select_album' => array(
				'title' => '选择相册',
				'type' => 'select',
				'value' => array(),
			),
			array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
		);
 //		相册
		$query = DB::query("SELECT * FROM ".DB::table("group_album")." WHERE fid=".$groupid);
		$albums = array();
		while ($row = DB::fetch($query)) {
			$settings['select_album']['value'][] = array($row['albumid'], $row['albumname']);
		}
		
        return $settings;
    }
    
	function getstylesetting($style) {
    	$categorys_setting = array();
        //相册图片
        $categorys_setting["piclist"] = array(
//            'orderby' => array(
//				'title' => '图片排序方式',
//				'type' => 'mradio',
//				'value' => array(
//					array('dateline', '按发布时间倒序'),
//					array('updatetime', '按更新时间倒序'),
//				),
//				'default' => 'dateline'
//			),
			'top_pic_width' => array(
                'title' => '图片宽度',
                'type' => 'text',
                'value' => 200
            ),
            'top_pic_height' => array(
                'title' => '图片高度',
                'type' => 'text',
                'value' => 200
            ),
			'titlelength' => array(
				'title' => '指定图片说明最大长度',
				'type' => 'text',
				'default' => 40
			),
        );
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
    	$groupid = $_GET["fid"];
	    $albumid = $parameter["select_album"];
		if ($albumid) {
			$query = DB::query("SELECT * FROM ".DB::table('group_pic')." WHERE albumid=".$albumid." ORDER BY dateline DESC LIMIT ".$parameter['items']);
	        $pics = array();
	    	if ($style[key] == "piclist") {
	            while ($row = DB::fetch($query)) {
	                $row["url"] = "#";
	                $row["title"] = cutstr($row["title"], $parameter["titlelength"]);
	                $row['pic'] = 'data/attachment/plugin_groupalbum2/'.$row['filepath'];
					$row['id'] = $row[picid];
					$row['contenttype']='groupalbum2';
	                
					 
					
	            }
	        }
		}
		$pics[] = $row;
        $result["parameter"] = $parameter;
	    $result["listdata"] = $pics;
	    
        return array('data' => $result);
    }

}
?>
