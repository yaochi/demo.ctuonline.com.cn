<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
/**
 * 资源列表
 * @author SK
 *
 */
class block_resourcelist {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

		$settings = array(
			'select_category' => array(
				'title' => '选择分类',
				'type' => 'select',
				'value' => array(),
			),
        	'select_type' => array(
				'title' => '选择类型',
				'type' => 'select',
				'value' => array(),
			),
			array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
		);

	    //资源类型
	    $settings['select_type']['value'][] = array(0, "全部");
	    $settings['select_type']['value'][] = array(1, "文档");
	    $settings['select_type']['value'][] = array(2, "案例");
	    $settings['select_type']['value'][] = array(4, "课程");

	    //资源分类
	    require_once libfile("function/category");
	    $is_enable_category = false;
	    $pluginid = $_GET["classname"];
	    $groupid = $_GET["fid"];
	    if(common_category_is_enable($groupid, $pluginid)){
	        $is_enable_category = true;
	        $categorys = common_category_get_category($groupid, $pluginid);
	    } else {
	    	array_shift($settings);
	    }
	    $settings['select_category']['value'][] = array(0, "全部");
	    foreach ($categorys as $value) {
	    	$settings['select_category']['value'][] = array($value['id'], $value['name']);
	    }

		return $settings;
    }

    function getstylesetting($style) {
    	$categorys_setting = array();
    	//纯列表
        $categorys_setting["general"] = array(
            'title_len' => array(
                'title' => '资源标题字数',
                'type' => 'text',
                'value' => 50
            )
        );
        //焦点
        $categorys_setting["resfocus"] = array(
            'title_len' => array(
                'title' => '资源标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'top_desc_len' => array(
                'title' => '首条资源摘要字数',
                'type' => 'text',
                'value' => 200
            ),
            'top_pic_width' => array(
                'title' => '首条资源的图片宽度',
                'type' => 'text',
                'value' => 80
            ),
            'top_pic_height' => array(
                'title' => '首条资源的图片高度',
                'type' => 'text',
                'value' => 112
            ),
        );
        //独立样式
        $categorys_setting["selfstyle"] = array(
            'title_len' => array(
                'title' => '资源标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'top_desc_len' => array(
                'title' => '资源摘要字数',
                'type' => 'text',
                'value' => 200
            ),
            'top_pic_width' => array(
                'title' => '资源图片宽度',
                'type' => 'text',
                'value' => 80
            ),
            'top_pic_height' => array(
                'title' => '资源图片高度',
                'type' => 'text',
                'value' => 112
            ),
        );
        //图+标题+类型
        $categorys_setting["cate_title_uploaddate"] = array(
            'title_len' => array(
                'title' => '资源标题字数',
                'type' => 'text',
                'value' => 50
            ),'value' => 200
        );
        //图+标题+类型
        $categorys_setting["pic_title_cate"] = array(
            'title_len' => array(
                'title' => '资源标题字数',
                'type' => 'text',
                'value' => 50
            ),
            'top_pic_width' => array(
                'title' => '资源图片宽度',
                'type' => 'text',
                'value' => 80
            ),
            'top_pic_height' => array(
                'title' => '资源图片高度',
                'type' => 'text',
                'value' => 112
            ),
        );

        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
    	if (!$_GET["fid"]) {
            return array('html' => '请在专区内使用该组件', 'data' => null);
        }
        $result = array();
    	$groupid = $_GET["fid"];
    	if ($groupid) {
    		$groupsql = " fid=".$groupid;
    	}
    	$mytype = $parameter["select_type"];
    	$mycategory = $parameter["select_category"];
    	if ($mytype) {
    		$mytypesql = " AND typeid=".$mytype;
    	}
    	if ($mycategory) {
    		$mycategorysql = " AND fcategoryid=".$mycategory;
    	}
        $sql = "SELECT * FROM ".DB::table('resourcelist')." WHERE ".$groupsql.$mytypesql.$mycategorysql." ORDER BY displayorder DESC, uploaddate DESC LIMIT ".$parameter['items'];
        $query = DB::query($sql);
        $resources = array();

		if ($style[key] == "general") {
            //纯列表 记录标题字数
            while ($resource = DB::fetch($query)) {
                $resource["url"] = $resource["titlelink"];
                $resource["name"] = cutstr($resource["title"], $parameter["title_len"]);
				$resource["contenttype"]='resourcelist';
				//$image_info = getimagesize($resource["imglink"]);
				if($image_info[0]>$image_info[1]){
					//宽度>高度
					$resource["imgflag"] = "0";
				}else{
					$resource["imgflag"] = "1";
				}
                $resources[$resource[id]] = $resource;
            }
        } else if ($style[key] == "resfocus") {
            //首条记录的图片尺寸 首条记录的摘要长度 记录标题字数
            while ($resource = DB::fetch($query)) {
                $resource["url"] = $resource["titlelink"];
                $resource["name"] = cutstr($resource["title"], $parameter["title_len"]);
                if ($resource["about"]) {
                    $resource["about"] = cutstr($resource["about"], $parameter["top_desc_len"]);
                }
                $resource["uploaddate"] = dgmdate($resource["uploaddate"]);
				$resource["contenttype"]='resourcelist';
                if (!$top) {
                	$top = $resource;
                	$top["pic"] = $top["imglink"];
					//$image_info = getimagesize($top["imglink"]);
					if($image_info[0]>$image_info[1]){
						//宽度>高度
						$top["imgflag"] = "0";
					}else{
						$top["imgflag"] = "1";
					}
                } else {
					//$image_info = getimagesize($resource["imglink"]);
					if($image_info[0]>$image_info[1]){
					//宽度>高度
						$resource["imgflag"] = "0";
					}else{
						$resource["imgflag"] = "1";
					}
                	$resources[$resource[id]] = $resource;
                }
            }
            $result["top"] = $top;
        } else if ($style[key] == "selfstyle") {
            //图片尺寸 摘要长度 标题字数
            while ($resource = DB::fetch($query)) {
                $resource["url"] = $resource["titlelink"];
                $resource["name"] = cutstr($resource["title"], $parameter["title_len"]);
                if ($resource["about"]) {
                    $resource["about"] = cutstr($resource["about"], $parameter[top_desc_len]);
                }
                $resource["uploaddate"] = dgmdate($resource["uploaddate"]);
				$resource["contenttype"]='resourcelist';
				//$image_info = getimagesize($resource["imglink"]);
					if($image_info[0]>$image_info[1]){
					//宽度>高度
						$resource["imgflag"] = "0";
					}else{
						$resource["imgflag"] = "1";
					}
                $resources[$resource[id]] = $resource;
            }
            $result["top"] = $top;
        } elseif ($style[key] == "cate_title_uploaddate") {
        	while ($resource = DB::fetch($query)) {
                $resource["title"] = cutstr($resource["title"], $parameter["title_len"]);
				$resource["category"] = $resource["type"];
				$resource["uploaddate"] = dgmdate($resource["uploaddate"]);
				$resource["contenttype"]='resourcelist';
				//$image_info = getimagesize($resource["imglink"]);
					if($image_info[0]>$image_info[1]){
					//宽度>高度
						$resource["imgflag"] = "0";
					}else{
						$resource["imgflag"] = "1";
					}
                $resources[$resource[id]] = $resource;
            }
        } else if ($style[key] == "pic_title_cate") {
        	while ($resource = DB::fetch($query)) {
                $resource["title"] = cutstr($resource["title"], $parameter["title_len"]);
				$resource["contenttype"]='resourcelist';
				$resource["category"] = $resource["type"];
                $resource["uploaddate"] = dgmdate($resource["uploaddate"]);
				//$image_info = getimagesize($resource["imglink"]);
					if($image_info[0]>$image_info[1]){
					//宽度>高度
						$resource["imgflag"] = "0";
					}else{
					//高度>宽度
						$resource["imgflag"] = "1";
					}
                $resources[$resource[id]] = $resource;
            }
        }
        //增加block id
		$time = microtime();
		$seq = hash('md5',$time);
        $result["parameter"] = $parameter;
	    $result["listdata"] = $resources;
	    $result["resblockid"] = $seq;

        return array('data' => $result);
    }
}
?>
