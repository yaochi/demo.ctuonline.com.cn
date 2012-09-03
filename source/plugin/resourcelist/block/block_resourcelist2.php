<?php

if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_resourcelist2 {

function getsetting() {

		
		global $_G;
		$block_param = $_G['tmp_block_param_value'];//放置选择的一些参数值，为解决模块选择的指定数据无法回显

		//print_r($block_param);

		//curr_select_category 当前选择值
		if($_GET['select_category']){
			$curr_select_category = $_GET['select_category'];
		}else{
			if($block_param['select_category']){
				$curr_select_category = $block_param['select_category'][0];
			}
		}
		//curr_select_resource 当前选择的具体资源数组值
		if($block_param['select_resource']){
			$curr_select_resource = $block_param['select_resource'];
		}


        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));
        $myscript = "'".$_GET['script']."'";
        $groupid = $_GET["fid"];
//        $one = '<select class="ps" name="parameter[select_type][]" onchange="javascript:location.href=portal.php?mod=portalcp&ac=block&op=setting&classname=notice&script='.$myscript.'&inajax=1&fid='.$_GET['fid'].'&select_type=this.value">';
		$myname = "'resourcelist'";
//        $one .= '<option value=""></option>';

		//资源分类
 		$one = '<select class="ps" name="parameter[select_category][]" onchange="block_get_setting_resourcelist_array('.$myname.', '.$myscript.', '.$_GET['fid'].', this.value)">';
		$check = 'selected="selected"';
		
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
	    $one .= '<option value="">全部</option>';
	


			
		

	    foreach ($categorys as $value) {
	    	$settings['select_category']['value'][] = array($value['id'], $value['name']);

			//add by songsp 2010-12-8 判断是否选中
			if($curr_select_category == $value['id']){
				$check = 'selected="selected"';
			}else{
				$check='';
			}

			$one .= '<option value='.$value['id'].' '.$check.'>'.$value['name'].'</option>';
			
	    	//$one .= '<option value='.$value['id'].' '.($_GET['select_category'] == $value['id'] ? $check : '').'>'.$value['name'].'</option>';
	    }
        $one .='</select>';
    	
 //		资源类型
// 		$two = '<select class="ps" name="parameter[select_type][]" onchange="block_get_setting_array('.$myname.', '.$myscript.', '.$_GET['fid'].', this.value)">';
//		$check = 'selected="selected"';
//		$two .= '<option value=1 '.($_GET['select_type'] == 1 ? $check : '').'>文档</option>';
//		$two .= '<option value=2 '.($_GET['select_type'] == 2 ? $check : '').'>案例</option>';
//		$two .= '<option value=4 '.($_GET['select_type'] == 4 ? $check : '').'>课程</option>';
//        $two .='</select>';
        
//        $mytype = "";
//        if(isset($_GET['select_type'])) {
//        	$mytype .= " AND typeid=".$_GET['select_type'];
//        } else {
//        	$mytype .= " AND typeid=1";
//        }
		$mycategory = "";
		//if(isset($_GET['select_category'])) {
		//	$mycategory .= " AND fcategoryid=".$_GET['select_category'];

		if(isset($curr_select_category) && $curr_select_category){
			$mycategory .= " AND fcategoryid=".$curr_select_category ;
		} else {
			$mycategory .= "";
		}
        
        $three = '<select class="ps" multiple="multiple" name="parameter[select_resource][]">';
		$query = DB::query("SELECT * FROM ".DB::table('resourcelist')." WHERE fid=".$groupid.$mycategory);
		while($value=DB::fetch($query)) {


			//设置是否选中
			if($curr_select_resource && in_array($value['id'] , $curr_select_resource)){
				$check = 'selected="selected"';
			}else{
				$check='';
			}


			$three .= '<option value="'.$value['id'].'"'.$check.' >'.$value['title'].'</option>';
		}
        $three .='</select>';
        
		$result[] = array("title"=>"选择分类", "html"=>$one);
//		$result[] = array("title"=>"选择类型", "html"=>$two);
		$result[] = array("title"=>"选择资源", "html"=>$three);
		$result[] = array("html"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>');
        return $result;
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
        return $categorys_setting[$style];
    }

    function getdata($style, $parameter) {
    	$groupid = $_GET["fid"];
	    $resourceid = '';
	    foreach($parameter['select_resource'] as $value) {
			$resourceid .= $value.',';
		}
		$resourceid = substr($resourceid, 0, strlen($resourceid)-1);
		if ($resourceid) {
        $query = DB::query("SELECT * FROM ".DB::table('resourcelist')." WHERE id IN (".$resourceid.") ORDER BY uploaddate DESC ");
        $resources = array();
	    	if ($style[key] == "selfstyle") {
	            //图片尺寸  摘要长度  标题字数
	            while ($resource = DB::fetch($query)) {
	                $resource["url"] = $resource["titlelink"];
	                $resource["title"] = cutstr($resource["title"], $parameter["title_len"]);
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
					//高度>宽度
						$resource["imgflag"] = "1";
					}
	                $resources[$resource[id]] = $resource;
	            }
	        }
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
					//高度>宽度
						$resource["imgflag"] = "1";
					}
	                $resources[$resource[id]] = $resource;
	            }
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
