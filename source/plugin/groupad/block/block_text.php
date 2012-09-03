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

class block_text {
    /*
     * 提供标题模糊查询功能
     * 未填写标题，依据分类显示最新且display_order高的记录
     */
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
        $categorys_setting = array();
        $categorys_setting["general"] = array();
        
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
    	
        $num = $parameter["items"];
        
        $ad_type = $parameter["ad_type"];
        $title = $parameter["title"];
        
        //!-----Data 校验---------------------
        // 是否为数字,是否为整数
        $num = is_numeric($num)?$num:1;
        $num = intval($num);
        
        // 依据广告分类判断SQL查询语句
        $ad_type = intval($ad_type);
        $adtype_sql = "";
        if($ad_type!=-1){
            $adtype_sql = " AND ad_type = '$ad_type' ";
        }         

        //!---拼装及执行SQL语句----------------------------------------------
        $sql = "SELECT * FROM $db WHERE group_id = $groupid AND media_style = 'text' AND is_display = '1' ".$adtype_sql."AND title LIKE '%$title%' ORDER BY display_order DESC, update_time DESC LIMIT $num";
        
        $query = DB::query($sql);

        //!----拼装前台html显示语句------------------------------------------
        $forums = array();
        while($data = DB::fetch($query)) {
           // 组装URL
            $murl = base64_encode($data["media_url"]);
            $ad_id = $data['id'];
            $url = "forum.php?mod=group&action=plugin&fid={$groupid}&plugin_name=groupad&plugin_op=groupmenu&groupad_action=push_cre_exp&id={$ad_id}&murl={$murl}";
            // 如果是"#"则不跳转页面
            if($data["media_url"]=="#"){
                $res[$ad_id]["isSelf"] = 0;
            }else{
                $res[$ad_id]["isSelf"] = 1;
            }
            
            $res[$ad_id]["url"] = $url;
            $res[$ad_id]["name"] = $data["title"];
            $res[$ad_id]["contenttype"] = 'activity';
            $res[$ad_id]["id"] = $ad_id;
        }
		$result["parameter"] = $parameter;
        $result["listdata"] = $res;

        return array('data' => $result);
    }

}
?>
