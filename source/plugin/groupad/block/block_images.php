<?php
/*
 * @function
 * DIY时，“文字列表(多)”Setting & Deal File
 * 选择该样式时，根据“选择分类”选定分类下，text类别广告下，选中
 *
 *
 *
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class block_images {

    function getsetting() {
        $plugin_id = array_pop(explode(DIRECTORY_SEPARATOR, dirname(dirname(__FILE__))));

        $settings = array(
                'ad_type' => array(
                    'title' => '选择分类',
                    'type'  => 'select',
                    'value' => array()
                ),
                'title' => array(
                    'title' => '指定广告标题',
                    'type' => 'text'
                ),               

                array("type"=>'<input type="hidden" name="parameter[plugin_id]" value="'.$plugin_id.'"/>')
        );

        //分类
        require_once libfile("function/category");
        $is_enable_category = false;
        $fid = $_GET["fid"];

        if(common_category_is_enable($fid, $plugin_id)){
            $is_enable_category = true;
            $categorys = common_category_get_category($fid, $plugin_id);
        }

        foreach ($categorys as $value){
                $settings['ad_type']['value'][] = array($value['id'], $value['name']);
        }
         $settings['ad_type']['value'][] = array(0, "未分类");
         $settings['ad_type']['value'][] = array(-1, "所有");

        return $settings;

    }
	
    function getstylesetting($style){
        // $categorys_setting = array();
		// ori原尺寸效果目前是将width和height设置为0
        $categorys_setting["groupad_only_pic"] = array(
                 'size' => array(
                    'title' => '图片尺寸',
                    'type' => 'select',
                    'value' => array(
                        array('ori', '原尺寸'),
                        array('diy', '自定义')
                    ),
                  ),
                'width' => array(
                    'title' => '广告图片宽度',
                    'type' => 'text',
                    'default' => 200
                ),
                'height' => array(
                    'title' => '广告图片高度',
                    'type' => 'text',
                    'default' => 200
                )

        );      
       $categorys_setting["groupad_pic_text"] = array(
             'width' => array(
                    'title' => '广告图片宽度',
                    'type' => 'text',
                    'default' => 200
                ),
                'height' => array(
                    'title' => '广告图片高度',
                    'type' => 'text',
                    'default' => 200
                ),
		'content_len' => array(
                     'title' => '图片文字字数',
                     'type' => 'text',
                     'default' => 20
                )
        );
         $categorys_setting["groupad_pics"] = array(
             'width' => array(
                    'title' => '广告图片宽度',
                    'type' => 'text',
                    'default' => 200
                ),
                'height' => array(
                    'title' => '广告图片高度',
                    'type' => 'text',
                    'default' => 200
                )
        );
        $categorys_setting["groupad_pics_roll"] = array(
                'height' => array(
                    'title' => '广告图片高度',
                    'type' => 'text',
                    'default' => 200
                ),
                'roll_speed' => array(
                    'title' => '滚动速度',
                    'type' => 'select',
                    'value' => array(
                        array('fast', '快'),
                        array('slow', '慢')
                    )
                )
        );
        $categorys_setting["groupad_pics_showwindows"] = array(
             'width' => array(
                    'title' => '广告图片宽度',
                    'type' => 'text',
                    'default' => 200
                ),
                'height' => array(
                    'title' => '广告图片高度',
                    'type' => 'text',
                    'default' => 200
                ),
                'content_len' => array(
                     'title' => '图片文字字数',
                     'type' => 'text',
                     'default' => 20
                )

        );
        
        return $categorys_setting[$style];
    }
    /*
     * @zic 2010-08-11
     *
     */
    function getdata($style, $parameter) {
        global $_G;

    	//!----Initializing Date-----------------
        $db = DB::table('groupad');
        $groupid = $_G["fid"];

        $ad_type = $parameter["ad_type"];
        $title = $parameter["title"];
        $num = $parameter["items"];
        $width = intval($parameter["width"]);
        $height = intval($parameter["height"]);
		$size = $parameter["size"];
	
	$content_len = $parameter["content_len"];
		
        //!-----Data 校验---------------------
        // 是否为数字,是否为整数
        $num = is_numeric($num)?$num:1;
        $num = intval($num);
		if($size == "ori"){
			$width = -1;
			$height = -1;
		}
        // 图片显示字数
        $content_len = is_numeric($content_len)?$content_len:1;
        $content_len = intval($content_len);
        // 依据广告分类判断SQL查询语句
        $ad_type = intval($ad_type);
        $adtype_sql = "";
        if($ad_type!=-1){
            $adtype_sql = " AND ad_type = '$ad_type' ";
        }

       //!---拼装及执行SQL语句----------------------------------------------
        $sql = "SELECT * FROM $db WHERE group_id = $groupid AND media_style = 'image' AND is_display = '1' ".$adtype_sql."AND title LIKE '%$title%' ORDER BY display_order DESC, update_time DESC LIMIT $num";
        //echo "sql = $sql";
        $query = DB::query($sql);

        //!----拼装前台html显示语句------------------------------------------
        $i = 0;
        $res_array = array();
        // 取出结果集数量
        $result["num_rows"] = DB::num_rows($query);
        while($res = DB::fetch($query)){

            $murl = base64_encode($res["media_url"]);
            // 广告资源id add in 2010-8-26
            $ad_id = $res["id"];
            $url = "forum.php?mod=group&action=plugin&fid={$groupid}&plugin_name=groupad&plugin_op=groupmenu&groupad_action=push_cre_exp&id={$ad_id}&murl={$murl}";
            $res["url"] = $url;

            // 如果是"#"则不跳转页面
            // 1 - 跳转一个新页面
            // 0 - 只在本页面
            if($res["media_url"]=="#"){
                $res["isSelf"] = 0;
            }else{
                $res["isSelf"] = 1;
            }
            // 如果用户在DIY编辑模块中填入内容，则沿用填写的内容
            // 如果没有填写尺寸，则沿用图片或者flash原始大小
             if($style[key]=="groupad_pics_roll"){
                 // 不能取整，系数可能小于0;
                 $cos = $height/$res["height"];
                 $width = $res["width"]*$cos;
               
                 $res["width"] = intval($width);
                 //echo $res["width"]."<br />";
                 $res["height"] = $height;
             }else{
                if($width>=0){
                    $res["width"] = $width;
                }
                if($height>=0){
                    $res["height"] = $height;
                }
             }
            // 显示内容字数处理
	    $content = $res["content"];
            // 内容显示字数，超过字数显示...
            $from = 0;
            $content = preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$content_len.'}).*#s','$1',$content);
            //$content = utf8Substr($content,0,$content_len);
            if($content!=$res["content"]){
                $content = "$content...";
            }
            $res["content"] = $content;
            
	    // 取出结果序列
            $res["point"] = ++$i;
			//2010-11-29 16:03:16 add
			$res['contenttype']='groupad';
			$res['id']=$ad_id;
			$res['name']=$res["title"];

            $res_array[$res["id"]] = $res;           

        };
        
        // 对于从左至右滚动，传递两个参数
        if($style[key]=="groupad_pics_roll"){
            // 滚动速度
            $result["speed"] = $parameter["roll_speed"];
            
            //产生一个随机block id
            $time = microtime();
            $seq = hash('md5',$time);
            $result["ul"] = $seq;         
           
        }

        // 对于互动橱窗，传递两个参数
        if($style[key]=="groupad_pics_showwindows"){
           
            //产生一个随机block id
            $time = microtime();
            $seq = hash('md5',$time);
            $result["div"] = $seq;
            $result["div_content"] = $seq."Content";
            $result["div_menu"] = $seq."Menu";

        }
		
		//2010-11-29 16:03:16 add
		$result["parameter"] = $parameter;
        // 组装返回页面参数;
        $result["listdata"] = $res_array;               
        
        return array('data' => $result);
    }
		 
    //utf-8 字符串截取函数
    function utf8Substr($str, $from, $len){
            return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s','$1',$str);

    }

}
?>
