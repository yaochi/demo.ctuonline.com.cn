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

class block_flash {

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
                'width' => array(
                    'title' => '广告宽度',
                    'type' => 'text',
                    'default' => 200
                ),
                'height' => array(
                    'title' => '广告高度',
                    'type' => 'text',
                    'default' => 200
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
        $categorys_setting = array();
        $categorys_setting["groupad_flash"] = array();
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
        $width = intval($parameter["width"]);
        $height = intval($parameter["height"]);

        //!-----Data 校验---------------------
        // 依据广告分类判断SQL查询语句
        $ad_type = intval($ad_type);
        $adtype_sql = "";
        if($ad_type!=-1){
            $adtype_sql = " AND ad_type = '$ad_type' ";
        }

       //!---拼装及执行SQL语句----------------------------------------------
        $sql = "SELECT * FROM $db WHERE group_id = $groupid AND media_style = 'flash' AND is_display = '1' ".$adtype_sql."AND title LIKE '%$title%' ORDER BY display_order DESC, update_time DESC LIMIT 1";
        $query = DB::query($sql);

        //!----拼装前台html显示语句------------------------------------------
        $data = DB::fetch($query);

            $murl = base64_encode($data["media_url"]);
            $ad_id = $data["id"];
            $url = "forum.php?mod=group&action=plugin&fid=$_G[fid]&plugin_name=groupad&plugin_op=groupmenu";


            $media_dir = $data['media_dir'];
            $content = $data["content"];

            // 如果用户在DIY编辑模块中填入内容，则沿用填写的内容
            // 如果没有填写内容，则沿用创建广告时，预设值

            if($width<=0){
                $width = $data["width"];
            }

            if($height<=0){
                $height = $data["height"];
            }

        $res["url"] = $url;
        $res["media_dir"] = $media_dir;
        $res["width"] = $width;
        $res["height"] = $height;
        $res["content"] = $content;
		$res['contenttype']='groupad';
		$res['id']=$ad_id;
		$res['name']=$data["title"];
		if($ad_id){
			$ress[$ad_id]=$res;
		}
        $result["listdata"] = $ress;
        return array('data' => $result);
    }

}
?>
