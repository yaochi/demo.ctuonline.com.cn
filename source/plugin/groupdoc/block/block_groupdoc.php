<?php
/* Function: 直播数据源 高级自定义
 * Com.:
 * Author: wuhan
 * Date: 2010-7-19
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_groupdoc {
	var $setting = array();
	function block_groupdoc() {
		$this->setting = array(
			'typeid'	=> array(
				'title' => '选择分类',
				'type' => 'select',
				'value' => ''
			),
		);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		if($settings['typeid']){
			require_once libfile("function/category");
		    $pluginid = "groupdoc";
		    if(common_category_is_enable($_G['fid'], $pluginid)){
		        $categorys = common_category_get_category($_G['fid'], $pluginid);
		    }
		    $settings['typeid']['value'][] = array(0, '全部');
		    foreach($categorys as $key => $value){
		    	$settings['typeid']['value'][] = array($value['id'], $value['name']);
		    }
		}

		return $settings;
	}
	
	function getstylesetting($style) {
        $categorys_setting = array();
        //纯列表
        $categorys_setting["general"] = array(
            'titlelength' => array(
                'title' => '文档标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
        //无摘要
        $categorys_setting["docstyle3"] = array(
            'titlelength' => array(
                'title' => '文档标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'top_pic_width' => array(
                'title' => '文档缩略图宽度',
                'type' => 'text',
                'value' => 80
            ),
            'top_pic_height' => array(
                'title' => '文档缩略图高度',
                'type' => 'text',
                'value' => 112
            ),
        );
        $categorys_setting["doclist"] = 
        $categorys_setting["doclist4"] = 
        $categorys_setting["doclist5"] = 
        $categorys_setting["doclist6"] = 
        $categorys_setting["doclist7"] = 
        $categorys_setting["doclist8"] = 
        $categorys_setting["doclist9"] = array(
			'titlelength' => array(
				'title' => '文档标题字数',
				'type' => 'text',
				'default' => 50
			),
        );
        //摘要
        $categorys_setting["docfocus"] =
        $categorys_setting["docstyle2"] = array(
            'titlelength' => array(
                'title' => '文档标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'contextlength' => array(
                'title' => '文档摘要字数',
                'type' => 'text',
                'value' => 200
            ),
            'top_pic_width' => array(
                'title' => '文档缩略图宽度',
                'type' => 'text',
                'value' => 80
            ),
            'top_pic_height' => array(
                'title' => '文档缩略图高度',
                'type' => 'text',
                'value' => 112
            ),
        );
        $categorys_setting["doclist2"] = 
        $categorys_setting["doclist3"] =  array(
			'titlelength' => array(
				'title' => '文档标题字数',
				'type' => 'text',
				'default' => 50
			),
			'contextlength' => array(
				'title' => '文档摘要字数',
				'type' => 'text',
				'default' => 200
			),
		);
        return $categorys_setting[$style];
    }

	function cookparameter($parameter) {
		return $parameter;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);
		$uids		= !empty($parameter['uids']) ? explode(',', $parameter['uids']) : array();
//		$docids		= !empty($parameter['docids']) ? explode(',', $parameter['docids']) : array();
		$typeid		= !empty($parameter['typeid']) ? intval($parameter['typeid']) : '';
		$startrow	= isset($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= isset($parameter['items']) ? intval($parameter['items']) : 10;
		$titlelength = isset($parameter['titlelength']) ? intval($parameter['titlelength']) : 40;
		$contextlength = isset($parameter['contextlength']) ? intval($parameter['contextlength']) : 200;
		$orderby	= isset($parameter['orderby']) && in_array($parameter['orderby'],array('uploadtime', 'read', 'share', 'favorite', 'comment', 'title', 'averagescore', 'download')) ? $parameter['orderby'] : 'uploadtime';

//		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();
	
		require_once libfile('function/doc');
		$filejson = getFileList($startrow+$items, 1, '', $typeid, $_G['fid'], '', $orderby, 2);
		
		$count = 0;
		$list = $resource = array();
		if($filejson){
			$count = $filejson['totalAmount'];
			$resource = $filejson['resources'];
		}
		
		require_once libfile("function/category");
		$pluginid = "groupdoc";
		$categorys = array();
		if(common_category_is_enable($_G['fid'], $pluginid)){
			$categorys = common_category_get_category($_G['fid'], $pluginid);
			$categorys[0] = array('name' => '全部');
		}
		
		if($count){
			$start = 0;
			require_once libfile("function/group");
			
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
					'username' => user_get_user_name($data['userid']),
					'typeid' => $data['folderid'],
					'typename' => $categorys[$data['folderid']],
					'img' => $data['imglink'],
					'readnum' => $data['readnum'],
					'sharenum' => $data['sharenum'],
					'favoritenum' => $data['favoritenum'],
					'commentnum' => $data['commentnum'],
					'averagescore' => $data['averagescore'],
					'downloadnum' => $data['downloadnum'],
					'fid' => $data['zoneid'],
					'status' => $data['status'],
					'contenttype'=>'groupdoc',
				);
				if ($style[key] == "general") {
					$value['name'] = $value['title'];
				}
				
				if (!$docfocus) {
				//print_r($docfocus);
					$docfocus = $value;
					$list[]=$value;
				} else {
					$list[] = $value;
				}
			}
		
			$_G['block_groupdoc'] = $list;
			
			$result["parameter"] = $parameter;
	    	$result["listdata"] = $list;
	    	$result["docfocus"] = $docfocus;
		//	print_r($result["docfocus"]);
			//print_r($result["listdata"]);
			//print_r($result);
	    	return array('data' => $result);
			//return array('data' => array('listdata' => $list,'parameter' =>$parameter));
//			return array('data' => array('listdata' => $list));
		}
		else{
			return array('html' => '', 'data' => null);
		}
	}
}
?>
